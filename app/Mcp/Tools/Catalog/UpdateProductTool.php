<?php

namespace App\Mcp\Tools\Catalog;

use App\Mcp\Tools\BaseAdminTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Webkul\Product\Repositories\ProductRepository;

#[Name('update_product')]
#[Description('Update product details like price, special price, name, status, or description by SKU or ID.')]
class UpdateProductTool extends BaseAdminTool
{
    public function handle(Request $request, ProductRepository $productRepository): Response
    {
        if ($authError = $this->checkPermission('catalog.products.edit')) {
            return $authError;
        }

        $id = $request->get('id');
        $sku = $request->get('sku');

        if (! $id && ! $sku) {
            return $this->errorResponse('Either product id or sku must be provided.');
        }

        $product = $id ? $productRepository->find($id) : $productRepository->findOneByField('sku', $sku);

        if (! $product) {
            return $this->errorResponse('Product not found.');
        }

        $updateData = [
            'channel' => 'default',
            'locale' => 'en',
        ];

        if ($request->has('name')) {
            $updateData['name'] = $request->get('name');
        }

        if ($request->has('price')) {
            $updateData['price'] = (float) $request->get('price');
        }

        if ($request->has('special_price')) {
            $updateData['special_price'] = (float) $request->get('special_price');
        }

        if ($request->has('status')) {
            $updateData['status'] = (int) $request->get('status');
        }

        if ($request->has('description')) {
            $updateData['description'] = $request->get('description');
        }

        if ($request->has('short_description')) {
            $updateData['short_description'] = $request->get('short_description');
        }

        if ($request->has('weight')) {
            $updateData['weight'] = (float) $request->get('weight');
        }

        $updatedProduct = $productRepository->update($updateData, $product->id);

        return $this->successResponse("Product [{$updatedProduct->sku}] updated successfully.", [
            'id' => $updatedProduct->id,
            'sku' => $updatedProduct->sku,
            'name' => $updatedProduct->name,
            'price' => (float) $updatedProduct->price,
            'status' => (bool) $updatedProduct->status,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'sku' => $schema->string()->description('The product SKU to update.'),
            'id' => $schema->integer()->description('The product ID to update.'),
            'name' => $schema->string()->description('New product name.'),
            'price' => $schema->number()->description('New product regular price.'),
            'special_price' => $schema->number()->description('Promotional/special discount price.'),
            'status' => $schema->boolean()->description('Product status: true for active, false for disabled.'),
            'description' => $schema->string()->description('Updated full description.'),
            'short_description' => $schema->string()->description('Updated short description.'),
            'weight' => $schema->number()->description('Updated product weight.'),
        ];
    }
}
