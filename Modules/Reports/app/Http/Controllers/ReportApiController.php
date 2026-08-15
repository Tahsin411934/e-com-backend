<?php

namespace Modules\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Reports\Support\CsvExporter;
use Modules\Reports\Support\ReportFilters;
use Modules\Reports\Services\ExecutiveReportService;
use Modules\Reports\Services\SalesReportService;
use Modules\Reports\Services\SalesPerformanceService;
use Modules\Reports\Services\ProductReportService;
use Modules\Reports\Services\OrderFulfillmentReportService;
use Modules\Reports\Services\CustomerReportService;
use Modules\Reports\Services\CampaignReportService;
use Modules\Reports\Services\FinanceReportService;
use Modules\Reports\Services\RefundReportService;
use Modules\Reports\Services\PurchaseSupplierReportService;
use Modules\Reports\Services\StaffReportService;
use Modules\Reports\Services\ShippingReportService;
use Modules\Reports\Services\ProductPerformanceService;
use Modules\Reports\Services\InventoryReportService;
use Modules\Reports\Services\InventoryStockReportService;
use Modules\Reports\Services\OrderFulfillmentTimingService;

class ReportApiController extends Controller
{
    /**
     * Map report category → [service instance, default method prefix, whitelisted methods].
     * SECURITY: Methods must be explicitly whitelisted to prevent unauthorized method invocation.
     */
    protected array $dispatchMap;

    public function __construct(
        private CsvExporter $csv,
    ) {
        $this->dispatchMap = [
            'executive' => [app(ExecutiveReportService::class), '', ['overview', 'topMetrics', 'alerts']],
            'sales' => [app(SalesReportService::class), '', ['totalSales', 'byProduct', 'byCategory', 'byDay', 'bySalesChannel']],
            'performance' => [app(SalesPerformanceService::class), '', ['performance', 'trend']],
            'products' => [app(ProductReportService::class), '', ['topProducts', 'lowStock', 'sales', 'ratings']],
            'product-perf' => [app(ProductPerformanceService::class), '', ['performance', 'byCategory', 'byBrand']],
            'inventory' => [app(InventoryReportService::class), '', ['movement', 'valuation', 'adjustments']],
            'inventory-stock' => [app(InventoryStockReportService::class), '', ['summary', 'byLocation', 'lowStock', 'stockAging']],
            'orders' => [app(OrderFulfillmentReportService::class), '', ['fulfillmentStatus', 'fulfillmentTime', 'returnRate']],
            'order-timing' => [app(OrderFulfillmentTimingService::class), '', ['avgFulfillmentTime', 'fulfillmentByDay', 'delays']],
            'shipping' => [app(ShippingReportService::class), '', ['costs', 'methods', 'carriers', 'timing']],
            'customers' => [app(CustomerReportService::class), '', ['kpis', 'topCustomers', 'citySales', 'orderFrequency']],
            'campaigns' => [app(CampaignReportService::class), '', ['overview', 'byChannel', 'roi', 'engagement']],
            'finance' => [app(FinanceReportService::class), '', ['revenue', 'expenses', 'profitMargin', 'cashFlow']],
            'refunds' => [app(RefundReportService::class), '', ['summary', 'byReason', 'byCategory', 'trends']],
            'purchases' => [app(PurchaseSupplierReportService::class), '', ['bySupplier', 'byProduct', 'costs', 'leadTime']],
            'staff' => [app(StaffReportService::class), '', ['sales', 'performance', 'targets']],
        ];
    }

    /**
     * GET /api/v1/reports/{category}/{method}
     * SECURITY: Validates that the requested method is in the whitelist before invocation.
     */
    public function report(string $category, string $method, Request $request): JsonResponse
    {
        $filters = ReportFilters::fromRequest($request);

        $map = $this->dispatchMap[$category] ?? null;
        if (! $map) {
            return response()->json(['error' => 'Unknown report category.', 'code' => 404], 404);
        }

        [$service, $prefix, $whitelist] = $map;
        $methodName = $prefix ? $prefix . ucfirst($method) : $method;

        // Security check: validate method is in whitelist
        if (! in_array($methodName, $whitelist, true)) {
            return response()->json(['error' => "Report method '{$category}.{$methodName}' is not allowed.", 'code' => 403], 403);
        }

        if (! method_exists($service, $methodName)) {
            return response()->json(['error' => "Report method '{$category}.{$methodName}' not found.", 'code' => 404], 404);
        }

        try {
            $result = $service->{$methodName}($filters);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage(), 'code' => 500], 500);
        }

        return response()->json($result);
    }

    /**
     * GET /api/v1/reports/{category}/{method}/export
     * SECURITY: Validates that the requested method is in the whitelist before invocation.
     */
    public function export(string $category, string $method, Request $request)
    {
        $filters = ReportFilters::fromRequest($request);

        $map = $this->dispatchMap[$category] ?? null;
        if (! $map) {
            abort(404, 'Unknown category.');
        }

        [$service, $prefix, $whitelist] = $map;
        $methodName = $prefix ? $prefix . ucfirst($method) : $method;

        // Security check: validate method is in whitelist
        if (! in_array($methodName, $whitelist, true)) {
            abort(403, "Report method '{$category}.{$methodName}' is not allowed.");
        }

        if (! method_exists($service, $methodName)) {
            abort(404, 'Method not found.');
        }

        $data = $service->{$methodName}($filters);

        $filename = "{$category}_{$method}_" . now()->format('Y-m-d') . '.csv';
        $columns = $data['columns'] ?? [];
        $rows = $data['rows'] ?? [];

        return $this->csv->stream($this->csv->fromRows($filename, $columns, $rows));
    }
}