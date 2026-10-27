<?php

namespace App\Mcp\Tools\Catalog;

use App\Mcp\Tools\BaseAdminTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Webkul\Attribute\Repositories\AttributeFamilyRepository;
use Webkul\Inventory\Repositories\InventorySourceRepository;
use Webkul\Product\Repositories\ProductInventoryRepository;
use Webkul\Product\Repositories\ProductRepository;

#[Name('create_product')]
#[Description('Create a new simple product in the store catalog with name, SKU, price, and inventory.')]
class CreateProductTool extends BaseAdminTool
{
    public function handle(
        Request $request,
        ProductRepository $productRepository,
        AttributeFamilyRepository $attributeFamilyRepository,
        ProductInventoryRepository $productInventoryRepository,
        InventorySourceRepository $inventorySourceRepository
    ): Response {
        if ($authError = $this->checkPermission('catalog.products.create')) {
            return $authError;
        }

        $sku = trim((string) $request->get('sku'));
        $name = trim((string) $request->get('name'));
        $price = (float) $request->get('price');
        $quantity = (int) ($request->get('quantity', 0));
        $description = $request->get('description', $name);
        $shortDescription = $request->get('short_description', $description);
        $weight = (float) ($request->get('weight', 1));
        $categoryIds = (array) ($request->get('categories', []));

        if (! $sku || ! $name || $price <= 0) {
            return $this->errorResponse('Validation failed: sku, name, and positive price are required.');
        }

        if ($productRepository->findOneByField('sku', $sku)) {
            return $this->errorResponse("Product with SKU [{$sku}] already exists.");
        }

        $family = $attributeFamilyRepository->first();
        $familyId = $family ? $family->id : 1;

        $inventorySource = $inventorySourceRepository->first();
        $sourceId = $inventorySource ? $inventorySource->id : 1;

        $channel = 'default';
        $locale = 'en';

        // 1. Create base product
        $product = $productRepository->create([
            'type' => 'simple',
            'attribute_family_id' => $familyId,
            'sku' => $sku,
        ]);

        $urlKey = Str::slug($name).'-'.strtolower(Str::random(5));

        // 2. Update with attributes
        $updateData = [
            'channel' => $channel,
            'locale' => $locale,
            'name' => $name,
            'url_key' => $urlKey,
            'price' => $price,
            'status' => 1,
            'visible_individually' => 1,
            'weight' => $weight,
            'short_description' => $shortDescription,
            'description' => $description,
            'categories' => $categoryIds,
            'channels' => [$channel],
            'inventories' => [
                $sourceId => $quantity,
            ],
        ];

        $updatedProduct = $productRepository->update($updateData, $product->id);

        return $this->successResponse('Product created successfully', [
            'id' => $updatedProduct->id,
            'sku' => $updatedProduct->sku,
            'name' => $updatedProduct->name,
            'price' => (float) $updatedProduct->price,
            'quantity' => $quantity,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('The product name.')->required(),
            'sku' => $schema->string()->description('Unique SKU for the product.')->required(),
            'price' => $schema->number()->description('Product price.')->required(),
            'quantity' => $schema->integer()->description('Initial inventory quantity. Defaults to 0.'),
            'description' => $schema->string()->description('Full product description.'),
            'short_description' => $schema->string()->description('Short product summary.'),
            'weight' => $schema->number()->description('Product weight. Defaults to 1.0.'),
            'categories' => $schema->array()->description('Array of category IDs to assign the product to.'),
        ];
    }
}
