<?php

namespace App\Mcp\Tools\Catalog;

use App\Mcp\Tools\BaseAdminTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Product\Repositories\ProductRepository;

#[Name('get_catalog_stats')]
#[Description('Get catalog health statistics: total products, products with and without images, in-stock and out-of-stock counts, and active status breakdown.')]
class GetCatalogStatsTool extends BaseAdminTool
{
    public function handle(
        Request $request,
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository
    ): Response {
        if ($authError = $this->checkPermission('catalog.products')) {
            return $authError;
        }

        $productModel = $productRepository->getModel();

        $totalProducts = $productModel->count();
        $productsWithImages = $productModel->whereHas('images')->count();
        $productsWithoutImages = $totalProducts - $productsWithImages;

        $activeProducts = $productModel->where('status', 1)->count();
        $inactiveProducts = $totalProducts - $activeProducts;

        $inStockCount = DB::table('product_inventories')
            ->select('product_id')
            ->groupBy('product_id')
            ->havingRaw('SUM(qty) > 0')
            ->get()
            ->count();

        $outOfStockCount = max(0, $totalProducts - $inStockCount);
        $totalCategories = $categoryRepository->count();

        $stats = [
            'total_products' => $totalProducts,
            'images_breakdown' => [
                'with_images' => $productsWithImages,
                'without_images' => $productsWithoutImages,
                'missing_images_percentage' => $totalProducts > 0 ? round(($productsWithoutImages / $totalProducts) * 100, 1) : 0,
            ],
            'status_breakdown' => [
                'active' => $activeProducts,
                'inactive' => $inactiveProducts,
            ],
            'inventory_breakdown' => [
                'in_stock' => $inStockCount,
                'out_of_stock' => $outOfStockCount,
            ],
            'total_categories' => $totalCategories,
        ];

        return $this->successResponse('Catalog health metrics retrieved successfully.', ['catalog_stats' => $stats]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
