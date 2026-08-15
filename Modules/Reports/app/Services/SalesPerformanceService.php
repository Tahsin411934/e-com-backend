<?php

namespace Modules\Reports\Services;

use Modules\Pos\Models\PosSale;
use Modules\Reports\Support\ReportFilters;

/**
 * Sales performance reports: hour/day/month sales trend, top selling products,
 * top categories and salesperson / cashier-wise POS sales.
 */
class SalesPerformanceService extends BaseReportService
{
    public function salesTrendBy(ReportFilters $filters, string $granularity): array
    {
        $expr = match ($granularity) {
            'hour' => "DATE_FORMAT(placed_at, '%Y-%m-%d %H:00')",
            'day' => "DATE_FORMAT(placed_at, '%Y-%m-%d')",
            default => "DATE_FORMAT(placed_at, '%Y-%m')",
        };

        $rows = $this->orderQuery($filters, true)
            ->selectRaw("$expr as label")
            ->selectRaw('COUNT(*) as orders, SUM(grand_total) as revenue')
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        return [
            'columns' => ['label' => 'Period', 'orders' => 'Orders', 'revenue' => 'Revenue'],
            'rows' => $rows->map(fn ($r) => [
                'label' => $r->label,
                'orders' => (int) $r->orders,
                'revenue' => round((float) $r->revenue, 2),
            ])->all(),
            'series' => $rows->map(fn ($r) => ['label' => $r->label, 'value' => round((float) $r->revenue, 2)])->values()->all(),
        ];
    }

    public function topProducts(ReportFilters $filters, int $limit = 10): array
    {
        return $this->orderItemQuery($filters)
            ->selectRaw('order_items.product_id')
            ->selectRaw('MAX(order_items.product_name) as product_name')
            ->selectRaw('SUM(order_items.quantity) as qty, SUM(order_items.line_total) as revenue')
            ->groupBy('order_items.product_id')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'product_id' => $r->product_id,
                'product_name' => $r->product_name,
                'qty' => (int) $r->qty,
                'revenue' => round((float) $r->revenue, 2),
            ])->all();
    }

    public function topCategories(ReportFilters $filters, int $limit = 10): array
    {
        return $this->orderItemQuery($filters)
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->selectRaw('categories.name as category')
            ->selectRaw('SUM(order_items.quantity) as qty, SUM(order_items.line_total) as revenue')
            ->groupBy('categories.name')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'category' => $r->category,
                'qty' => (int) $r->qty,
                'revenue' => round((float) $r->revenue, 2),
            ])->all();
    }

    public function posSalesByCashier(ReportFilters $filters): array
    {
        $q = PosSale::query()
            ->whereNull('deleted_at')
            ->when($filters->from, fn ($q) => $q->whereDate('created_at', '>=', $filters->from))
            ->when($filters->to, fn ($q) => $q->whereDate('created_at', '<=', $filters->to));

        if ($filters->storeId !== null) {
            $q->whereHas('register', fn ($r) => $r->where('store_id', $filters->storeId));
        }

        $rows = (clone $q)->select('user_id')
            ->selectRaw('COUNT(*) as sales, SUM(total) as total')
            ->groupBy('user_id')
            ->with('user')
            ->orderByDesc('total')
            ->get();

        return [
            'columns' => ['cashier' => 'Cashier', 'sales' => 'Sales', 'total' => 'Total'],
            'rows' => $rows->map(fn ($r) => [
                'cashier' => $r->user?->name ?: 'Unknown',
                'sales' => (int) $r->sales,
                'total' => round((float) $r->total, 2),
            ])->all(),
        ];
    }
}