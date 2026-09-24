<?php

namespace Webkul\Sales\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Webkul\Daftra\Http\Controllers\DaftraController;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\Refund;

class CreateDaftraOrderRefund implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job should be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $retryAfter = 60;

    /**
     * Timeout in seconds.
     *
     * @var int
     */
    public $timeout = 1800;

    /**
     * @var DaftraController
     */
    protected $daftraService;

    /**
     * @var int
     */
    protected $order_id;

    /**
     * @var int
     */
    protected $refund_id;

    /**
     * Create a new job instance.
     *
     * @param  int  $order_id
     * @param  int  $refund_id
     * @return void
     */
    public function __construct($order_id, $refund_id)
    {
        $this->order_id = $order_id;
        $this->refund_id = $refund_id;
    }

    /**
     * Execute the job.
     */
    public function handle(DaftraController $daftraService): void
    {
        $this->daftraService = $daftraService;

        $order = Order::find($this->order_id);

        if (! $order) {
            Log::error("Daftra Refund: Order not found with ID: {$this->order_id}");

            return;
        }

        $refund = Refund::with(['items', 'inventory_source'])->find($this->refund_id);

        if (! $refund) {
            Log::error("Daftra Refund: Refund not found with ID: {$this->refund_id}");

            return;
        }

        $address = $order->shipping_address ?: $order->billing_address;

        if (! $address) {
            Log::error("Daftra Refund: Address not found for order ID: {$this->order_id}");

            return;
        }

        $clientId = $this->findOrCreateClient($order, $address);

        if (! $clientId) {
            Log::error("Daftra Refund: Could not find or create client for order ID: {$this->order_id}");

            return;
        }

        $this->createRefund($order, $refund, $address, $clientId);
    }

    /**
     * Create refund receipt in Daftra.
     */
    protected function createRefund($order, $refund, $address, $clientId)
    {
        try {
            $refundDetails = $this->buildRefundDetails($order, $refund, $address, $clientId);

            $refundItems = $this->buildRefundItems($order, $refund);

            $payments = $this->buildRefundPayments($order, $refund);

            $refundData = $this->daftraService->buildRefundData(
                $refundDetails,
                $refundItems,
                $payments,
                $order->order_currency_code ?? 'KWD'
            );

            $result = $this->daftraService->createRefund($refundData);

            $daftraRefundId = $result['id'] ?? $result['data']['RefundReceipt']['id'] ?? null;

            if ($daftraRefundId) {
                Log::info("Daftra Refund ID: {$daftraRefundId} created successfully for Order: #{$order->id}, Refund: #{$refund->id}");

                Refund::withoutEvents(function () use ($refund, $daftraRefundId) {
                    $refund->daftra_refund_id = $daftraRefundId;
                    $refund->save();
                });
            } else {
                Log::error('Daftra Refund created but ID not found in response', ['response' => $result]);
            }

            return $result;
        } catch (Exception $e) {
            Log::error("Failed to create Daftra refund for order #{$order->id}: ".$e->getMessage());

            throw $e;
        }
    }

    /**
     * Build refund details array.
     */
    protected function buildRefundDetails($order, $refund, $address, $clientId): array
    {
        $storeId = $refund->inventory_source_id ? (int) $refund->inventory_source_id : 1;

        return [
            'no' => $this->generateRefundNumber($order, $refund),
            'po_number' => (string) ($order->increment_id ?? $order->id),
            'name' => trim($address->first_name.' '.$address->last_name),
            'client_id' => $clientId,
            'subscription_id' => $order->daftra_invoice_id ? (int) $order->daftra_invoice_id : null,
            'store_id' => $storeId,
            'client_business_name' => '',
            'client_first_name' => $address->first_name,
            'client_last_name' => $address->last_name,
            'client_email' => $address->email ?: ($order->customer_email ?: 'no-email@example.com'),
            'client_address1' => (string) $address->address1,
            'client_address2' => $address->address2 ?? '',
            'client_postal_code' => $address->postcode ?? '',
            'client_city' => $address->city ?? '',
            'client_state' => $address->state ?? '',
            'client_country_code' => $address->country ?? 'KW',
            'date' => now()->format('Y-m-d'),
            'draft' => false,
            'notes' => $this->buildRefundNotes($order, $refund),
            'html_notes' => $this->buildRefundNotes($order, $refund),
            'currency_code' => $order->order_currency_code ?? 'KWD',
            'is_offline' => false,
        ];
    }

    /**
     * Build refund items.
     */
    protected function buildRefundItems($order, $refund): array
    {
        $items = [];
        $storeId = $refund->inventory_source_id ? (int) $refund->inventory_source_id : 1;

        foreach ($refund->items as $refundItem) {
            $items[] = [
                'invoice_id' => $order->daftra_invoice_id,
                'item' => $refundItem->name,
                'description' => 'SKU: '.$refundItem->sku,
                'unit_price' => (float) $refundItem->base_price,
                'quantity' => (float) $refundItem->qty,
                'product_id' => null,
                'discount' => (float) ($refundItem->base_discount_amount ?? 0),
                'discount_type' => 2,
                'tax1' => (float) ($refundItem->base_tax_amount ?? 0),
                'tax2' => 0,
                'store_id' => $storeId,
                'col_3' => null,
                'col_4' => null,
                'col_5' => null,
            ];
        }

        if ($refund->base_shipping_amount > 0) {
            $items[] = [
                'invoice_id' => $order->daftra_invoice_id,
                'item' => 'مرتجع رسوم الشحن',
                'description' => 'مرتجع رسوم الشحن',
                'unit_price' => (float) $refund->base_shipping_amount,
                'quantity' => 1,
                'product_id' => null,
                'discount' => 0,
                'discount_type' => 2,
                'tax1' => 0,
                'tax2' => 0,
                'store_id' => $storeId,
                'col_3' => null,
                'col_4' => null,
                'col_5' => null,
            ];
        }

        if ($refund->base_adjustment_refund > 0) {
            $items[] = [
                'invoice_id' => $order->daftra_invoice_id,
                'item' => 'استرداد تعديل',
                'description' => 'استرداد مبلغ تعديل',
                'unit_price' => (float) $refund->base_adjustment_refund,
                'quantity' => 1,
                'product_id' => null,
                'discount' => 0,
                'discount_type' => 2,
                'tax1' => 0,
                'tax2' => 0,
                'store_id' => $storeId,
                'col_3' => null,
                'col_4' => null,
                'col_5' => null,
            ];
        }

        return $items;
    }

    /**
     * Build refund payments.
     */
    protected function buildRefundPayments($order, $refund): array
    {
        $payments = [];

        $paymentMethod = $order->payment ? $order->payment->method : 'cash';
        $mappedMethod = $this->mapPaymentMethodForRefund($paymentMethod);
        $treasuryId = $this->getTreasuryId($paymentMethod);

        $payments[] = [
            'invoice_id' => $order->daftra_invoice_id,
            'payment_method' => $mappedMethod,
            'amount' => (float) $refund->base_grand_total,
            'transaction_id' => 'refund_'.$refund->id.'_'.time(),
            'treasury_id' => $treasuryId,
            'date' => now()->format('Y-m-d H:i:s'),
            'status' => 1,
            'staff_id' => 0,
            'email' => null,
            'notes' => "Refund payment for Refund #{$refund->id}",
            'response_code' => null,
            'response_message' => null,
            'currency_code' => $order->order_currency_code ?? 'KWD',
            'first_name' => null,
            'last_name' => null,
            'address1' => null,
            'address2' => null,
            'city' => null,
            'state' => null,
            'postal_code' => null,
            'country_code' => null,
            'phone1' => null,
            'phone2' => null,
            'transaction_type' => null,
            'processed' => true,
            'attachment' => null,
            'receipt_notes' => null,
        ];

        return $payments;
    }

    /**
     * Map payment method for Daftra.
     */
    protected function mapPaymentMethodForRefund(string $paymentMethod): string
    {
        $methodMapping = [
            'cashondelivery' => 'cash',
            'moneytransfer' => 'bank_transfer',
            'upayments_knet' => 'knet',
            'upayments_credit_card' => 'credit_card',
            'upayments_apple_pay' => 'apple_pay',
            'upayments_google_pay' => 'google_pay',
            'upayments_samsung_pay' => 'samsung_pay',
            'upayments' => 'upayments',
            'knet' => 'knet',
        ];

        return $methodMapping[$paymentMethod] ?? 'cash';
    }

    /**
     * Get treasury ID based on payment method.
     */
    protected function getTreasuryId(string $paymentMethod): int
    {
        if (str_starts_with($paymentMethod, 'upayments')) {
            return 3; // يوباي منت (UPayments)
        }

        return 1; // Default Main Treasury
    }

    /**
     * Generate refund receipt number.
     */
    protected function generateRefundNumber($order, $refund): string
    {
        return 'RF-'.($order->increment_id ?? $order->id).'-'.$refund->id;
    }

    /**
     * Build refund notes.
     */
    protected function buildRefundNotes($order, $refund): string
    {
        $notes = [];
        $notes[] = "Refund #{$refund->id} for Order #".($order->increment_id ?? $order->id);

        if ($order->daftra_invoice_id) {
            $notes[] = "Original Daftra Invoice ID: {$order->daftra_invoice_id}";
        }

        $notes[] = 'Refund Date: '.now()->format('Y-m-d H:i:s');

        if ($refund->inventory_source) {
            $notes[] = "Returned to Store: {$refund->inventory_source->name} (ID: {$refund->inventory_source_id})";
        }

        return implode("\n", $notes);
    }

    /**
     * Find or create Daftra client.
     */
    protected function findOrCreateClient($order, $address)
    {
        if ($order->daftra_invoice_id) {
            try {
                $invoiceResponse = $this->daftraService->getInvoice($order->daftra_invoice_id);
                $clientId = $invoiceResponse['data']['Invoice']['client_id']
                    ?? $invoiceResponse['client_id']
                    ?? null;

                if ($clientId) {
                    return $clientId;
                }
            } catch (Exception $e) {
                Log::warning("Could not fetch Daftra invoice {$order->daftra_invoice_id}: ".$e->getMessage());
            }
        }

        $email = $order->customer_email ?: ($address->email ?: '');
        $firstName = $order->customer_first_name ?: ($address->first_name ?: 'Customer');
        $lastName = $order->customer_last_name ?: ($address->last_name ?: '');
        $phone = $address->phone ?: ($order->billing_address ? $order->billing_address->phone : '');

        if ($email) {
            try {
                $clientsResponse = $this->daftraService->getClients(1, 100);

                if (isset($clientsResponse['data']) && is_array($clientsResponse['data'])) {
                    foreach ($clientsResponse['data'] as $clientItem) {
                        if (isset($clientItem['Client']) && $clientItem['Client']['email'] === $email) {
                            return $clientItem['Client']['id'];
                        }
                    }
                }
            } catch (Exception $e) {
                Log::warning('Daftra find client failed: '.$e->getMessage());
            }
        }

        try {
            $clientData = [
                'Client' => [
                    'business_name' => trim($firstName.' '.$lastName) ?: 'Customer',
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email ?: ('customer_'.$order->id.'@example.com'),
                    'phone1' => $phone,
                    'address1' => $address->address1 ?: '',
                    'city' => $address->city ?: '',
                    'country_code' => $address->country ?: 'KW',
                ],
            ];

            $response = $this->daftraService->createClient($clientData);

            if (isset($response['id'])) {
                return $response['id'];
            } elseif (isset($response['data']['Client']['id'])) {
                return $response['data']['Client']['id'];
            }

            Log::error('Daftra Create Client returned unexpected format', ['response' => $response]);
        } catch (Exception $e) {
            Log::error('Daftra Create Client Exception: '.$e->getMessage());
        }

        return null;
    }
}
