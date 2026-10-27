<?php

namespace App\Mcp\Tools\Analytics;

use App\Mcp\Tools\BaseAdminTool;
use Carbon\Carbon;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Webkul\Admin\Helpers\Reporting\Sale as SaleReporting;

#[Name('get_sales_analytics')]
#[Description('Retrieve sales metrics and analytics including total revenue, order count, and average order value for today, this week, or a custom date range.')]
class GetSalesAnalyticsTool extends BaseAdminTool
{
    public function handle(Request $request, SaleReporting $saleReporting): Response
    {
        if ($authError = $this->checkPermission('reporting.sales')) {
            return $authError;
        }

        $period = $request->get('period', 'today');
        $customStart = $request->get('start_date');
        $customEnd = $request->get('end_date');

        if ($customStart && $customEnd) {
            $startDate = Carbon::parse($customStart)->startOfDay();
            $endDate = Carbon::parse($customEnd)->endOfDay();
        } else {
            [$startDate, $endDate] = match ($period) {
                'yesterday' => [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()],
                'this_week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
                'last_week' => [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()],
                'this_month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
                'last_month' => [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()],
                'this_year' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
                default => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
            };
        }

        $totalSales = $saleReporting->getTotalSales($startDate, $endDate);
        $totalOrders = $saleReporting->getTotalOrders($startDate, $endDate);
        $avgSales = $saleReporting->getAverageSales($startDate, $endDate);
        $refunds = $saleReporting->getRefunds($startDate, $endDate);
        $tax = $saleReporting->getTaxCollected($startDate, $endDate);
        $shipping = $saleReporting->getShippingCollected($startDate, $endDate);

        $analytics = [
            'period' => $period,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'total_revenue' => (float) $totalSales,
            'total_orders' => (int) $totalOrders,
            'average_order_value' => (float) ($avgSales ?? 0),
            'total_refunds' => (float) $refunds,
            'tax_collected' => (float) $tax,
            'shipping_collected' => (float) $shipping,
        ];

        return $this->successResponse('Sales analytics calculated successfully.', ['analytics' => $analytics]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'period' => $schema->string()->description('Predefined period: today, yesterday, this_week, last_week, this_month, last_month, this_year. Defaults to today.'),
            'start_date' => $schema->string()->description('Optional custom start date (YYYY-MM-DD). Overrides period.'),
            'end_date' => $schema->string()->description('Optional custom end date (YYYY-MM-DD). Overrides period.'),
        ];
    }
}
