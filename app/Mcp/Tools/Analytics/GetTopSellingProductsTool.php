<?php

namespace App\Mcp\Tools\Analytics;

use App\Mcp\Tools\BaseAdminTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Webkul\Admin\Helpers\Reporting\Product as ProductReporting;

#[Name('get_top_selling_products')]
#[Description('Get the best-selling products ranked by revenue or by total quantity sold.')]
class GetTopSellingProductsTool extends BaseAdminTool
{
    public function handle(Request $request, ProductReporting $productReporting): Response
    {
        if ($authError = $this->checkPermission('reporting.products')) {
            return $authError;
        }

        $limit = (int) ($request->get('limit', 10));
        $sortBy = $request->get('sort_by', 'revenue');

        $collection = $sortBy === 'quantity'
            ? $productReporting->getTopSellingProductsByQuantity($limit)
            : $productReporting->getTopSellingProductsByRevenue($limit);

        $products = $collection->map(fn ($p) => [
            'id' => $p->id,
            'sku' => $p->sku,
            'name' => $p->name,
            'price' => (float) $p->price,
            'total_revenue' => (float) ($p->revenue ?? 0),
            'total_quantity_sold' => (int) ($p->total_qty_ordered ?? 0),
        ]);

        return $this->successResponse('Top selling products retrieved successfully.', [
            'sort_by' => $sortBy,
            'limit' => $limit,
            'products' => $products,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'sort_by' => $schema->string()->description('Ranking metric: "revenue" (default) or "quantity".'),
            'limit' => $schema->integer()->description('Number of products to return (default 10, max 50).'),
        ];
    }
}
