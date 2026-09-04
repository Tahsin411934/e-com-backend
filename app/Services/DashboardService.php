<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Catalog\Models\Product;
use Modules\Identity\Models\User;
use Modules\Inventory\Models\InventoryStock;
use Modules\Order\Models\Delivery;
use Modules\Order\Models\Order;
use Modules\Order\Models\OrderItem;
use Modules\Store\Support\CurrentStore;

class DashboardService
{
    private const REVENUE_EXCLUDED_STATUSES = ['cancelled', 'refunded'];

    /**
     * Get all KPI data for the dashboard.
     */
    public function getKpiData(): array
    {
        $totalRevenue = $this->scopeOrders(
            Order::whereNotIn('status', self::REVENUE_EXCLUDED_STATUSES)
        )->sum('grand_total');

        $totalOrders = $this->scopeOrders(Order::query())->count();

        $totalCustomers = $this->scopedCustomerCount();

        // Admin panel product totals use explicit store scoping.
        $totalProducts = Product::forCurrentStore()->count();

        $pendingOrders = $this->scopeOrders(Order::where('status', 'pending'))->count();

        $lowStockItems = $this->activeStockQuery()
            ->where(DB::raw('quantity_on_hand - quantity_reserved'), '<=', DB::raw('reorder_point'))
            ->count();

        // Previous period calculations for comparison
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();
        $thisMonthStart = Carbon::now()->startOfMonth();

        $lastMonthRevenue = $this->scopeOrders(
            Order::whereNotIn('status', self::REVENUE_EXCLUDED_STATUSES)
        )->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->sum('grand_total');

        $thisMonthRevenue = $this->scopeOrders(
            Order::whereNotIn('status', self::REVENUE_EXCLUDED_STATUSES)
        )->where('created_at', '>=', $thisMonthStart)->sum('grand_total');

        $lastMonthOrders = $this->scopeOrders(Order::query())
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $thisMonthOrders = $this->scopeOrders(Order::query())
            ->where('created_at', '>=', $thisMonthStart)->count();

        $lastMonthCustomers = $this->scopedCustomerCountBetween($lastMonthStart, $lastMonthEnd);
        $thisMonthCustomers = $this->scopedCustomerCountBetween($thisMonthStart);

        // Growth percentages
        $revenueGrowth = $lastMonthRevenue > 0
            ? round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : 0;

        $orderGrowth = $lastMonthOrders > 0
            ? round((($thisMonthOrders - $lastMonthOrders) / $lastMonthOrders) * 100, 1)
            : 0;

        $customerGrowth = $lastMonthCustomers > 0
            ? round((($thisMonthCustomers - $lastMonthCustomers) / $lastMonthCustomers) * 100, 1)
            : 0;

        return [
            'totalRevenue' => $totalRevenue,
            'totalOrders' => $totalOrders,
            'totalCustomers' => $totalCustomers,
            'totalProducts' => $totalProducts,
            'pendingOrders' => $pendingOrders,
            'lowStockItems' => $lowStockItems,
            'revenueGrowth' => $revenueGrowth,
            'orderGrowth' => $orderGrowth,
            'customerGrowth' => $customerGrowth,
            'lastMonthRevenue' => $lastMonthRevenue,
            'thisMonthRevenue' => $thisMonthRevenue,
            'lastMonthOrders' => $lastMonthOrders,
            'thisMonthOrders' => $thisMonthOrders,
            'lastMonthCustomers' => $lastMonthCustomers,
            'thisMonthCustomers' => $thisMonthCustomers,
        ];
    }

    /**
     * Get monthly revenue data for the chart (last 12 months).
     */
    public function getMonthlyRevenue(): array
    {
        $months = [];
        $revenueCurrentYear = [];
        $revenueLastYear = [];
        $currentYear = Carbon::now()->year;
        $lastYear = $currentYear - 1;

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $year = $date->year;
            $month = $date->month;
            $months[] = $date->format('M');

            $revenue = $this->scopeOrders(
                Order::whereNotIn('status', self::REVENUE_EXCLUDED_STATUSES)
            )->whereYear('created_at', $year)->whereMonth('created_at', $month)->sum('grand_total');

            $revenueCurrentYear[] = (float) $revenue;

            $lastYearRevenue = $this->scopeOrders(
                Order::whereNotIn('status', self::REVENUE_EXCLUDED_STATUSES)
            )->whereYear('created_at', $lastYear)->whereMonth('created_at', $month)->sum('grand_total');
            $revenueLastYear[] = (float) $lastYearRevenue;
        }

