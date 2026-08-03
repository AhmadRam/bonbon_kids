<?php

namespace Webkul\Daftra\Listeners;

use Illuminate\Support\Facades\Log;
use Webkul\Daftra\Http\Controllers\DaftraController;
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
     * @param \Webkul\Sales\Contracts\Order $order
     */
    public function syncOrderToDaftra($order)
    {
        try {
            Log::info("Starting Daftra sync for order #{$order->id}");

            // 1. Find or Create Client
            $clientId = $this->findOrCreateClient($order);

            if (!$clientId) {
                Log::error("Daftra Sync Failed: Could not find or create client for order #{$order->id}");
                return;
            }

            // 2. Build Invoice Items
            $items = [];
            foreach ($order->items as $item) {
                // Determine product ID in Daftra.
                // Depending on integration, we might need to search Daftra for the product code.
                // For now, we just pass the SKU as item name or product code if it exists.
                // Daftra createInvoice allows sending item name directly if product_id is not available.
                $items[] = $this->daftraController->buildInvoiceItem(
                    $item->name,
                    "SKU: " . $item->sku,
                    $item->price,
                    $item->qty_ordered,
                    null, // product_id in Daftra, we can omit it and Daftra creates a line item
                    $item->discount_amount
                );
            }
            
            // Add shipping as an item if there is shipping cost
            if ($order->shipping_amount > 0) {
                 $items[] = $this->daftraController->buildInvoiceItem(
                    'Shipping - ' . $order->shipping_title,
                    'Shipping charge',
                    $order->shipping_amount,
                    1,
                    null,
                    0
                );
            }

            // 3. Build Payments (Optional, if we want to mark it based on order status)
            $payments = [];
            if ($order->status === 'processing' || $order->status === 'completed') {
                $payments[] = $this->daftraController->buildPayment(
                    $order->payment->method ?? 'cash',
                    $order->grand_total,
                    $order->created_at->format('Y-m-d H:i:s'),
                    'order_' . $order->id
                );
            }

            // 4. Build Invoice Data
            $invoiceDetails = [
                'client_id' => $clientId,
                'notes' => 'Bagisto Order #' . $order->id,
                'discount' => 0,
                'discount_amount' => 0, // We already applied discount in line items or we can apply it here
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
            if (isset($response['data']['Invoice']['id'])) {
                $daftraInvoiceId = $response['data']['Invoice']['id'];
                
                // Avoid firing save events infinitely
                \Webkul\Sales\Models\Order::withoutEvents(function () use ($order, $daftraInvoiceId) {
                    $order->daftra_invoice_id = $daftraInvoiceId;
                    $order->save();
                });
                
                Log::info("Successfully synced order #{$order->id} to Daftra Invoice #{$daftraInvoiceId}");
            } else {
                Log::error("Daftra Sync Failed: Could not get Invoice ID from response", ['response' => $response]);
            }

        } catch (\Exception $e) {
            Log::error("Daftra Sync Exception for order #{$order->id}: " . $e->getMessage());
        }
    }

    protected function findOrCreateClient($order)
    {
        $email = $order->customer_email;
        $firstName = $order->customer_first_name;
        $lastName = $order->customer_last_name;
        $phone = $order->billing_address ? $order->billing_address->phone : '';

        // 1. Try to find the client
        $page = 1;
        $limit = 100; // Search in recent clients (we might need a search endpoint if Daftra supports it)
        try {
            $clientsResponse = $this->daftraController->getClients($page, $limit);
            if (isset($clientsResponse['data']) && is_array($clientsResponse['data'])) {
                foreach ($clientsResponse['data'] as $clientItem) {
                    if (isset($clientItem['Client']) && $clientItem['Client']['email'] === $email) {
                        return $clientItem['Client']['id'];
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning("Daftra find client failed: " . $e->getMessage());
        }

        // 2. Client not found, create new
        try {
            $clientData = [
                'Client' => [
                    'business_name' => trim($firstName . ' ' . $lastName),
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'phone1' => $phone,
                    'address1' => $order->billing_address ? $order->billing_address->address1 : '',
                    'city' => $order->billing_address ? $order->billing_address->city : '',
                    'country_code' => $order->billing_address ? $order->billing_address->country : '',
                ]
            ];

            $response = $this->daftraController->createClient($clientData);
            
            if (isset($response['id'])) {
                return $response['id'];
            } elseif (isset($response['data']['Client']['id'])) {
                return $response['data']['Client']['id'];
            }
            
            // Log response if format is unexpected
            Log::error("Daftra Create Client returned unexpected format", ['response' => $response]);
            
        } catch (\Exception $e) {
            Log::error("Daftra Create Client Exception: " . $e->getMessage());
        }

        return null;
    }
}
