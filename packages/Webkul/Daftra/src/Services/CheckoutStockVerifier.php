<?php

namespace Webkul\Daftra\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\ResponseCache\Facades\ResponseCache;
use Webkul\Daftra\Http\Controllers\DaftraController;
use Webkul\Product\Helpers\Indexers\Inventory;
use Webkul\Product\Repositories\ProductInventoryRepository;
use Webkul\Product\Repositories\ProductRepository;

class CheckoutStockVerifier
{
    public function __construct(
        protected DaftraController $daftraController,
        protected ProductInventoryRepository $productInventoryRepository,
        protected Inventory $inventoryIndexer,
        protected ProductRepository $productRepository
    ) {}

    /**
     * Verify cart items against live Daftra Sharq Store stock before checkout proceeds.
     * Fails open if Daftra is unreachable or slow so legitimate checkouts are never blocked.
     *
     * @return array<int, array{product_id: int, sku: string, name: string, requested: int, available: int}>
     */
    public function verifyCart($cart): array
    {
        if (! config('daftra.checkout_stock_check', true)) {
            return [];
        }

        if (empty(config('daftra.api_key')) && empty(config('daftra.client_secret'))) {
            return [];
        }

        if (! $cart || (method_exists($cart, 'haveStockableItems') && ! $cart->haveStockableItems())) {
            return [];
        }

        if (method_exists($cart, 'loadMissing')) {
            $cart->loadMissing('items.product', 'items.child.product');
        }

        $requestedByProduct = [];

        foreach ($cart->items ?? [] as $item) {
            $product = ($item->type === 'configurable' && $item->child?->product)
                ? $item->child->product
                : $item->product;

            if (! $product || ! $product->isStockable() || ! $product->manage_stock) {
                continue;
            }

            $productId = (int) $product->id;
            $qty = (int) ($item->quantity ?? 0);
            $sku = trim((string) $product->sku);

            if ($qty <= 0 || $sku === '') {
                continue;
            }

            if (! isset($requestedByProduct[$productId])) {
                $requestedByProduct[$productId] = [
                    'product_id' => $productId,
                    'sku' => $sku,
                    'name' => (string) ($item->name ?: $product->name),
                    'requested' => 0,
                    'product' => $product,
                ];
            }

            $requestedByProduct[$productId]['requested'] += $qty;
        }

        if (empty($requestedByProduct)) {
            return [];
        }

        $storeId = (int) config('daftra.store_id', 2);
        $timeout = (float) config('daftra.checkout_stock_timeout', 2.5);
        $connectTimeout = (float) config('daftra.checkout_stock_connect_timeout', 1.5);

        $skuRequests = array_map(
            fn (array $entry): array => ['sku' => $entry['sku']],
            array_values($requestedByProduct)
        );

        try {
            $liveBalances = $this->daftraController->getLiveStoreBalances(
                $skuRequests,
                $storeId,
                $timeout,
                $connectTimeout
            );
        } catch (\Throwable $e) {
            Log::warning('Checkout live stock check skipped (fail-open): '.$e->getMessage());

            return [];
        }

        $pendingByProduct = $this->loadPendingUnsyncedQuantitiesForProducts(array_keys($requestedByProduct));

        $shortages = [];
        $productsToSync = [];
        $skusToForget = [];

        foreach ($requestedByProduct as $productId => $entry) {
            $sku = $entry['sku'];
            $daftraBalance = $liveBalances[$sku] ?? null;

            if ($daftraBalance === null) {
                continue;
            }

            $pendingQty = (float) ($pendingByProduct[$productId] ?? 0.0);
            $effectiveAvailable = (int) floor(max(0.0, (float) $daftraBalance - $pendingQty));

            if ($effectiveAvailable < $entry['requested']) {
                $shortages[] = [
                    'product_id' => $productId,
                    'sku' => $sku,
                    'name' => $entry['name'],
                    'requested' => $entry['requested'],
                    'available' => $effectiveAvailable,
                ];

                $productsToSync[$productId] = $effectiveAvailable;
                $skusToForget[] = $sku;
            }
        }

        if (! empty($productsToSync)) {
            $this->syncShortageProductsLocally($productsToSync, $storeId);
            $this->daftraController->forgetLiveStockCache($skusToForget, $storeId);
        }

        return $shortages;
    }

    /**
     * Format a localized customer-facing message describing all stock shortages.
     *
     * @param  array<int, array{product_id: int, sku: string, name: string, requested: int, available: int}>  $shortages
     */
    public function formatShortageMessage(array $shortages): string
    {
        $lines = [];

        foreach ($shortages as $shortage) {
            if ($shortage['available'] <= 0) {
                $lines[] = trans('daftra::app.checkout.out-of-stock', [
                    'name' => $shortage['name'],
                ]);
            } else {
                $lines[] = trans('daftra::app.checkout.insufficient-stock', [
                    'name' => $shortage['name'],
                    'available' => $shortage['available'],
                    'requested' => $shortage['requested'],
                ]);
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Load pending unsynced ordered quantities for a small set of product IDs.
     *
     * @param  array<int, int>  $productIds
     * @return array<int, float>
     */
    protected function loadPendingUnsyncedQuantitiesForProducts(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $rows = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('order_items as parent_items', 'parent_items.id', '=', 'order_items.parent_id')
            ->whereNull('orders.daftra_invoice_id')
            ->whereNotIn('orders.status', ['canceled', 'closed'])
            ->whereIn('order_items.product_id', $productIds)
            ->where('order_items.type', '!=', 'configurable')
            ->selectRaw('order_items.product_id, SUM(GREATEST(0, COALESCE(order_items.qty_ordered, parent_items.qty_ordered, 0) - COALESCE(order_items.qty_shipped, 0) - COALESCE(order_items.qty_canceled, 0) - COALESCE(order_items.qty_refunded, 0))) as pending_qty')
            ->groupBy('order_items.product_id')
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $qty = (float) $row->pending_qty;
            if ($qty > 0) {
                $map[(int) $row->product_id] = $qty;
            }
        }

        return $map;
    }

    /**
     * Persist updated Sharq Store quantities locally and reindex affected products immediately.
     *
     * @param  array<int, int>  $quantitiesByProductId
     */
    protected function syncShortageProductsLocally(array $quantitiesByProductId, int $storeId): void
    {
        if (! DB::table('inventory_sources')->where('id', $storeId)->exists()) {
            return;
        }

        foreach ($quantitiesByProductId as $productId => $qty) {
            $this->productInventoryRepository->saveInventories(
                ['inventories' => [$storeId => $qty]],
                (object) ['id' => $productId]
            );
        }

        $products = $this->productRepository
            ->with(['inventories', 'ordered_inventories', 'inventory_indices'])
            ->whereIn('type', ['simple', 'virtual'])
            ->findWhereIn('id', array_keys($quantitiesByProductId));

        if ($products->isNotEmpty()) {
            $this->inventoryIndexer->reindexBatch($products);
        }

        if (class_exists(ResponseCache::class)) {
            ResponseCache::clear();
        }
    }
}
