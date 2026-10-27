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
#[Description('List products with optional search query, status filter, and pagination.')]
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

        $productsQuery = $productRepository->getModel()->newQuery()
            ->with(['inventories', 'categories']);

        if ($query) {
            $productsQuery->where(function ($q) use ($query) {
                $q->where('sku', 'like', "%{$query}%")
                    ->orWhereHas('attribute_values', function ($aq) use ($query) {
                        $aq->where('text_value', 'like', "%{$query}%");
                    });
            });
        }

        if ($status !== null && $status !== '') {
            $productsQuery->where('status', (bool) $status);
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
            'limit' => $schema->integer()->description('Number of results to return (default 20, max 50).'),
            'page' => $schema->integer()->description('Page number for pagination.'),
        ];
    }
}
