<?php

namespace Webkul\Daftra\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Spatie\ResponseCache\Facades\ResponseCache;
use Webkul\Daftra\Http\Controllers\DaftraController;
use Webkul\Product\Helpers\Indexers\Inventory;
use Webkul\Product\Repositories\ProductInventoryRepository;
use Webkul\Product\Repositories\ProductRepository;

class SyncDaftraQuantities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daftra:sync-quantities {--full : Force a full inventory reindex and response cache clear}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync product quantities from Daftra to Bagisto';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        protected DaftraController $daftraController,
        protected ProductRepository $productRepository,
        protected ProductInventoryRepository $productInventoryRepository
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting Daftra quantity sync...');

        try {
            $page = 1;
            $limit = 100;
            $updatedCount = 0;
            $unchangedCount = 0;
            $notFoundCount = 0;
            $forceFull = (bool) $this->option('full');
            $primaryStoreId = (int) config('daftra.store_id', 2);

            $validSourceIds = DB::table('inventory_sources')
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if (empty($validSourceIds)) {
                $validSourceIds = [1];
            }

            $skuToProduct = $this->loadNormalizedProductMap();
            $existingInventories = $this->loadExistingInventories();

            $manageStockAttrId = DB::table('attributes')
                ->where('code', 'manage_stock')
                ->value('id');

            $defaultChannelCode = core()->getDefaultChannelCode();
            $existingManageStock = $manageStockAttrId
                ? $this->loadExistingManageStock((int) $manageStockAttrId, $defaultChannelCode)
                : [];

            $changedProductIds = [];
            $sharqStockChanged = false;

            $reconciledOrdered = $this->reconcileOrderedInventories();
            foreach ($reconciledOrdered as $pid) {
                $changedProductIds[] = $pid;
                $sharqStockChanged = true;
            }

            do {
                $this->info("Fetching page {$page} from Daftra...");

                $daftraProductsData = $this->daftraController->getProducts($page, $limit);

                if (empty($daftraProductsData)) {
                    break;
                }

                foreach ($daftraProductsData as $daftraItem) {
                    if (! isset($daftraItem['Product']) || ! is_array($daftraItem['Product'])) {
                        continue;
                    }

                    $daftraProduct = $daftraItem['Product'];
                    $productCode = $daftraProduct['product_code'] ?? null;
                    $barcode = $daftraProduct['barcode'] ?? null;
                    $candidates = $this->daftraController->extractSkuCandidates($productCode, $barcode);

                    if (empty($candidates)) {
                        continue;
                    }

                    $matchedProduct = null;
                    foreach ($candidates as $candidate) {
                        $normalized = mb_strtolower($candidate);
                        if (isset($skuToProduct[$normalized])) {
                            $matchedProduct = $skuToProduct[$normalized];
                            break;
                        }
                    }

                    if (! $matchedProduct) {
                        $notFoundCount++;

                        continue;
                    }

                    $productId = $matchedProduct['id'];
                    $bagistoSku = $matchedProduct['sku'];

                    if (! empty($daftraProduct['id'])) {
                        Cache::put(
                            $this->daftraController->skuToDaftraIdCacheKey($bagistoSku),
                            (int) $daftraProduct['id'],
                            86400
                        );
                    }

                    $inventories = array_fill_keys($validSourceIds, 0.0);
                    $isDeactivated = (int) ($daftraProduct['deactivate'] ?? 0) === 1;

                    if (! $isDeactivated) {
                        if (! empty($daftraProduct['ProductStock']) && is_array($daftraProduct['ProductStock'])) {
                            foreach ($daftraProduct['ProductStock'] as $stockItem) {
                                $storeId = (int) ($stockItem['store_id'] ?? 0);
                                if ($storeId > 0 && in_array($storeId, $validSourceIds, true)) {
                                    $inventories[$storeId] = max(0.0, (float) ($stockItem['balance'] ?? 0));
                                }
                            }
                        } else {
                            $stockBalance = max(0.0, (float) ($daftraProduct['stock_balance'] ?? 0));
                            if ($stockBalance > 0 && in_array($primaryStoreId, $validSourceIds, true)) {
                                $inventories[$primaryStoreId] = $stockBalance;
                            }
                        }
                    }

                    $existingForProduct = $existingInventories[$productId] ?? [];
                    $hasInventoryChange = $forceFull;

                    foreach ($inventories as $sourceId => $newQty) {
                        $oldQty = $existingForProduct[$sourceId] ?? null;
                        if ($oldQty === null || abs((float) $oldQty - (float) $newQty) > 0.0001) {
                            $hasInventoryChange = true;
                            if ($sourceId === $primaryStoreId) {
                                $sharqStockChanged = true;
                            }
                        }
                    }

                    if ($hasInventoryChange) {
                        $this->productInventoryRepository->saveInventories(
                            ['inventories' => $inventories],
                            (object) ['id' => $productId]
                        );

                        $existingInventories[$productId] = $inventories;
                        $changedProductIds[] = $productId;
                        $this->line("Updated SKU: {$bagistoSku} | Store {$primaryStoreId} Qty: ".($inventories[$primaryStoreId] ?? 0));
                        $updatedCount++;
                    } else {
                        $unchangedCount++;
                    }

                    if ($manageStockAttrId && ($existingManageStock[$productId] ?? null) !== 1) {
                        DB::table('product_attribute_values')->updateOrInsert([
                            'product_id' => $productId,
                            'attribute_id' => $manageStockAttrId,
                            'channel' => $defaultChannelCode,
                        ], [
                            'boolean_value' => 1,
                        ]);
                        $existingManageStock[$productId] = 1;
                    }
                }

                if (count($daftraProductsData) < $limit) {
                    break;
                }

                $page++;
            } while (true);

            $this->info('Sync completed successfully!');
            $this->info("Updated Products: {$updatedCount} | Unchanged: {$unchangedCount} | Not Found in Bagisto: {$notFoundCount}");

            if ($forceFull) {
                $this->info('Running full inventory reindex...');
                Artisan::call('indexer:index', [
                    '--type' => ['inventory'],
                    '--mode' => ['full'],
                ]);

                if (class_exists(ResponseCache::class)) {
                    ResponseCache::clear();
                }
            } elseif (! empty($changedProductIds)) {
                $this->info('Reindexing changed inventory indices ('.count(array_unique($changedProductIds)).' products)...');
                $inventoryIndexer = app(Inventory::class);

                foreach (array_chunk(array_unique($changedProductIds), 100) as $chunkIds) {
                    $products = $this->productRepository
                        ->with(['inventories', 'ordered_inventories', 'inventory_indices'])
                        ->whereIn('type', ['simple', 'virtual'])
                        ->findWhereIn('id', $chunkIds);

                    if ($products->isNotEmpty()) {
                        $inventoryIndexer->reindexBatch($products);
                    }
                }

                if ($sharqStockChanged && class_exists(ResponseCache::class)) {
                    ResponseCache::clear();
                }
            }

            return 0;
        } catch (\Exception $e) {
            $this->error('Error during sync: '.$e->getMessage());

            return 1;
        }
    }

    /**
     * Load all Bagisto products keyed by normalized lowercase SKU.
     *
     * @return array<string, array{id: int, sku: string}>
     */
    protected function loadNormalizedProductMap(): array
    {
        $map = [];

        $rows = DB::table('products')->select('id', 'sku')->get();
        foreach ($rows as $row) {
            $sku = trim((string) $row->sku);
            if ($sku === '') {
                continue;
            }

            $map[mb_strtolower($sku)] = [
                'id' => (int) $row->id,
                'sku' => $sku,
            ];
        }

        return $map;
    }

    /**
     * Load existing product_inventories grouped by [product_id][inventory_source_id] => qty.
     *
     * @return array<int, array<int, float>>
     */
    protected function loadExistingInventories(): array
    {
        $map = [];

        $rows = DB::table('product_inventories')
            ->where('vendor_id', 0)
            ->select('product_id', 'inventory_source_id', 'qty')
            ->get();

        foreach ($rows as $row) {
            $map[(int) $row->product_id][(int) $row->inventory_source_id] = (float) $row->qty;
        }

        return $map;
    }

    /**
     * Load existing manage_stock boolean values keyed by product_id.
     *
     * @return array<int, int>
     */
    protected function loadExistingManageStock(int $attributeId, string $channel): array
    {
        $map = [];

        $rows = DB::table('product_attribute_values')
            ->where('attribute_id', $attributeId)
            ->where('channel', $channel)
            ->select('product_id', 'boolean_value')
            ->get();

        foreach ($rows as $row) {
            $map[(int) $row->product_id] = (int) $row->boolean_value;
        }

        return $map;
    }

    /**
     * Reconcile product_ordered_inventories so it holds ONLY unshipped quantities
     * from active Bagisto orders not yet synced to Daftra (daftra_invoice_id IS NULL).
     * Returns product IDs whose ordered inventory changed.
     *
     * @return array<int, int>
     */
    protected function reconcileOrderedInventories(): array
    {
        $rows = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('order_items as parent_items', 'parent_items.id', '=', 'order_items.parent_id')
            ->whereNull('orders.daftra_invoice_id')
            ->whereNotIn('orders.status', ['canceled', 'closed'])
            ->whereNotNull('order_items.product_id')
            ->whereNotNull('orders.channel_id')
            ->where('order_items.type', '!=', 'configurable')
            ->selectRaw('order_items.product_id, orders.channel_id, SUM(GREATEST(0, COALESCE(order_items.qty_ordered, parent_items.qty_ordered, 0) - COALESCE(order_items.qty_shipped, 0) - COALESCE(order_items.qty_canceled, 0) - COALESCE(order_items.qty_refunded, 0))) as pending_qty')
            ->groupBy('order_items.product_id', 'orders.channel_id')
            ->get();

        $expected = [];
        foreach ($rows as $row) {
            $qty = (int) round((float) $row->pending_qty);
            if ($qty > 0) {
                $expected[(int) $row->product_id.'|'.(int) $row->channel_id] = [
                    'product_id' => (int) $row->product_id,
                    'channel_id' => (int) $row->channel_id,
                    'qty' => $qty,
                ];
            }
        }

        $existingRows = DB::table('product_ordered_inventories')
            ->select('id', 'product_id', 'channel_id', 'qty')
            ->get();

        $changedProductIds = [];
        $seenKeys = [];

        foreach ($existingRows as $existing) {
            $key = (int) $existing->product_id.'|'.(int) $existing->channel_id;
            $seenKeys[$key] = true;
            $targetQty = $expected[$key]['qty'] ?? 0;

            if ((int) $existing->qty !== $targetQty) {
                DB::table('product_ordered_inventories')
                    ->where('id', $existing->id)
                    ->update(['qty' => $targetQty]);

                $changedProductIds[] = (int) $existing->product_id;
            }
        }

        foreach ($expected as $key => $item) {
            if (! isset($seenKeys[$key])) {
                DB::table('product_ordered_inventories')->insert([
                    'product_id' => $item['product_id'],
                    'channel_id' => $item['channel_id'],
                    'qty' => $item['qty'],
                ]);

                $changedProductIds[] = $item['product_id'];
            }
        }

        return array_values(array_unique($changedProductIds));
    }
}
