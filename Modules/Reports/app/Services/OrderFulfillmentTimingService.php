<?php

namespace Modules\Reports\Services;

use Modules\Order\Models\Delivery;
use Modules\Order\Models\Order;
use Modules\Reports\Support\ReportFilters;
use Modules\Shipping\Models\Shipment;

/**
 * Order fulfilment timing reports: avg fulfilment time, failed/returned
 * deliveries and COD vs prepaid split.
 */
class OrderFulfillmentTimingService extends BaseReportService
{
    public function fulfillmentTimes(?ReportFilters $f = null, int $target = 7): array
    {
        $base = Shipment::query()->join('orders', 'orders.id', '=', 'shipments.order_id')
            ->whereNull('orders.deleted_at')->whereNotNull('shipments.delivered_at')
            ->when($f && $f->from, fn ($q) => $q->whereDate('shipments.delivered_at', '>=', $f->from))
            ->when($f && $f->to, fn ($q) => $q->whereDate('shipments.delivered_at', '<=', $f->to));

        $avg = (clone $base)->selectRaw('AVG(TIMESTAMPDIFF(HOUR, orders.placed_at, shipments.delivered_at) / 24) as avg')->value('avg');
        $delayed = (clone $base)->whereRaw('TIMESTAMPDIFF(DAY, orders.placed_at, shipments.delivered_at) > ?', [$target])->count();
        $total = (clone $base)->count();

        return ['kpis' => [
            ['key' => 'avg_delivery_days', 'label' => 'Avg Order→Delivery (days)', 'value' => round((float) ($avg ?? 0), 2)],
            ['key' => 'delivered_total', 'label' => 'Delivered Shipments', 'value' => $total],
            ['key' => 'delayed', 'label' => "Delayed (> {$target}d)", 'value' => $delayed],
            ['key' => 'on_time', 'label' => 'On-Time Rate (%)', 'value' => $total > 0 ? round((($total - $delayed) / $total) * 100, 1) : 0],
        ]];
    }

    public function failedDeliveries(?ReportFilters $f = null): array
    {
        $rows = Delivery::query()->whereIn('status', ['failed', 'returned'])
            ->join('orders', 'orders.id', '=', 'deliveries.order_id')->whereNull('orders.deleted_at')
            ->when($f && $f->from, fn ($q) => $q->whereDate('deliveries.created_at', '>=', $f->from))
            ->when($f && $f->to, fn ($q) => $q->whereDate('deliveries.created_at', '<=', $f->to))
            ->selectRaw('orders.order_number, deliveries.status, orders.grand_total as amount')
            ->orderByDesc('deliveries.created_at')->limit(200)->get()
            ->map(fn ($r) => ['order_number' => $r->order_number, 'status' => $r->status, 'amount' => round((float) $r->amount, 2)]);
        return ['columns' => ['order_number' => 'Order #', 'status' => 'Status', 'amount' => 'Amount'], 'rows' => $rows->all()];
    }

    public function codVsPrepaid(ReportFilters $filters): array
    {
        $rows = $this->orderQuery($filters, false)
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->selectRaw("CASE WHEN LOWER(payments.method) = 'cod' THEN 'COD' ELSE 'Prepaid' END as type")
            ->selectRaw('COUNT(DISTINCT orders.id) as orders, SUM(payments.amount) as amount')
            ->groupBy('type')->orderByDesc('amount')->get()
            ->map(fn ($r) => ['type' => $r->type, 'orders' => (int) $r->orders, 'amount' => round((float) $r->amount, 2)]);
        return ['columns' => ['type' => 'Payment Type', 'orders' => 'Orders', 'amount' => 'Amount'], 'rows' => $rows->all()];
    }
}