<?php

namespace App\Mcp\Tools\Catalog;

use App\Mcp\Tools\BaseAdminTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Webkul\Product\Repositories\ProductRepository;

#[Name('list_products')]
#[Description('List products with optional search query, status filter, has_images filter, and pagination.')]
class ListProductsTool extends BaseAdminTool
{
    public function handle(Request $request, ProductRepository $productRepository): Response
    {
        if ($authError = $this->checkPermission('catalog.products')) {
            return $authError;
        }

        $query = $request->get('query');
        $limit = (int) ($request->get('limit', 20));
        $status = $request->get('status');
        $hasImages = $request->get('has_images');

        $productsQuery = $productRepository->getModel()->newQuery()
            ->with(['inventories', 'categories', 'images']);

        if ($query) {
            $productsQuery->where(function ($q) use ($query) {
                $q->where('sku', 'like', "%{$query}%")
                    ->orWhereHas('attribute_values', function ($aq) use ($query) {
                        $aq->where('text_value', 'like', "%{$query}%");
                    });
            });
        }

        if ($status !== null && $status !== '') {
            $productsQuery->whereHas('attribute_values', function ($aq) use ($status) {
                $aq->whereHas('attribute', fn ($a) => $a->where('code', 'status'))
                    ->where('boolean_value', (bool) $status);
            });
        }

        if ($hasImages !== null && $hasImages !== '') {
            if ((bool) $hasImages) {
                $productsQuery->whereHas('images');
            } else {
                $productsQuery->whereDoesntHave('images');
            }
        }

        $paginator = $productsQuery->orderBy('id', 'desc')->paginate(min($limit, 50));

        $items = collect($paginator->items())->map(function ($product) {
            return [
                'id' => $product->id,
                'sku' => $product->sku,
                'type' => $product->type,
                'name' => $product->name,
                'price' => (float) $product->price,
                'status' => (bool) $product->status,
                'has_images' => $product->images->isNotEmpty(),
                'images_count' => $product->images->count(),
                'total_quantity' => (int) $product->inventories->sum('qty'),
                'created_at' => $product->created_at?->toIso8601String(),
            ];
        });

        return $this->successResponse('Products retrieved successfully', [
            'products' => $items,
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
            'query' => $schema->string()->description('Optional search keyword to match against SKU or product name.'),
            'status' => $schema->boolean()->description('Filter by active (true) or inactive (false) status.'),
            'has_images' => $schema->boolean()->description('Filter products by image presence: true (has images) or false (missing images).'),
            'limit' => $schema->integer()->description('Number of results to return (default 20, max 50).'),
            'page' => $schema->integer()->description('Page number for pagination.'),
        ];
    }
}
