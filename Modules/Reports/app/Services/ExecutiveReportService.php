<?php

namespace Modules\Reports\Services;

use Modules\Inventory\Models\InventoryStock;
use Modules\Order\Models\Delivery;
use Modules\Order\Models\Order;
use Modules\Reports\Support\ReportFilters;

/**
 * Executive dashboard report: today/week/month sales, orders, net revenue,
 * gross profit + margin, AOV, new vs returning customers, pending order /
 * delivery alerts and low-stock alerts, plus the monthly sales trend.
 */
class ExecutiveReportService extends BaseReportService
{
    public function dashboard(ReportFilters $filters): array
    {
        return [
            'kpis' => array_merge($this->kpis($filters), $this->alerts()),
            'series' => $this->salesTrend($filters)['series'],
            'filters' => $filters->toArray(),
        ];
    }

    public function kpis(ReportFilters $filters): array
    {
        $orders = $this->orderQuery($filters, true);
        $revenue = (float) (clone $orders)->sum('grand_total');
        $count = (clone $orders)->count();
        $profit = $this->grossProfit($filters);

        $prev = $this->previousFilters($filters);
        $prevRevenue = $filters->previousTo !== null ? (float) $this->orderQuery($prev, true)->sum('grand_total') : null;
        $prevCount = $filters->previousTo !== null ? (float) $this->orderQuery($prev, true)->count() : null;
        $prevProfit = $filters->previousTo !== null ? $this->grossProfit($prev) : null;

        $aov = $count > 0 ? $revenue / $count : 0.0;
        $prevAov = $prevCount ? $prevRevenue / $prevCount : null;
        $margin = $revenue > 0 ? ($profit / $revenue) * 100 : 0.0;

        $customers = $this->newReturningCustomers($filters);
        $prevCustomers = $prev->previousTo !== null ? $this->newReturningCustomers($prev) : null;

        return [
            $this->kpi('revenue', 'Net Revenue', $revenue, $prevRevenue),
            $this->kpi('orders', 'Orders', $count, $prevCount),
            $this->kpi('gross_profit', 'Gross Profit', $profit, $prevProfit),
            $this->kpi('aov', 'Avg Order Value', $aov, $prevAov),
            $this->kpi('margin', 'Profit Margin (%)', $margin, null),
            $this->kpi('new_customers', 'New Customers', $customers['new'], $prevCustomers['new'] ?? null),
            $this->kpi('returning_customers', 'Returning Customers', $customers['returning'], $prevCustomers['returning'] ?? null),
        ];
    }

    public function alerts(): array
    {
        return [
            ['key' => 'pending_orders', 'label' => 'Pending Orders', 'value' => Order::whereIn('status', ['pending', 'confirmed', 'processing'])->count(), 'icon' => 'clock'],
            ['key' => 'pending_deliveries', 'label' => 'Pending Deliveries', 'value' => Delivery::whereNotIn('status', ['delivered', 'cancelled', 'failed'])->count(), 'icon' => 'truck'],
            ['key' => 'low_stock', 'label' => 'Low Stock Items', 'value' => InventoryStock::whereRaw('quantity_on_hand - quantity_reserved <= reorder_point')->count(), 'icon' => 'exclamation'],
        ];
    }

    public function salesTrend(ReportFilters $filters, string $granularity = 'month'): array
    {
        $expr = $granularity === 'day' ? 'DATE(placed_at)' : "DATE_FORMAT(placed_at, '%Y-%m')";

        $rows = $this->orderQuery($filters, true)
            ->selectRaw("$expr as label")
            ->selectRaw('COUNT(*) as orders, SUM(grand_total) as revenue')
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        return [
            'series' => $rows->map(fn ($r) => [
                'label' => $r->label,
                'revenue' => round((float) $r->revenue, 2),
                'orders' => (int) $r->orders,
            ])->values()->all(),
        ];
    }

    protected function previousFilters(ReportFilters $filters): ReportFilters
    {
        $clone = clone $filters;
        $clone->from = $filters->previousFrom;
        $clone->to = $filters->previousTo;

        return $clone;
    }

    protected function grossProfit(ReportFilters $filters): float
    {
        return (float) $this->orderItemQuery($filters)->sum('order_items.gross_profit');
    }

    protected function newReturningCustomers(ReportFilters $filters): array
    {
        $unique = $this->orderQuery($filters, true)->whereNotNull('user_id')->distinct()->count('user_id');
        $returning = $this->orderQuery($filters, true)->whereNotNull('user_id')->select('user_id')->groupBy('user_id')->havingRaw('COUNT(*) > 1')->get()->count();

        return [
            'new' => max(0, $unique - $returning),
            'returning' => $returning,
        ];
    }
}