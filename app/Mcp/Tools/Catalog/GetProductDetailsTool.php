<?php

namespace App\Mcp\Tools\Catalog;

use App\Mcp\Tools\BaseAdminTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Webkul\Product\Repositories\ProductRepository;

#[Name('get_product_details')]
#[Description('Retrieve complete details of a product using its SKU or ID.')]
class GetProductDetailsTool extends BaseAdminTool
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
            ? $productRepository->with(['inventories', 'categories', 'images'])->find($id)
            : $productRepository->with(['inventories', 'categories', 'images'])->findOneByField('sku', $sku);

        if (! $product) {
            return $this->errorResponse('Product not found.');
        }

        $details = [
            'id' => $product->id,
            'sku' => $product->sku,
            'type' => $product->type,
            'name' => $product->name,
            'url_key' => $product->url_key,
            'price' => (float) $product->price,
            'special_price' => $product->special_price ? (float) $product->special_price : null,
            'special_price_from' => $product->special_price_from,
            'special_price_to' => $product->special_price_to,
            'status' => (bool) $product->status,
            'short_description' => strip_tags((string) $product->short_description),
            'description' => strip_tags((string) $product->description),
            'weight' => (float) $product->weight,
            'total_quantity' => (int) $product->inventories->sum('qty'),
            'inventories' => $product->inventories->map(fn ($inv) => [
                'inventory_source_id' => $inv->inventory_source_id,
                'source_name' => $inv->inventory_source?->name,
                'quantity' => (int) $inv->qty,
            ]),
            'categories' => $product->categories->map(fn ($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
            ]),
            'images' => $product->images->map(fn ($img) => [
                'id' => $img->id,
                'url' => $img->url,
            ]),
            'created_at' => $product->created_at?->toIso8601String(),
            'updated_at' => $product->updated_at?->toIso8601String(),
        ];

        return $this->successResponse('Product details retrieved successfully', ['product' => $details]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'sku' => $schema->string()->description('The unique SKU of the product.'),
            'id' => $schema->integer()->description('The product ID.'),
        ];
    }
}
