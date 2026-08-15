<?php

namespace Modules\Reports\Services;

use Illuminate\Support\Facades\DB;
use Modules\Reports\Support\ReportFilters;
use Modules\Shipping\Models\DeliveryDriver;
use Modules\Shipping\Models\DeliveryZone;
use Modules\Shipping\Models\Shipment;

/**
 * Shipping & delivery reports: overview KPIs, driver performance,
 * zone-wise delivery cost and failed delivery reasons.
 */
class ShippingReportService extends BaseReportService
{
    public function overview(?ReportFilters $f = null): array
    {
        $delivered = Shipment::whereNotNull('delivered_at')
            ->when($f && $f->from, fn ($q) => $q->whereDate('delivered_at', '>=', $f->from))
            ->when($f && $f->to, fn ($q) => $q->whereDate('delivered_at', '<=', $f->to));
        $total = (clone $delivered)->count();
        $shippingCost = (float) (clone $delivered)->sum('shipping_cost');
        $avgTime = (float) (clone $delivered)->whereNotNull('shipped_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, shipped_at, delivered_at) / 24) as avg_days')->value('avg_days') ?? 0;

        return ['kpis' => [
            ['key' => 'delivered', 'label' => 'Delivered Shipments', 'value' => $total],
            ['key' => 'shipping_cost', 'label' => 'Total Shipping Cost', 'value' => round($shippingCost, 2)],
            ['key' => 'avg_delivery_time', 'label' => 'Avg Delivery Time (days)', 'value' => round($avgTime, 2)],
        ]];
    }

    public function driverPerformance(?ReportFilters $f = null): array
    {
        $rows = DeliveryDriver::query()->with('user')
            ->selectRaw('delivery_drivers.id, delivery_drivers.name, COUNT(shipments.id) as total_deliveries')
            ->selectRaw('SUM(CASE WHEN shipments.delivered_at IS NOT NULL THEN 1 ELSE 0 END) as delivered')
            ->leftJoin('shipments', 'shipments.driver_id', '=', 'delivery_drivers.id')
            ->when($f && $f->from, fn ($q) => $q->whereDate('shipments.delivered_at', '>=', $f->from))
            ->when($f && $f->to, fn ($q) => $q->whereDate('shipments.delivered_at', '<=', $f->to))
            ->groupBy('delivery_drivers.id')->orderByDesc('total_deliveries')->get()
            ->map(fn ($r) => ['driver' => $r->name, 'total' => (int) $r->total_deliveries, 'delivered' => (int) $r->delivered, 'rate' => (int) $r->total_deliveries > 0 ? round(((int) $r->delivered / (int) $r->total_deliveries) * 100, 1) : 0]);
        return ['columns' => ['driver' => 'Driver', 'total' => 'Assigned', 'delivered' => 'Delivered', 'rate' => 'Success Rate (%)'], 'rows' => $rows->all()];
    }

    public function zoneCost(?ReportFilters $f = null): array
    {
        $rows = DeliveryZone::query()
            ->selectRaw('delivery_zones.name, COUNT(shipments.id) as total')
            ->selectRaw('COALESCE(SUM(shipments.shipping_cost), 0) as cost')
            ->leftJoin('shipments', 'shipments.delivery_zone_id', '=', 'delivery_zones.id')
            ->when($f && $f->from, fn ($q) => $q->whereDate('shipments.created_at', '>=', $f->from))
            ->when($f && $f->to, fn ($q) => $q->whereDate('shipments.created_at', '<=', $f->to))
            ->groupBy('delivery_zones.id')->orderByDesc('cost')->get()
            ->map(fn ($r) => ['zone' => $r->name, 'shipments' => (int) $r->total, 'cost' => round((float) $r->cost, 2)]);
        return ['columns' => ['zone' => 'Zone', 'shipments' => 'Shipments', 'cost' => 'Cost'], 'rows' => $rows->all()];
    }

    public function failedReasons(?ReportFilters $f = null): array
    {
        $rows = Shipment::query()->whereIn('status', ['failed', 'returned'])
            ->when($f && $f->from, fn ($q) => $q->whereDate('created_at', '>=', $f->from))
            ->when($f && $f->to, fn ($q) => $q->whereDate('created_at', '<=', $f->to))
            ->selectRaw("COALESCE(NULLIF(delivery_instructions, ''), 'Unknown') as reason, COUNT(*) as count")
            ->groupBy('reason')->orderByDesc('count')->get()
            ->map(fn ($r) => ['reason' => mb_strimwidth($r->reason, 0, 80, '…'), 'count' => (int) $r->count]);
        return ['columns' => ['reason' => 'Reason', 'count' => 'Shipments'], 'rows' => $rows->all()];
    }
}