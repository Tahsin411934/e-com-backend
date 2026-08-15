<?php

namespace Modules\Reports\Services;

use Modules\Reports\Support\ReportFilters;

/**
 * Sales reports: date / store / source (online vs POS) / payment-method /
 * order-status segmented sales and category/brand/product/variant breakout.
 */
class SalesReportService extends BaseReportService
{
    public function salesByDate(ReportFilters $filters): array
    {
        $rows = $this->orderQuery($filters, true)
            ->selectRaw('DATE(placed_at) as date')
            ->selectRaw('COUNT(*) as orders, SUM(grand_total) as revenue, SUM(discount_total) as discount')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'columns' => ['date' => 'Date', 'orders' => 'Orders', 'revenue' => 'Revenue', 'discount' => 'Discount'],
            'rows' => $rows->map(fn ($r) => [
                'date' => $r->date,
                'orders' => (int) $r->orders,
                'revenue' => round((float) $r->revenue, 2),
                'discount' => round((float) $r->discount, 2),
            ])->all(),
        ];
    }

    public function salesByStore(ReportFilters $filters): array
    {
        $rows = $this->orderQuery($filters, true)
            ->join('stores', 'stores.id', '=', 'orders.store_id')
            ->selectRaw('orders.store_id, stores.name as store_name')
            ->selectRaw('COUNT(*) as orders, SUM(orders.grand_total) as revenue')
            ->groupBy('orders.store_id', 'stores.name')
            ->orderByDesc('revenue')
            ->get();

        return [
            'columns' => ['store_name' => 'Store', 'orders' => 'Orders', 'revenue' => 'Revenue'],
            'rows' => $rows->map(fn ($r) => [
                'store_name' => $r->store_name,
                'orders' => (int) $r->orders,
                'revenue' => round((float) $r->revenue, 2),
            ])->all(),
        ];
    }

    public function salesBySource(ReportFilters $filters): array
    {
        $series = ['Online (Web/Mobile)' => 0, 'POS' => 0, 'Admin' => 0];

        $this->orderQuery($filters, true)
            ->select('source')
            ->selectRaw('SUM(grand_total) as revenue')
            ->groupBy('source')
            ->get()
            ->each(function ($row) use (&$series) {
                $key = $row->source === 'pos' ? 'POS' : ($row->source === 'admin' ? 'Admin' : 'Online (Web/Mobile)');
                $series[$key] += (float) $row->revenue;
            });

        return collect($series)->map(fn ($v, $k) => ['label' => $k, 'value' => round($v, 2)])->values()->all();
    }

    public function salesByPaymentMethod(ReportFilters $filters): array
    {
        $rows = $this->orderQuery($filters, true)
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->selectRaw("COALESCE(payments.method, 'unknown') as method")
            ->selectRaw('SUM(payments.amount) as amount')
            ->groupBy('payments.method')
            ->orderByDesc('amount')
            ->get();

        return [
            'columns' => ['method' => 'Payment Method', 'amount' => 'Amount'],
            'rows' => $rows->map(fn ($r) => [
                'method' => $r->method,
                'amount' => round((float) $r->amount, 2),
            ])->all(),
        ];
    }

    public function salesByOrderStatus(ReportFilters $filters): array
    {
        $rows = $this->orderQuery($filters, false)
            ->select('status')
            ->selectRaw('COUNT(*) as orders, SUM(grand_total) as revenue')
            ->groupBy('status')
            ->get();

        return [
            'columns' => ['status' => 'Status', 'orders' => 'Orders', 'revenue' => 'Revenue'],
            'rows' => $rows->map(fn ($r) => [
                'status' => $r->status,
                'orders' => (int) $r->orders,
                'revenue' => round((float) $r->revenue, 2),
            ])->all(),
        ];
    }

    /**
     * Segment sales by one of category|brand|product|variant.
     */
    public function segmentedSales(ReportFilters $filters, string $group): array
    {
        $map = [
            'category' => ['categories.name', 'Category'],
            'brand' => ['brands.name', 'Brand'],
            'product' => ['order_items.product_name', 'Product'],
            'variant' => ['order_items.variant_name', 'Variant'],
        ];
        [$select, $label] = $map[$group] ?? $map['category'];

        $q = $this->orderItemQuery($filters)
            ->selectRaw("$select as segment")
            ->selectRaw('SUM(order_items.quantity) as qty, SUM(order_items.line_total) as revenue');

        if ($group === 'category') {
            $q->join('products', 'products.id', '=', 'order_items.product_id')->join('categories', 'categories.id', '=', 'products.category_id');
        } elseif ($group === 'brand') {
            $q->join('products', 'products.id', '=', 'order_items.product_id')->join('brands', 'brands.id', '=', 'products.brand_id');
        }

        $rows = $q->groupBy($select)->orderByDesc('revenue')->get();

        return [
            'columns' => ['segment' => $label, 'qty' => 'Qty Sold', 'revenue' => 'Revenue'],
            'rows' => $rows->map(fn ($r) => [
                'segment' => $r->segment ?: '-',
                'qty' => (int) $r->qty,
                'revenue' => round((float) $r->revenue, 2),
            ])->all(),
        ];
    }
}