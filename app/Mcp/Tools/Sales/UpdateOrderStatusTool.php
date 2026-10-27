<?php

namespace App\Mcp\Tools\Sales;

use App\Mcp\Tools\BaseAdminTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Repositories\OrderRepository;

#[Name('update_order_status')]
#[Description('Update the state/status of an order (e.g., processing, completed, canceled, closed, pending).')]
class UpdateOrderStatusTool extends BaseAdminTool
{
    public function handle(Request $request, OrderRepository $orderRepository): Response
    {
        if ($authError = $this->checkPermission('sales.orders.edit')) {
            return $authError;
        }

        $id = $request->get('id');
        $incrementId = $request->get('increment_id');
        $newStatus = strtolower(trim((string) $request->get('status')));

        if (! $id && ! $incrementId) {
            return $this->errorResponse('Either order id or increment_id must be provided.');
        }

        $validStatuses = [
            Order::STATUS_PENDING,
            Order::STATUS_PROCESSING,
            Order::STATUS_COMPLETED,
            Order::STATUS_CANCELED,
            Order::STATUS_CLOSED,
            Order::STATUS_FRAUD,
        ];

        if (! in_array($newStatus, $validStatuses)) {
            return $this->errorResponse("Invalid status [{$newStatus}]. Allowed values: ".implode(', ', $validStatuses));
        }

        $order = $id ? $orderRepository->find($id) : $orderRepository->findOneByField('increment_id', $incrementId);

        if (! $order) {
            return $this->errorResponse('Order not found.');
        }

        $previousStatus = $order->status;
        $orderRepository->updateOrderStatus($order, $newStatus);

        return $this->successResponse("Order #{$order->increment_id} status updated from [{$previousStatus}] to [{$newStatus}].", [
            'id' => $order->id,
            'increment_id' => $order->increment_id,
            'previous_status' => $previousStatus,
            'current_status' => $newStatus,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'status' => $schema->string()->description('New order status: pending, processing, completed, canceled, closed.')->required(),
            'id' => $schema->integer()->description('The order database ID.'),
            'increment_id' => $schema->string()->description('The order public increment ID (e.g. 1).'),
        ];
    }
}