        return [
            'labels' => $months,
            'currentYear' => $revenueCurrentYear,
            'lastYear' => $revenueLastYear,
            'currentYearLabel' => 'Revenue '.$currentYear,
            'lastYearLabel' => 'Revenue '.$lastYear,
        ];
    }

    /**
     * Get revenue by source/channel.
     */
    public function getRevenueBySource(): array
    {
        $rows = $this->scopeOrders(Order::query())
            ->select('source', DB::raw('SUM(grand_total) as total_revenue'))
            ->whereNotIn('status', self::REVENUE_EXCLUDED_STATUSES)
            ->groupBy('source')
            ->orderByDesc('total_revenue')
            ->get();

        $total = (float) $rows->sum('total_revenue');
        $colors = ['#4F46E5', '#10B981', '#7C3AED', '#F59E0B', '#EC4899', '#06B6D4'];

        return [
            'labels' => $rows->pluck('source')->map(fn (?string $source) => $this->formatLabel($source ?? 'unknown'))->values()->all(),
            'data' => $rows->map(fn ($row) => $total > 0 ? round(((float) $row->total_revenue / $total) * 100, 1) : 0)->values()->all(),
            'amounts' => $rows->map(fn ($row) => [
                'source' => $row->source ?? 'unknown',
                'label' => $this->formatLabel($row->source ?? 'unknown'),
                'amount' => (float) $row->total_revenue,
            ])->values()->all(),
            'colors' => array_slice($colors, 0, max($rows->count(), 1)),
        ];
    }

    /**
     * Get delivery overview data.
     */
    public function getDeliveryOverview(): array
    {
        $statuses = ['pending', 'assigned', 'picked', 'delivered', 'cancelled'];
        $assigned = Delivery::where('status', 'assigned')->count();
        $pending = Delivery::where('status', 'pending')->count();
        $picked = Delivery::where('status', 'picked')->count();
        $delivered = Delivery::where('status', 'delivered')->count();
        $cancelled = Delivery::where('status', 'cancelled')->count();

        return [
            'labels' => array_map(fn (string $status) => $this->formatLabel($status), $statuses),
            'data' => [$pending, $assigned, $picked, $delivered, $cancelled],
            'colors' => ['#64748B', '#3B82F6', '#F59E0B', '#10B981', '#F43F5E'],
        ];
    }

    /**
     * Get recent orders (last N).
     */
    public function getRecentOrders(int $limit = 5): array
    {
        return $this->scopeOrders(Order::with(['user', 'items']))
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (Order $order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer' => $order->user?->name ?: 'Guest',
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'source' => $this->formatLabel($order->source),
                'items_count' => $order->items->count(),
                'grand_total' => (float) $order->grand_total,
                'created_at' => $order->created_at?->format('M j, Y g:i A'),
            ])
            ->all();
    }

    /**
     * Get top selling products.
     */
    public function getTopSellingProducts(int $limit = 5): array
    {
        $productIds = $this->scopedProductIds();

        if ($productIds !== null && $productIds === []) {
            return [];
        }

        return OrderItem::select(
            'product_id', 'product_name',
            DB::raw('SUM(quantity) as total_quantity'),
            DB::raw('SUM(line_total) as total_revenue')
        )
            ->whereHas('order', fn ($q) => $q->whereNotIn('status', ['cancelled', 'refunded']))
            ->when($productIds !== null, fn ($q) => $q->whereIn('product_id', $productIds))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get()
            ->map(fn ($item) => [
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'total_quantity' => (int) $item->total_quantity,
                'total_revenue' => (float) $item->total_revenue,
            ])
            ->all();
    }

    /**
     * Get low stock products.
     */
    public function getLowStockProducts(int $limit = 5): array
    {
        if (! Schema::hasTable('inventory_stock')) {
            return [];
        }

        return $this->activeStockQuery()
            ->with(['variant.product', 'variantOption'])
            ->where(DB::raw('quantity_on_hand - quantity_reserved'), '<=', DB::raw('reorder_point'))
            ->orderBy(DB::raw('quantity_on_hand - quantity_reserved'))
            ->limit($limit)
            ->get()
            ->map(function (InventoryStock $stock): array {
                $available = (int) $stock->quantity_on_hand - (int) $stock->quantity_reserved;

                return [
                    'id' => $stock->id,
                    'product_name' => $stock->variant?->product?->name ?? 'Unknown product',
                    'variant_name' => $stock->variant?->name ?? $stock->variant?->sku ?? 'Default',
                    'sku' => $stock->variant?->sku,
                    'quantity_on_hand' => (int) $stock->quantity_on_hand,
                    'quantity_reserved' => (int) $stock->quantity_reserved,
                    'available' => $available,
                    'reorder_point' => (int) $stock->reorder_point,
                ];
            })
            ->all();
    }

    /**
     * Query InventoryStock rows that belong to a live (non-soft-deleted)
     * variant/product. Deleting a product usually leaves its stock rows
     * behind, so we must exclude those to avoid inflated counts.
     *
     * Explicit INNER JOINs on product_variants + products guarantee that any
     * stock row whose product or variant is missing/soft-deleted is excluded
     * (a WHERE EXISTS can silently miss orphaned/hard-deleted references).
     */
    private function activeStockQuery(): Builder
    {
        $query = InventoryStock::query()
            ->select('inventory_stock.*')
            ->join('product_variants', 'inventory_stock.variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->whereNull('product_variants.deleted_at')
            ->whereNull('products.deleted_at')
            ->where(function ($q) {
                $q->whereNull('inventory_stock.variant_option_id')
                    ->orWhereHas('variantOption', fn ($o) => $o->whereNull('deleted_at'));
            });

        // SaaS: scope inventory KPIs to the current store when one is active.
        if (($storeId = CurrentStore::id()) !== null) {
            $query->where('products.store_id', $storeId);
        }

        return $query;
    }

    /**
     * Get recent activities.
     */
    public function getRecentActivities(int $limit = 10): array
    {
        $activities = [];
        $recentOrders = $this->scopeOrders(Order::with('user'))
            ->whereNotIn('status', self::REVENUE_EXCLUDED_STATUSES)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        foreach ($recentOrders as $order) {
            $activities[] = [
                'type' => 'order',
                'description' => 'Order #'.$order->order_number.' placed',
                'user' => $order->user?->name ?: 'Guest',
                'created_at' => $order->created_at?->diffForHumans(),
                'sort_at' => $order->created_at,
                'icon' => 'shopping-bag',
                'color' => 'blue',
            ];
        }

        $recentUsers = User::orderByDesc('created_at')->limit($limit)->get();
        foreach ($recentUsers as $user) {
            $activities[] = [
                'type' => 'user',
                'description' => 'User registered',
                'user' => $user->name ?: $user->email,
                'created_at' => $user->created_at?->diffForHumans(),
                'sort_at' => $user->created_at,
                'icon' => 'user-plus',
                'color' => 'purple',
            ];
        }

        return collect($activities)
            ->sortByDesc('sort_at')
            ->take($limit)
            ->map(function (array $activity): array {
                unset($activity['sort_at']);

                return $activity;
            })
            ->values()
            ->all();
    }

    /**
     * Get inventory summary data.
     */
    public function getInventorySummary(): array
    {
        if (! Schema::hasTable('inventory_stock')) {
            return ['inStock' => 0, 'lowStock' => 0, 'outOfStock' => 0, 'totalStockItems' => 0];
        }

        $available = DB::raw('quantity_on_hand - quantity_reserved');
        $base = $this->activeStockQuery();
        $total = (clone $base)->count();
        $inStock = (clone $base)->where($available, '>', DB::raw('reorder_point'))->count();
        $lowStock = (clone $base)->where($available, '<=', DB::raw('reorder_point'))
            ->where($available, '>', 0)
            ->count();
        $outOfStock = (clone $base)->where($available, '<=', 0)->count();

        return ['inStock' => $inStock, 'lowStock' => $lowStock, 'outOfStock' => $outOfStock, 'totalStockItems' => $total];
    }

    private function formatLabel(?string $value): string
    {
        return str($value ?? 'unknown')->replace(['_', '-'], ' ')->title()->toString();
    }

    /**
     * SaaS: product ids of the store the dashboard is scoped to
     * (null = no store context → platform-wide dashboard).
     */
    private function scopedProductIds(): ?array
    {
        $storeId = CurrentStore::id();

        if ($storeId === null) {
            return null;
        }

        return Product::where('store_id', $storeId)
            ->pluck('id')
            ->all();
    }

    /**
     * SaaS: restrict order queries to orders containing the current store's
     * products. Mixed-store orders are attributed in full to each involved
     * store until per-store order items are introduced.
     */
    private function scopeOrders($query)
    {
        $productIds = $this->scopedProductIds();

        if ($productIds === null) {
            return $query;
        }

        if ($productIds === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('items', fn ($q) => $q->whereIn('product_id', $productIds));
    }

    /**
     * SaaS: customer totals — all users platform-wide, or distinct
     * customers who ordered the current store's products.
     */
    private function scopedCustomerCount(): int
    {
        return $this->scopedCustomerCountBetween(Carbon::createFromTimestamp(0));
    }

    private function scopedCustomerCountBetween(Carbon $start, ?Carbon $end = null): int
    {
        $query = $this->scopedProductIds() === null
            ? User::query()
            : User::whereIn('id', $this->scopeOrders(Order::query())
                ->whereNotNull('user_id')
                ->select('user_id'));

        $query->where('created_at', '>=', $start);

        if ($end !== null) {
            $query->where('created_at', '<=', $end);
        }

        return (int) $query->count();
    }

    /**
     * Get all dashboard data in one call.
     */
    public function getDashboardData(): array
    {
        return [
            'kpi' => $this->getKpiData(),
            'monthlyRevenue' => $this->getMonthlyRevenue(),
            'revenueBySource' => $this->getRevenueBySource(),
            'deliveryOverview' => $this->getDeliveryOverview(),
            'recentOrders' => $this->getRecentOrders(),
            'topSellingProducts' => $this->getTopSellingProducts(),
            'lowStockProducts' => $this->getLowStockProducts(),
            'recentActivities' => $this->getRecentActivities(),
            'inventorySummary' => $this->getInventorySummary(),
        ];
    }
}
