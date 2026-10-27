<?php

namespace App\Mcp\Tools\Customers;

use App\Mcp\Tools\BaseAdminTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Webkul\Customer\Repositories\CustomerRepository;

#[Name('update_customer_status')]
#[Description('Activate or suspend/deactivate a customer account.')]
class UpdateCustomerStatusTool extends BaseAdminTool
{
    public function handle(Request $request, CustomerRepository $customerRepository): Response
    {
        if ($authError = $this->checkPermission('customers.customers.edit')) {
            return $authError;
        }

        $id = $request->get('id');
        $email = $request->get('email');
        $status = $request->get('status');

        if ((! $id && ! $email) || $status === null) {
            return $this->errorResponse('Customer identifier (id or email) and status (boolean) are required.');
        }

        $customer = $id ? $customerRepository->find($id) : $customerRepository->findOneByField('email', $email);

        if (! $customer) {
            return $this->errorResponse('Customer not found.');
        }

        $customerRepository->update([
            'status' => (int) $status,
        ], $customer->id);

        $stateText = $status ? 'activated' : 'suspended';

        return $this->successResponse("Customer [{$customer->email}] has been {$stateText}.", [
            'id' => $customer->id,
            'email' => $customer->email,
            'status' => (bool) $status,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'status' => $schema->boolean()->description('True to activate the customer, false to suspend.')->required(),
            'id' => $schema->integer()->description('The customer database ID.'),
            'email' => $schema->string()->description('The customer registered email address.'),
        ];
    }
}
