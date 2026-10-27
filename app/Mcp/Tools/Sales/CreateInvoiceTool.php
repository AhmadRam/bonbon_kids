<?php

namespace App\Mcp\Tools\Sales;

use App\Mcp\Tools\BaseAdminTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Sales\Repositories\OrderRepository;

#[Name('create_invoice')]
#[Description('Create an invoice for an order. Invoices all pending order items if no specific items are provided.')]
class CreateInvoiceTool extends BaseAdminTool
{
    public function handle(
        Request $request,
        OrderRepository $orderRepository,
        InvoiceRepository $invoiceRepository
    ): Response {
        if ($authError = $this->checkPermission('sales.invoices.create')) {
            return $authError;
        }

        $orderId = $request->get('order_id');
        $incrementId = $request->get('increment_id');

        if (! $orderId && ! $incrementId) {
            return $this->errorResponse('Either order_id or increment_id must be provided.');
        }

        $order = $orderId ? $orderRepository->find($orderId) : $orderRepository->findOneByField('increment_id', $incrementId);

        if (! $order) {
            return $this->errorResponse('Order not found.');
        }

        if (! $order->canInvoice()) {
            return $this->errorResponse("Order #{$order->increment_id} cannot be invoiced (already invoiced, canceled, or completed).");
        }

        $items = [];
        foreach ($order->items as $item) {
            $qtyToInvoice = $item->qty_to_invoice;
            if ($qtyToInvoice > 0) {
                $items[$item->id] = $qtyToInvoice;
            }
        }

        if (empty($items)) {
            return $this->errorResponse('No remaining items found to invoice for this order.');
        }

        $invoice = $invoiceRepository->create([
            'order_id' => $order->id,
            'invoice' => [
                'items' => $items,
            ],
        ]);

        return $this->successResponse("Invoice #{$invoice->increment_id} created successfully for order #{$order->increment_id}.", [
            'invoice_id' => $invoice->id,
            'invoice_increment_id' => $invoice->increment_id,
            'order_id' => $order->id,
            'order_increment_id' => $order->increment_id,
            'grand_total' => (float) $invoice->grand_total,
            'state' => $invoice->state,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'order_id' => $schema->integer()->description('The order database ID.'),
            'increment_id' => $schema->string()->description('The order public increment ID (e.g. 1).'),
        ];
    }
}
