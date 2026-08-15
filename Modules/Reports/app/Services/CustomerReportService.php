<?php

namespace Modules\Reports\Services;

use Illuminate\Support\Facades\DB;
use Modules\Identity\Models\User;
use Modules\Order\Models\Order;
use Modules\Reports\Support\ReportFilters;
use Modules\Store\Models\Address;

/**
 * Customer reports: KPIs (new vs returning, lifetime value), top customers
 * by spending/order count, location/city-wise sales, order frequency.
 */
class CustomerReportService extends BaseReportService
{
    public function kpis(ReportFilters $filters): array
    {
        $total = User::count();
        $new = User::when($filters->from, fn ($q) => $q->whereDate('created_at', '>=', $filters->from))
            ->when($filters->to, fn ($q) => $q->whereDate('created_at', '<=', $filters->to))->count();

        $uniqueCustomers = $this->orderQuery($filters, true)->whereNotNull('user_id')->distinct()->count('user_id');
        $returning = $this->orderQuery($filters, true)->whereNotNull('user_id')->select('user_id')->groupBy('user_id')->havingRaw('COUNT(*) > 1')->get()->count();
        $revenue = (float) $this->orderQuery($filters, true)->sum('grand_total');
        $ltv = $uniqueCustomers > 0 ? $revenue / $uniqueCustomers : 0;

        return ['kpis' => [
            ['key' => 'total_customers', 'label' => 'Total Customers', 'value' => $total],
            ['key' => 'new_customers', 'label' => 'New (filtered)', 'value' => $new],
            ['key' => 'repeat_rate', 'label' => 'Repeat Customer Rate (%)', 'value' => $uniqueCustomers > 0 ? round(($returning / $uniqueCustomers) * 100, 1) : 0],
            ['key' => 'customer_ltv', 'label' => 'Avg Customer LTV', 'value' => round($ltv, 2)],
        ]];
    }

    public function topCustomers(ReportFilters $filters, int $limit = 20): array
    {
        $rows = $this->orderQuery($filters, true)
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->selectRaw('users.id, CONCAT(users.first_name, " ", users.last_name) as customer_name, users.email')
            ->selectRaw('COUNT(*) as orders, SUM(orders.grand_total) as spent')
            ->groupBy('users.id')->orderByDesc('spent')->limit($limit)->get()
            ->map(fn ($r) => ['customer' => trim($r->customer_name) ?: $r->email, 'orders' => (int) $r->orders, 'spent' => round((float) $r->spent, 2)]);
        return ['columns' => ['customer' => 'Customer', 'orders' => 'Orders', 'spent' => 'Total Spent'], 'rows' => $rows->all()];
    }

    public function citySales(ReportFilters $filters): array
    {
        $rows = $this->orderQuery($filters, true)
            ->join('addresses', 'addresses.id', '=', 'orders.shipping_address_id')
            ->selectRaw('COALESCE(addresses.city, "Unknown") as city')
            ->selectRaw('COUNT(*) as orders, SUM(orders.grand_total) as revenue')
            ->groupBy('addresses.city')->orderByDesc('revenue')->get()
            ->map(fn ($r) => ['city' => $r->city, 'orders' => (int) $r->orders, 'revenue' => round((float) $r->revenue, 2)]);
        return ['columns' => ['city' => 'City', 'orders' => 'Orders', 'revenue' => 'Revenue'], 'rows' => $rows->all()];
    }

    public function orderFrequency(ReportFilters $filters): array
    {
        $rows = $this->orderQuery($filters, true)->whereNotNull('user_id')
            ->select('user_id')->selectRaw('COUNT(*) as freq')
            ->groupBy('user_id')->get()
            ->groupBy(fn ($r) => $r->freq >= 10 ? '10+' : (string) $r->freq)
            ->map(fn ($g, $key) => ['orders' => $key, 'customers' => $g->count()])->values();

        return ['columns' => ['orders' => 'Orders Placed', 'customers' => 'Customers'], 'rows' => $rows->all()];
    }
}