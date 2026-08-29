<?php

namespace Modules\Reports\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Reports\Services\CustomerReportService;
use Modules\Reports\Services\ExecutiveReportService;
use Modules\Reports\Services\InventoryReportService;
use Modules\Reports\Services\InventoryStockReportService;
use Modules\Reports\Services\OrderFulfillmentTimingService;
use Modules\Reports\Services\ProductPerformanceService;
use Modules\Reports\Services\SalesPerformanceService;
use Modules\Reports\Services\SalesReportService;
use Modules\Reports\Services\ShippingReportService;
use Modules\Reports\Support\CsvExporter;
use Modules\Reports\Support\ReportFilters;

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
            // NOTE: only categories backed by an existing service class are
            // registered here — the whitelist must match real public methods
            // (ReportFilters is the only argument injected by report()).
            'executive' => [app(ExecutiveReportService::class), '', ['dashboard', 'kpis', 'alerts', 'salesTrend']],
            'sales' => [app(SalesReportService::class), '', ['salesByDate', 'salesByStore', 'salesBySource', 'salesByPaymentMethod', 'salesByOrderStatus']],
            'performance' => [app(SalesPerformanceService::class), '', ['topProducts', 'topCategories', 'posSalesByCashier']],
            'product-perf' => [app(ProductPerformanceService::class), '', ['variantWise', 'returnRate', 'conversion', 'stockTurnover']],
            'inventory' => [app(InventoryReportService::class), '', ['currentStock', 'lowOutOfStock', 'variantLevelStock', 'valuation']],
            'inventory-stock' => [app(InventoryStockReportService::class), '', ['movements', 'damagedExpiredLost', 'deadStock', 'reorder', 'transfers']],
            'order-timing' => [app(OrderFulfillmentTimingService::class), '', ['fulfillmentTimes', 'failedDeliveries', 'codVsPrepaid']],
            'shipping' => [app(ShippingReportService::class), '', ['overview', 'driverPerformance', 'zoneCost', 'failedReasons']],
            'customers' => [app(CustomerReportService::class), '', ['kpis', 'topCustomers', 'citySales', 'orderFrequency']],
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
            return ApiResponse::error('Unknown report category.', 404);
        }

        [$service, $prefix, $whitelist] = $map;
        $methodName = $prefix ? $prefix.ucfirst($method) : $method;

        // Security check: validate method is in whitelist
        if (! in_array($methodName, $whitelist, true)) {
            return ApiResponse::error("Report method '{$category}.{$methodName}' is not allowed.", 403);
        }

        if (! method_exists($service, $methodName)) {
            return ApiResponse::error("Report method '{$category}.{$methodName}' not found.", 404);
        }

        try {
            $result = $service->{$methodName}($filters);
        } catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }

        return ApiResponse::success($result, 'Report generated successfully.');
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
        $methodName = $prefix ? $prefix.ucfirst($method) : $method;

        // Security check: validate method is in whitelist
        if (! in_array($methodName, $whitelist, true)) {
            abort(403, "Report method '{$category}.{$methodName}' is not allowed.");
        }

        if (! method_exists($service, $methodName)) {
            abort(404, 'Method not found.');
        }

        $data = $service->{$methodName}($filters);

        $filename = "{$category}_{$method}_".now()->format('Y-m-d').'.csv';
        $columns = $data['columns'] ?? [];
        $rows = $data['rows'] ?? [];

        return $this->csv->stream($this->csv->fromRows($filename, $columns, $rows));
    }
}
