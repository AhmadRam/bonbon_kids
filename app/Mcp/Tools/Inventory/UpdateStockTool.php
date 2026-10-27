<?php

namespace App\Mcp\Tools\Inventory;

use App\Mcp\Tools\BaseAdminTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Webkul\Inventory\Repositories\InventorySourceRepository;
use Webkul\Product\Repositories\ProductInventoryRepository;
use Webkul\Product\Repositories\ProductRepository;

#[Name('update_stock')]
#[Description('Update product stock quantity in a specific warehouse or the default warehouse.')]
class UpdateStockTool extends BaseAdminTool
{
    public function handle(
        Request $request,
        ProductRepository $productRepository,
        ProductInventoryRepository $productInventoryRepository,
        InventorySourceRepository $inventorySourceRepository
    ): Response {
        if ($authError = $this->checkPermission('catalog.products.edit')) {
            return $authError;
        }

        $id = $request->get('id');
        $sku = $request->get('sku');
        $qty = $request->get('quantity');
        $sourceId = $request->get('inventory_source_id');

        if ((! $id && ! $sku) || $qty === null) {
            return $this->errorResponse('Product identifier (id or sku) and quantity are required.');
        }

        $product = $id ? $productRepository->find($id) : $productRepository->findOneByField('sku', $sku);

        if (! $product) {
            return $this->errorResponse('Product not found.');
        }

        if (! $sourceId) {
            $defaultSource = $inventorySourceRepository->first();
            $sourceId = $defaultSource ? $defaultSource->id : 1;
        }

        $productInventoryRepository->saveInventories([
            'inventories' => [
                $sourceId => (int) $qty,
            ],
        ], $product);

        $newTotal = (int) $productInventoryRepository->where('product_id', $product->id)->sum('qty');

        return $this->successResponse("Stock updated successfully for SKU [{$product->sku}].", [
            'id' => $product->id,
            'sku' => $product->sku,
            'inventory_source_id' => $sourceId,
            'updated_warehouse_qty' => (int) $qty,
            'total_stock' => $newTotal,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'quantity' => $schema->integer()->description('The new stock quantity to set.')->required(),
            'sku' => $schema->string()->description('The product SKU to update.'),
            'id' => $schema->integer()->description('The product ID to update.'),
            'inventory_source_id' => $schema->integer()->description('Warehouse/inventory source ID. If omitted, uses default warehouse.'),
        ];
    }
}
