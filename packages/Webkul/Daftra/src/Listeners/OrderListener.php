<?php

namespace Webkul\Daftra\Listeners;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Webkul\Daftra\Http\Controllers\DaftraController;
use Webkul\Sales\Contracts\Order;
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
            $rowNumber = 1;

            foreach ($order->items as $item) {
                $sku = trim((string) ($item->child?->sku ?: $item->sku));
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

            // 3. Build Payments (Optional, if we want to mark it based on order status)
            $payments = [];
            if ($order->status === 'processing' || $order->status === 'completed') {
                $rawMethod = $order->payment->method ?? 'cash';
                $paymentMethod = match (true) {
                    str_contains($rawMethod, 'cash') => 'cash',
                    str_contains($rawMethod, 'transfer') => 'bank_transfer',
                    str_contains($rawMethod, 'cheque') => 'cheque',
                    default => 'cash',
                };

                $payments[] = $this->daftraController->buildPayment(
                    $paymentMethod,
                    $order->grand_total,
                    $order->created_at->format('Y-m-d H:i:s'),
                    'order_'.$order->id
                );
            }

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
                \Webkul\Sales\Models\Order::withoutEvents(function () use ($order, $daftraInvoiceId) {
                    $order->daftra_invoice_id = $daftraInvoiceId;
                    $order->save();
                });

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
