<?php

namespace App\Mcp\Tools\Sales;

use App\Mcp\Tools\BaseAdminTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Webkul\Sales\Repositories\OrderRepository;

#[Name('cancel_order')]
#[Description('Cancel an order and automatically return its items to the store inventory.')]
class CancelOrderTool extends BaseAdminTool
{
    public function handle(Request $request, OrderRepository $orderRepository): Response
    {
        if ($authError = $this->checkPermission('sales.orders.cancel')) {
            return $authError;
        }

        $id = $request->get('id');
        $incrementId = $request->get('increment_id');

        if (! $id && ! $incrementId) {
            return $this->errorResponse('Either order id or increment_id must be provided.');
        }

        $order = $id ? $orderRepository->find($id) : $orderRepository->findOneByField('increment_id', $incrementId);

        if (! $order) {
            return $this->errorResponse('Order not found.');
        }

        if (! $order->canCancel()) {
            return $this->errorResponse("Order #{$order->increment_id} cannot be canceled (already shipped, canceled, or completed).");
        }

        $result = $orderRepository->cancel($order);

        if (! $result) {
            return $this->errorResponse("Failed to cancel order #{$order->increment_id}.");
        }

        return $this->successResponse("Order #{$order->increment_id} has been canceled successfully and stock was restored.", [
            'order_id' => $order->id,
            'increment_id' => $order->increment_id,
            'status' => 'canceled',
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The order database ID.'),
            'increment_id' => $schema->string()->description('The order increment ID.'),
        ];
    }
}
