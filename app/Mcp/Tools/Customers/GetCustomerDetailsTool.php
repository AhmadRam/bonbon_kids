<?php

namespace App\Mcp\Tools\Customers;

use App\Mcp\Tools\BaseAdminTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Webkul\Customer\Repositories\CustomerRepository;

#[Name('get_customer_details')]
#[Description('Get complete profile, addresses, and order history for a customer by ID or email.')]
class GetCustomerDetailsTool extends BaseAdminTool
{
    public function handle(Request $request, CustomerRepository $customerRepository): Response
    {
        if ($authError = $this->checkPermission('customers.customers')) {
            return $authError;
        }

        $id = $request->get('id');
        $email = $request->get('email');

        if (! $id && ! $email) {
            return $this->errorResponse('Either customer id or email must be provided.');
        }

        $customer = $id
            ? $customerRepository->with(['addresses', 'orders', 'group'])->find($id)
            : $customerRepository->with(['addresses', 'orders', 'group'])->findOneByField('email', $email);

        if (! $customer) {
            return $this->errorResponse('Customer not found.');
        }

        $details = [
            'id' => $customer->id,
            'name' => "{$customer->first_name} {$customer->last_name}",
            'email' => $customer->email,
            'phone' => $customer->phone,
            'gender' => $customer->gender,
            'date_of_birth' => $customer->date_of_birth,
            'status' => (bool) $customer->status,
            'group' => $customer->group?->name,
            'addresses' => $customer->addresses->map(fn ($a) => [
                'id' => $a->id,
                'address' => $a->address1,
                'city' => $a->city,
                'state' => $a->state,
                'country' => $a->country,
                'postcode' => $a->postcode,
                'phone' => $a->phone,
                'default_address' => (bool) $a->default_address,
            ]),
            'orders' => $customer->orders->take(10)->map(fn ($o) => [
                'id' => $o->id,
                'increment_id' => $o->increment_id,
                'status' => $o->status,
                'grand_total' => (float) $o->grand_total,
                'currency' => $o->order_currency_code,
                'created_at' => $o->created_at?->toIso8601String(),
            ]),
            'total_spent' => (float) $customer->orders->where('status', 'completed')->sum('grand_total'),
            'registered_at' => $customer->created_at?->toIso8601String(),
        ];

        return $this->successResponse('Customer details retrieved successfully.', ['customer' => $details]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The customer database ID.'),
            'email' => $schema->string()->description('The customer registered email address.'),
        ];
    }
}
