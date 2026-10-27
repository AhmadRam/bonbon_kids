<?php

namespace App\Mcp\Tools\Sales;

use App\Mcp\Tools\BaseAdminTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Webkul\Sales\Repositories\OrderRepository;

#[Name('list_orders')]
#[Description('List store orders with optional status filter, customer filter, and pagination.')]
class ListOrdersTool extends BaseAdminTool
{
    public function handle(Request $request, OrderRepository $orderRepository): Response
    {
        if ($authError = $this->checkPermission('sales.orders')) {
            return $authError;
        }

        $status = $request->get('status');
        $limit = (int) ($request->get('limit', 20));
        $customerId = $request->get('customer_id');

        $query = $orderRepository->getModel()->newQuery()
            ->with(['items', 'customer']);

        if ($status) {
            $query->where('status', strtolower($status));
        }

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        $paginator = $query->orderBy('id', 'desc')->paginate(min($limit, 50));

        $orders = collect($paginator->items())->map(fn ($order) => [
            'id' => $order->id,
            'increment_id' => $order->increment_id,
            'status' => $order->status,
            'status_label' => $order->status_label,
            'customer_name' => $order->customer_full_name,
            'customer_email' => $order->customer_email,
            'grand_total' => (float) $order->grand_total,
            'order_currency_code' => $order->order_currency_code,
            'total_items_count' => (int) $order->total_qty_ordered,
            'created_at' => $order->created_at?->toIso8601String(),
        ]);

        return $this->successResponse('Orders retrieved successfully.', [
            'orders' => $orders,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'status' => $schema->string()->description('Filter by status: pending, processing, completed, canceled, closed.'),
            'customer_id' => $schema->integer()->description('Filter orders by a specific customer ID.'),
            'limit' => $schema->integer()->description('Max orders to return (default 20, max 50).'),
            'page' => $schema->integer()->description('Pagination page number.'),
        ];
    }
}
