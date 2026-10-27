<?php

namespace App\Mcp\Tools\Sales;

use App\Mcp\Tools\BaseAdminTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Webkul\Sales\Repositories\OrderRepository;

#[Name('get_order_details')]
#[Description('Get complete details of a specific order by order ID or increment ID.')]
class GetOrderDetailsTool extends BaseAdminTool
{
    public function handle(Request $request, OrderRepository $orderRepository): Response
    {
        if ($authError = $this->checkPermission('sales.orders')) {
            return $authError;
        }

        $id = $request->get('id');
        $incrementId = $request->get('increment_id');

        if (! $id && ! $incrementId) {
            return $this->errorResponse('Either order id or increment_id must be provided.');
        }

        $order = $id
            ? $orderRepository->with(['items', 'customer', 'addresses', 'payment', 'invoices', 'shipments'])->find($id)
            : $orderRepository->with(['items', 'customer', 'addresses', 'payment', 'invoices', 'shipments'])->findOneByField('increment_id', $incrementId);

        if (! $order) {
            return $this->errorResponse('Order not found.');
        }

        $shippingAddress = $order->shipping_address;
        $billingAddress = $order->billing_address;

        $details = [
            'id' => $order->id,
            'increment_id' => $order->increment_id,
            'status' => $order->status,
            'status_label' => $order->status_label,
            'customer' => [
                'id' => $order->customer_id,
                'name' => $order->customer_full_name,
                'email' => $order->customer_email,
            ],
            'items' => $order->items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'sku' => $item->sku,
                'name' => $item->name,
                'qty_ordered' => (int) $item->qty_ordered,
                'qty_invoiced' => (int) $item->qty_invoiced,
                'qty_shipped' => (int) $item->qty_shipped,
                'price' => (float) $item->price,
                'total' => (float) $item->total,
            ]),
            'financials' => [
                'sub_total' => (float) $order->sub_total,
                'discount_amount' => (float) $order->discount_amount,
                'shipping_amount' => (float) $order->shipping_amount,
                'tax_amount' => (float) $order->tax_amount,
                'grand_total' => (float) $order->grand_total,
                'currency' => $order->order_currency_code,
            ],
            'payment' => [
                'method' => $order->payment?->method,
                'method_title' => $order->payment?->method_title,
            ],
            'shipping' => [
                'title' => $order->shipping_title,
                'address' => $shippingAddress ? [
                    'name' => "{$shippingAddress->first_name} {$shippingAddress->last_name}",
                    'address' => $shippingAddress->address1,
                    'city' => $shippingAddress->city,
                    'state' => $shippingAddress->state,
                    'country' => $shippingAddress->country,
                    'phone' => $shippingAddress->phone,
                ] : null,
            ],
            'invoices_count' => $order->invoices->count(),
            'shipments_count' => $order->shipments->count(),
            'created_at' => $order->created_at?->toIso8601String(),
        ];

        return $this->successResponse('Order details retrieved successfully.', ['order' => $details]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The order database ID.'),
            'increment_id' => $schema->string()->description('The public order increment ID (e.g., 1 or 10001).'),
        ];
    }
}
