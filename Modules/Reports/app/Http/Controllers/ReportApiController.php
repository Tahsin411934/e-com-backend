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
     * Map report category → [service instance, default method prefix].
     */
    protected array $dispatchMap;

    public function __construct(
        private CsvExporter $csv,
    ) {
        $this->dispatchMap = [
            'executive' => [app(ExecutiveReportService::class), ''],
            'sales' => [app(SalesReportService::class), ''],
            'performance' => [app(SalesPerformanceService::class), ''],
            'products' => [app(ProductReportService::class), ''],
            'product-perf' => [app(ProductPerformanceService::class), ''],
            'inventory' => [app(InventoryReportService::class), ''],
            'inventory-stock' => [app(InventoryStockReportService::class), ''],
            'orders' => [app(OrderFulfillmentReportService::class), ''],
            'order-timing' => [app(OrderFulfillmentTimingService::class), ''],
            'shipping' => [app(ShippingReportService::class), ''],
            'customers' => [app(CustomerReportService::class), ''],
            'campaigns' => [app(CampaignReportService::class), ''],
            'finance' => [app(FinanceReportService::class), ''],
            'refunds' => [app(RefundReportService::class), ''],
            'purchases' => [app(PurchaseSupplierReportService::class), ''],
            'staff' => [app(StaffReportService::class), ''],
        ];
    }

    /**
     * GET /api/v1/reports/{category}/{method}
     */
    public function report(string $category, string $method, Request $request): JsonResponse
    {
        $filters = ReportFilters::fromRequest($request);

        $map = $this->dispatchMap[$category] ?? null;
        if (! $map) {
            return response()->json(['error' => 'Unknown report category.', 'code' => 404], 404);
        }

        [$service, $prefix] = $map;
        $methodName = $prefix ? $prefix . ucfirst($method) : $method;

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
     */
    public function export(string $category, string $method, Request $request)
    {
        $filters = ReportFilters::fromRequest($request);

        $map = $this->dispatchMap[$category] ?? null;
        if (! $map) {
            abort(404, 'Unknown category.');
        }

        [$service, $prefix] = $map;
        $methodName = $prefix ? $prefix . ucfirst($method) : $method;

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