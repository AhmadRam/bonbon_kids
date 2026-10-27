<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\Analytics\GetSalesAnalyticsTool;
use App\Mcp\Tools\Analytics\GetTopSellingProductsTool;
use App\Mcp\Tools\Catalog\CreateCategoryTool;
use App\Mcp\Tools\Catalog\CreateProductTool;
use App\Mcp\Tools\Catalog\DeleteProductTool;
use App\Mcp\Tools\Catalog\GetProductDetailsTool;
use App\Mcp\Tools\Catalog\ListCategoriesTool;
use App\Mcp\Tools\Catalog\ListProductsTool;
use App\Mcp\Tools\Catalog\UpdateProductTool;
use App\Mcp\Tools\Customers\GetCustomerDetailsTool;
use App\Mcp\Tools\Customers\ListCustomersTool;
use App\Mcp\Tools\Customers\ListReviewsTool;
use App\Mcp\Tools\Customers\UpdateCustomerStatusTool;
use App\Mcp\Tools\Inventory\CheckStockTool;
use App\Mcp\Tools\Inventory\GetLowStockAlertsTool;
use App\Mcp\Tools\Inventory\UpdateStockTool;
use App\Mcp\Tools\Marketing\CreateCouponTool;
use App\Mcp\Tools\Marketing\ListCartRulesTool;
use App\Mcp\Tools\Sales\CancelOrderTool;
use App\Mcp\Tools\Sales\CreateInvoiceTool;
use App\Mcp\Tools\Sales\CreateShipmentTool;
use App\Mcp\Tools\Sales\GetOrderDetailsTool;
use App\Mcp\Tools\Sales\ListOrdersTool;
use App\Mcp\Tools\Sales\UpdateOrderStatusTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Bagisto Store Admin MCP Server')]
#[Version('1.0.0')]
#[Instructions('This MCP server provides full administrative capabilities for Bagisto e-commerce platform. AI agents can manage catalog, products, orders, invoices, shipments, stock, customers, promotions, and sales analytics.')]
class BagistoAdminServer extends Server
{
    /**
     * The tools registered with this MCP server.
     *
     * @var array<int, class-string>
     */
    protected array $tools = [
        // Catalog & Products
        ListProductsTool::class,
        GetProductDetailsTool::class,
        CreateProductTool::class,
        UpdateProductTool::class,
        DeleteProductTool::class,
        ListCategoriesTool::class,
        CreateCategoryTool::class,

        // Sales & Orders
        ListOrdersTool::class,
        GetOrderDetailsTool::class,
        UpdateOrderStatusTool::class,
        CreateInvoiceTool::class,
        CreateShipmentTool::class,
        CancelOrderTool::class,

        // Inventory
        CheckStockTool::class,
        UpdateStockTool::class,
        GetLowStockAlertsTool::class,

        // Customers & Reviews
        ListCustomersTool::class,
        GetCustomerDetailsTool::class,
        UpdateCustomerStatusTool::class,
        ListReviewsTool::class,

        // Marketing & Coupons
        CreateCouponTool::class,
        ListCartRulesTool::class,

        // Analytics & Reports
        GetSalesAnalyticsTool::class,
        GetTopSellingProductsTool::class,
    ];

    /**
     * The resources registered with this server.
     *
     * @var array<int, class-string>
     */
    protected array $resources = [];

    /**
     * The prompts registered with this server.
     *
     * @var array<int, class-string>
     */
    protected array $prompts = [];
}
