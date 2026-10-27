<?php

namespace App\Mcp\Tools\Inventory;

use App\Mcp\Tools\BaseAdminTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Webkul\Product\Repositories\ProductRepository;

#[Name('get_low_stock_alerts')]
#[Description('List products that are running low on stock (below a specified quantity threshold).')]
class GetLowStockAlertsTool extends BaseAdminTool
{
    public function handle(Request $request, ProductRepository $productRepository): Response
    {
        if ($authError = $this->checkPermission('catalog.products')) {
            return $authError;
        }

        $threshold = (int) ($request->get('threshold', 5));
        $limit = (int) ($request->get('limit', 20));

        // Group product inventories by product_id and find where sum(qty) <= threshold
        $lowStockProductIds = DB::table('product_inventories')
            ->select('product_id', DB::raw('SUM(qty) as total_qty'))
            ->groupBy('product_id')
            ->having('total_qty', '<=', $threshold)
            ->limit(min($limit, 50))
            ->pluck('total_qty', 'product_id');

        if ($lowStockProductIds->isEmpty()) {
            return $this->successResponse('No low stock products found.', ['products' => [], 'count' => 0]);
        }

        $products = $productRepository->getModel()->whereIn('id', $lowStockProductIds->keys())->get();

        $items = $products->map(fn ($p) => [
            'id' => $p->id,
            'sku' => $p->sku,
            'name' => $p->name,
            'price' => (float) $p->price,
            'current_quantity' => (int) ($lowStockProductIds[$p->id] ?? 0),
            'status' => (bool) $p->status,
        ]);

        return $this->successResponse('Low stock alerts retrieved successfully.', [
            'threshold' => $threshold,
            'count' => $items->count(),
            'products' => $items,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'threshold' => $schema->integer()->description('Stock quantity threshold (default 5). Returns items with quantity <= threshold.'),
            'limit' => $schema->integer()->description('Max number of items to return (default 20).'),
        ];
    }
}
