<?php

namespace Webkul\Daftra\Listeners;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\ResponseCache\Facades\ResponseCache;
use Webkul\Daftra\Http\Controllers\DaftraController;
use Webkul\Product\Jobs\UpdateCreateInventoryIndex as UpdateCreateInventoryIndexJob;
use Webkul\Sales\Contracts\Order;
use Webkul\Sales\Models\Order as OrderModel;
use Webkul\Sales\Repositories\OrderRepository;

class OrderListener
{
    protected $daftraController;

    protected $orderRepository;

    public function __construct(
        DaftraController $daftraController,
        OrderRepository $orderRepository
    ) {
        $this->daftraController = $daftraController;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param  Order  $order
     */
    public function syncOrderToDaftra($order)
    {
        if (empty(config('daftra.api_key')) && empty(config('daftra.client_secret'))) {
            return;
        }

        $lock = Cache::lock('daftra_sync_lock_order_'.$order->id, 60);

        if (! $lock->get()) {
            Log::info("Daftra Sync skipped for order #{$order->id}: sync already in progress.");

            return;
        }

        try {
            if (method_exists($order, 'refresh')) {
                $order->refresh();
            }

            if (in_array($order->status ?? null, ['canceled', 'closed'], true)) {
                Log::info("Skipping Daftra sync for order #{$order->id}: order is {$order->status}");

                return;
            }

            if (! empty($order->daftra_invoice_id)) {
                Log::info("Skipping Daftra sync for order #{$order->id}: already synced to Invoice #{$order->daftra_invoice_id}");

                return;
            }

            Log::info("Starting Daftra sync for order #{$order->id}");

            // 1. Find or Create Client
            $clientId = $this->findOrCreateClient($order);

            if (! $clientId) {
                Log::error("Daftra Sync Failed: Could not find or create client for order #{$order->id}");

                return;
            }

            // 2. Build Invoice Items
            $storeId = (int) config('daftra.store_id', 2);

            if (method_exists($order, 'loadMissing')) {
                $order->loadMissing('items.child');
            }

            $items = [];
            $orderedSkus = [];
            $rowNumber = 1;

            foreach ($order->items as $item) {
                $sku = trim((string) ($item->child?->sku ?: $item->sku));
                if ($sku !== '') {
                    $orderedSkus[] = $sku;
                }

                $daftraProduct = $this->daftraController->findProductBySku($sku, $item->name);

                if (! $daftraProduct) {
                    Log::warning("Daftra Sync: Product not found in Daftra for order #{$order->id}, SKU: {$sku}, Name: {$item->name}");
                }

                $productCode = ($daftraProduct && $daftraProduct['product_code'] !== '')
                    ? $daftraProduct['product_code']
                    : $sku;

                $productName = ($daftraProduct && $daftraProduct['name'] !== '')
                    ? $daftraProduct['name']
                    : $item->name;

                $items[] = $this->daftraController->buildInvoiceItem(
                    (string) $rowNumber++,
                    $productCode,
                    $item->price,
                    $item->qty_ordered,
                    $daftraProduct['id'] ?? null,
                    $item->discount_amount,
                    $storeId,
                    $productName
                );
            }

            // 3. Always create the Daftra invoice without nested Payment objects so
            // paid orders (e.g., UPayments/KNET) never fail invoice creation & stock deduction.
            $payments = [];

            // 4. Build Invoice Data
            $invoiceDetails = [
                'client_id' => $clientId,
                'notes' => 'Bagisto Order #'.$order->id,
                'discount' => 0,
                'discount_amount' => 0, // We already applied discount in line items or we can apply it here
                'store_id' => $storeId, // Force invoice to deduct from the Sub-warehouse (store 2)
                'invoice_layout_id' => (int) config('daftra.invoice_layout_id', 13),
                'shipping_amount' => $order->shipping_amount > 0 ? (float) $order->shipping_amount : null,
            ];

            // Daftra base currency might be different, let's use the order currency
            $invoiceData = $this->daftraController->buildInvoiceData(
                $invoiceDetails,
                $items,
                $payments,
                $order->order_currency_code
            );

            // 5. Create Invoice in Daftra
            $response = $this->daftraController->createInvoice($invoiceData);

            // 6. Save the daftra_invoice_id to Bagisto order
            $daftraInvoiceId = $response['id']
                ?? $response['data']['Invoice']['id']
                ?? null;

            if ($daftraInvoiceId) {
                // Avoid firing save events infinitely
                OrderModel::withoutEvents(function () use ($order, $daftraInvoiceId) {
                    $order->daftra_invoice_id = $daftraInvoiceId;
                    $order->save();
                });

                $this->transferReservationAfterDaftraSync($order, $storeId);
                $this->daftraController->forgetLiveStockCache($orderedSkus, $storeId);

                // 7. Record payment separately for paid/processing/completed orders
                if ($order->status === 'processing' || $order->status === 'completed') {
                    $this->recordInvoicePayment($order, (int) $daftraInvoiceId);
                }

                Log::info("Successfully synced order #{$order->id} to Daftra Invoice #{$daftraInvoiceId}");
            } else {
                Log::error('Daftra Sync Failed: Could not get Invoice ID from response', ['response' => $response]);
            }

        } catch (\Exception $e) {
            Log::error("Daftra Sync Exception for order #{$order->id}: ".$e->getMessage());
        } finally {
            $lock->release();
        }
    }

    /**
     * Cancel/delete a synced Daftra invoice when a Bagisto order is canceled,
     * restoring stock in Daftra and locally in Sharq Store.
     *
     * @param  Order|object  $order
     */
    public function cancelOrderInDaftra($order): void
    {
        if (empty(config('daftra.api_key')) && empty(config('daftra.client_secret'))) {
            return;
        }

        $lock = Cache::lock('daftra_cancel_lock_order_'.$order->id, 60);

        if (! $lock->get()) {
            Log::info("Daftra Cancel skipped for order #{$order->id}: cancellation already in progress.");

            return;
        }

        try {
            if (method_exists($order, 'refresh')) {
                $order->refresh();
            }

            if (empty($order->daftra_invoice_id)) {
                Log::info("Skipping Daftra cancel for order #{$order->id}: no daftra_invoice_id recorded.");

                return;
            }

            if (method_exists($order, 'loadMissing')) {
                $order->loadMissing('items.child.product', 'items.product');
            }

            $invoiceId = (int) $order->daftra_invoice_id;
            $this->daftraController->deleteInvoice($invoiceId);

            OrderModel::withoutEvents(function () use ($order) {
                $order->daftra_invoice_id = null;
                $order->save();
            });

            $storeId = (int) config('daftra.store_id', 2);
            $skus = [];

            foreach ($order->items ?? [] as $item) {
                $sku = trim((string) ($item->child?->sku ?: $item->sku));
                if ($sku !== '') {
                    $skus[] = $sku;
                }
            }

            $this->daftraController->forgetLiveStockCache($skus, $storeId);
            $this->restoreSharqInventoryAfterDaftraCancel($order, $storeId);

            Log::info("Successfully deleted Daftra Invoice #{$invoiceId} and restored stock for canceled order #{$order->id}");
        } catch (\Exception $e) {
            Log::error("Daftra Cancel Exception for order #{$order->id}: ".$e->getMessage());
        } finally {
            $lock->release();
        }
    }

    /**
     * Move the order's reservation from product_ordered_inventories into product_inventories[Sharq]
     * once Daftra has deducted the invoice, preventing double-deduction in inventory indices.
     */
    protected function transferReservationAfterDaftraSync($order, int $storeId): void
    {
        if (! ($order instanceof OrderModel) || empty($order->channel_id)) {
            return;
        }

        if (! DB::table('inventory_sources')->where('id', $storeId)->exists()) {
            return;
        }

        $order->loadMissing('items.child.product', 'items.product');
        $affectedProductIds = [];

        foreach ($order->items ?? [] as $item) {
            $product = ($item->type === 'configurable' && $item->child?->product)
                ? $item->child->product
                : $item->product;

            if (! $product || ! $product->isStockable() || ! $product->manage_stock) {
                continue;
            }

            $qty = max(0, (int) ($item->qty_ordered - ($item->qty_shipped ?? 0) - ($item->qty_canceled ?? 0)));
            if ($qty <= 0) {
                continue;
            }

            $orderedInventory = DB::table('product_ordered_inventories')
                ->where('product_id', $product->id)
                ->where('channel_id', $order->channel_id)
                ->first();

            if ($orderedInventory) {
                DB::table('product_ordered_inventories')
                    ->where('id', $orderedInventory->id)
                    ->update(['qty' => max(0, (int) $orderedInventory->qty - $qty)]);
            }

            $sourceInventory = DB::table('product_inventories')
                ->where('product_id', $product->id)
                ->where('inventory_source_id', $storeId)
                ->where('vendor_id', 0)
                ->first();

            if ($sourceInventory) {
                DB::table('product_inventories')
                    ->where('id', $sourceInventory->id)
                    ->update(['qty' => max(0, (int) $sourceInventory->qty - $qty)]);
            }

            $affectedProductIds[] = (int) $product->id;
        }

        if (! empty($affectedProductIds)) {
            UpdateCreateInventoryIndexJob::dispatchSync(array_values(array_unique($affectedProductIds)));
        }
    }

    /**
     * Restore Sharq Store inventory locally after Daftra deletes the canceled order invoice.
     */
    protected function restoreSharqInventoryAfterDaftraCancel($order, int $storeId): void
    {
        if (! ($order instanceof OrderModel)) {
            return;
        }

        if (! DB::table('inventory_sources')->where('id', $storeId)->exists()) {
            return;
        }

        $affectedProductIds = [];

        foreach ($order->items ?? [] as $item) {
            $product = ($item->type === 'configurable' && $item->child?->product)
                ? $item->child->product
                : $item->product;

            if (! $product || ! $product->isStockable() || ! $product->manage_stock) {
                continue;
            }

            $restoreQty = max(0, (int) ($item->qty_ordered - ($item->qty_shipped ?? 0)));
            if ($restoreQty <= 0) {
                continue;
            }

            $sourceInventory = DB::table('product_inventories')
                ->where('product_id', $product->id)
                ->where('inventory_source_id', $storeId)
                ->where('vendor_id', 0)
                ->first();

            if ($sourceInventory) {
                DB::table('product_inventories')
                    ->where('id', $sourceInventory->id)
                    ->update(['qty' => (int) $sourceInventory->qty + $restoreQty]);
            } else {
                DB::table('product_inventories')->insert([
                    'product_id' => $product->id,
                    'inventory_source_id' => $storeId,
                    'vendor_id' => 0,
                    'qty' => $restoreQty,
                ]);
            }

            $affectedProductIds[] = (int) $product->id;
        }

        if (! empty($affectedProductIds)) {
            UpdateCreateInventoryIndexJob::dispatchSync(array_values(array_unique($affectedProductIds)));

            if (class_exists(ResponseCache::class)) {
                ResponseCache::clear();
            }
        }
    }

    /**
     * Record the payment separately against an already-created Daftra invoice.
     * Failures are logged as warnings so they never block invoice creation or stock deduction.
     */
    protected function recordInvoicePayment($order, int $daftraInvoiceId): void
    {
        $rawMethod = (string) ($order->payment->method ?? 'cash');
        $isUpayments = str_starts_with($rawMethod, 'upayments');

        $paymentMethod = (string) config('daftra.payment_method', 'cash_on_delivery');

        $treasuryId = $isUpayments
            ? (int) config('daftra.upayments_treasury_id', 3)
            : (int) config('daftra.default_treasury_id', 1);

        $date = $order->created_at
            ? $order->created_at->format('Y-m-d H:i:s')
            : now()->format('Y-m-d H:i:s');

        try {
            $this->daftraController->createInvoicePayment([
                'invoice_id' => $daftraInvoiceId,
                'amount' => (float) $order->grand_total,
                'payment_method' => $paymentMethod,
                'treasury_id' => $treasuryId,
                'transaction_id' => 'order_'.$order->id,
                'date' => $date,
                'status' => 1,
            ]);
        } catch (\Exception $e) {
            Log::warning("Daftra Invoice Payment recording failed for order #{$order->id} (Invoice #{$daftraInvoiceId}): ".$e->getMessage());
        }
    }

    protected function findOrCreateClient($order)
    {
        $email = trim((string) $order->customer_email);
        $firstName = $order->customer_first_name;
        $lastName = $order->customer_last_name;
        $phone = $order->billing_address ? $order->billing_address->phone : '';

        // 1. Try to find the client by email if available
        if (! empty($email)) {
            $clientId = $this->searchClientByEmail($email);
            if ($clientId) {
                return $clientId;
            }
        }

        // 2. Search in recent clients (page 1)
        try {
            $clientsResponse = $this->daftraController->getClients(1, 100);
            if (isset($clientsResponse['data']) && is_array($clientsResponse['data'])) {
                foreach ($clientsResponse['data'] as $clientItem) {
                    if (isset($clientItem['Client']) && ! empty($email) && strcasecmp($clientItem['Client']['email'] ?? '', $email) === 0) {
                        return $clientItem['Client']['id'];
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('Daftra find client failed: '.$e->getMessage());
        }

        // 3. Client not found, create new
        try {
            $clientData = [
                'Client' => [
                    'business_name' => trim($firstName.' '.$lastName) ?: 'Customer',
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'phone1' => $phone,
                    'address1' => $order->billing_address ? $order->billing_address->address1 : '',
                    'city' => $order->billing_address ? $order->billing_address->city : '',
                    'country_code' => $order->billing_address ? $order->billing_address->country : '',
                ],
            ];

            $response = $this->daftraController->createClient($clientData);

            if (isset($response['id'])) {
                return $response['id'];
            } elseif (isset($response['data']['Client']['id'])) {
                return $response['data']['Client']['id'];
            }

            Log::error('Daftra Create Client returned unexpected format', ['response' => $response]);

        } catch (\Exception $e) {
            Log::error('Daftra Create Client Exception: '.$e->getMessage());

            // If creation failed because email already exists, search across multiple pages
            if (! empty($email) && (str_contains($e->getMessage(), 'متواجد سابقاً') || str_contains($e->getMessage(), 'already exists') || str_contains($e->getMessage(), 'validation_errors'))) {
                $foundId = $this->searchClientAcrossPages($email);
                if ($foundId) {
                    return $foundId;
                }
            }
        }

        return null;
    }

    /**
     * Search for a client by email using API query
     */
    protected function searchClientByEmail(string $email)
    {
        try {
            $response = $this->daftraController->getClientsByEmail($email);
            if (isset($response['data']) && is_array($response['data'])) {
                foreach ($response['data'] as $item) {
                    if (isset($item['Client']) && strcasecmp($item['Client']['email'] ?? '', $email) === 0) {
                        return $item['Client']['id'];
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('searchClientByEmail failed: '.$e->getMessage());
        }

        return null;
    }

    /**
     * Search for a client by paginating clients if email already exists
     */
    protected function searchClientAcrossPages(string $email): ?int
    {
        for ($page = 1; $page <= 10; $page++) {
            try {
                $response = $this->daftraController->getClients($page, 100);
                if (isset($response['data']) && is_array($response['data'])) {
                    foreach ($response['data'] as $item) {
                        if (isset($item['Client']) && strcasecmp($item['Client']['email'] ?? '', $email) === 0) {
                            return (int) $item['Client']['id'];
                        }
                    }
                }

                $pageCount = $response['pagination']['page_count'] ?? 1;
                if ($page >= $pageCount) {
                    break;
                }
            } catch (\Exception $e) {
                break;
            }
        }

        return null;
    }
}
