<?php

namespace App\Mcp\Tools\Catalog;

use App\Mcp\Tools\BaseAdminTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Webkul\Product\Repositories\ProductRepository;

#[Name('delete_product')]
#[Description('Delete a product from the catalog by its SKU or ID.')]
class DeleteProductTool extends BaseAdminTool
{
    public function handle(Request $request, ProductRepository $productRepository): Response
    {
        if ($authError = $this->checkPermission('catalog.products.delete')) {
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

        $deletedSku = $product->sku;
        $productRepository->delete($product->id);

        return $this->successResponse("Product [{$deletedSku}] has been deleted successfully.");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'sku' => $schema->string()->description('The product SKU to delete.'),
            'id' => $schema->integer()->description('The product ID to delete.'),
        ];
    }
}
