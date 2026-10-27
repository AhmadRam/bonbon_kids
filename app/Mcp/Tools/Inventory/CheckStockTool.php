<?php

namespace App\Mcp\Tools\Inventory;

use App\Mcp\Tools\BaseAdminTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Webkul\Product\Repositories\ProductRepository;

#[Name('check_stock')]
#[Description('Check stock availability and quantities across all warehouses for a given SKU or product ID.')]
class CheckStockTool extends BaseAdminTool
{
    public function handle(Request $request, ProductRepository $productRepository): Response
    {
        if ($authError = $this->checkPermission('catalog.products')) {
            return $authError;
        }

        $id = $request->get('id');
        $sku = $request->get('sku');

        if (! $id && ! $sku) {
            return $this->errorResponse('Either product id or sku must be provided.');
        }

        $product = $id
            ? $productRepository->with('inventories.inventory_source')->find($id)
            : $productRepository->with('inventories.inventory_source')->findOneByField('sku', $sku);

        if (! $product) {
            return $this->errorResponse('Product not found.');
        }

        $sources = $product->inventories->map(fn ($inv) => [
            'source_id' => $inv->inventory_source_id,
            'source_name' => $inv->inventory_source?->name ?? 'Default',
            'quantity' => (int) $inv->qty,
        ]);

        return $this->successResponse('Stock check completed.', [
            'id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'total_quantity' => (int) $product->inventories->sum('qty'),
            'warehouses' => $sources,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'sku' => $schema->string()->description('The product SKU to check.'),
            'id' => $schema->integer()->description('The product ID to check.'),
        ];
    }
}
