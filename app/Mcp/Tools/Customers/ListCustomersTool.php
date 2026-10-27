<?php

namespace App\Mcp\Tools\Customers;

use App\Mcp\Tools\BaseAdminTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Webkul\Customer\Repositories\CustomerRepository;

#[Name('list_customers')]
#[Description('Search and list registered customers with their contact info, status, and order count.')]
class ListCustomersTool extends BaseAdminTool
{
    public function handle(Request $request, CustomerRepository $customerRepository): Response
    {
        if ($authError = $this->checkPermission('customers.customers')) {
            return $authError;
        }

        $query = $request->get('query');
        $status = $request->get('status');
        $limit = (int) ($request->get('limit', 20));

        $customerQuery = $customerRepository->getModel()->newQuery()
            ->withCount('orders')
            ->with('group');

        if ($query) {
            $customerQuery->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%");
            });
        }

        if ($status !== null && $status !== '') {
            $customerQuery->where('status', (bool) $status);
        }

        $paginator = $customerQuery->orderBy('id', 'desc')->paginate(min($limit, 50));

        $customers = collect($paginator->items())->map(fn ($c) => [
            'id' => $c->id,
            'name' => "{$c->first_name} {$c->last_name}",
            'email' => $c->email,
            'phone' => $c->phone,
            'gender' => $c->gender,
            'status' => (bool) $c->status,
            'group' => $c->group?->name,
            'total_orders' => (int) $c->orders_count,
            'registered_at' => $c->created_at?->toIso8601String(),
        ]);

        return $this->successResponse('Customers retrieved successfully.', [
            'customers' => $customers,
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
            'query' => $schema->string()->description('Search keyword matching name, email, or phone.'),
            'status' => $schema->boolean()->description('Filter by active (true) or suspended (false) status.'),
            'limit' => $schema->integer()->description('Max customers to return (default 20, max 50).'),
            'page' => $schema->integer()->description('Pagination page number.'),
        ];
    }
}
