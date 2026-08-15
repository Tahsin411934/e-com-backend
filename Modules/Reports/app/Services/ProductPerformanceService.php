<?php

namespace Modules\Reports\Services;

use Modules\Cart\Models\CartItem;
use Modules\Catalog\Models\ProductVariant;
use Modules\Inventory\Models\InventoryStock;
use Modules\Order\Models\Refund;
use Modules\Reports\Support\ReportFilters;

/**
 * Product performance reports: variant-wise sales, return/refund rate,
 * add-to-cart → purchase conversion and stock turnover rate.
 */
class ProductPerformanceService extends BaseReportService
{
    public function variantWise(ReportFilters $filters): array
    {
        $rows = $this->orderItemQuery($filters, false)
            ->selectRaw('order_items.variant_id, MAX(order_items.product_name) as product_name, MAX(order_items.variant_name) as variant_name, MAX(order_items.sku) as sku')
            ->selectRaw('SUM(order_items.quantity) as qty, SUM(order_items.line_total) as revenue')
            ->groupBy('order_items.variant_id')->orderByDesc('revenue')->get();

        return ['columns' => ['product_name' => 'Product', 'variant_name' => 'Variant', 'sku' => 'SKU', 'qty' => 'Qty', 'revenue' => 'Revenue'], 'rows' => $rows->map(fn ($r) => ['product_name' => $r->product_name, 'variant_name' => $r->variant_name ?: '-', 'sku' => $r->sku ?: '-', 'qty' => (int) $r->qty, 'revenue' => round((float) $r->revenue, 2)])->all()];
    }

    public function returnRate(ReportFilters $filters): array
    {
        $sold = $this->orderItemQuery($filters, false)
            ->selectRaw('order_items.product_id, MAX(order_items.product_name) as product_name, SUM(order_items.quantity) as qty')
            ->groupBy('order_items.product_id')->get();

        $returned = Refund::query()->join('orders', 'orders.id', '=', 'refunds.order_id')
            ->join('order_items', fn ($j) => $j->on('order_items.order_id', '=', 'orders.id')->whereNull('order_items.deleted_at'))
            ->selectRaw('order_items.product_id, SUM(refunds.amount) as refund_amount, COUNT(DISTINCT refunds.id) as refund_count')
            ->when($filters->from, fn ($q) => $q->whereDate('refunds.processed_at', '>=', $filters->from))
            ->when($filters->to, fn ($q) => $q->whereDate('refunds.processed_at', '<=', $filters->to))
            ->groupBy('order_items.product_id')->get()->keyBy('product_id');

        $rows = $sold->map(function ($row) use ($returned) {
            $r = $returned->get($row->product_id);
            return ['product_name' => $row->product_name, 'qty_sold' => (int) $row->qty, 'refund_count' => (int) ($r->refund_count ?? 0), 'refund_amount' => round((float) ($r->refund_amount ?? 0), 2), 'return_rate' => (int) $row->qty > 0 ? round(((int) ($r->refund_count ?? 0) / (int) $row->qty) * 100, 2) : 0];
        })->sortByDesc('refund_amount')->values();

        return ['columns' => ['product_name' => 'Product', 'qty_sold' => 'Qty Sold', 'refund_count' => 'Refunds', 'refund_amount' => 'Refund Amt', 'return_rate' => 'Return Rate %'], 'rows' => $rows->all()];
    }

    public function conversion(ReportFilters $filters): array
    {
        $cartAdds = CartItem::query()->whereNull('deleted_at')
            ->when($filters->from, fn ($q) => $q->whereDate('created_at', '>=', $filters->from))
            ->when($filters->to, fn ($q) => $q->whereDate('created_at', '<=', $filters->to))
            ->selectRaw('variant_id, SUM(quantity) as cart_qty')->groupBy('variant_id')->get()->keyBy('variant_id');

        $purchased = $this->orderItemQuery($filters, false)
            ->selectRaw('order_items.variant_id, SUM(order_items.quantity) as purchased_qty')
            ->groupBy('order_items.variant_id')->get()->keyBy('variant_id');

        $ids = $cartAdds->keys()->merge($purchased->keys())->unique()->all();
        $variants = ProductVariant::whereIn('id', $ids)->with('product')->get();

        $rows = $variants->map(function ($v) use ($cartAdds, $purchased) {
            $added = (int) ($cartAdds->get($v->id)->cart_qty ?? 0);
            $bought = (int) ($purchased->get($v->id)->purchased_qty ?? 0);
            return ['variant' => ($v->product?->name ?? '-') . ' / ' . ($v->name ?? '-'), 'cart_adds' => $added, 'purchased' => $bought, 'conversion_rate' => $added > 0 ? round(($bought / $added) * 100, 2) : 0];
        })->sortByDesc('cart_adds')->values();

        return ['note' => 'Product views are not tracked; funnel is add-to-cart → purchase.', 'columns' => ['variant' => 'Product/Variant', 'cart_adds' => 'Cart Adds', 'purchased' => 'Purchased', 'conversion_rate' => 'Conversion %'], 'rows' => $rows->all()];
    }

    public function stockTurnover(ReportFilters $filters): array
    {
        $sold = $this->orderItemQuery($filters, false)
            ->selectRaw('order_items.product_id, MAX(order_items.product_name) as product_name, SUM(order_items.quantity) as qty')
            ->groupBy('order_items.product_id')->get()->keyBy('product_id');

        $stock = InventoryStock::query()->select('variant_id')->selectRaw('SUM(quantity_on_hand) as on_hand')->groupBy('variant_id')->get();
        $vp = ProductVariant::whereIn('id', $stock->pluck('variant_id'))->pluck('product_id', 'id');

        $byProduct = collect();
        foreach ($stock as $s) { $pid = $vp->get($s->variant_id); $byProduct->put($pid, (float) $byProduct->get($pid, 0) + (float) $s->on_hand); }

        $rows = $sold->map(function ($row) use ($byProduct) {
            $avg = (float) $byProduct->get($row->product_id, 0);
            return ['product_name' => $row->product_name, 'qty_sold' => (int) $row->qty, 'avg_stock' => $avg, 'turnover' => $avg > 0 ? round((int) $row->qty / $avg, 2) : 0];
        })->sortByDesc('qty_sold')->values();

        return ['columns' => ['product_name' => 'Product', 'qty_sold' => 'Qty Sold', 'avg_stock' => 'On-Hand Units', 'turnover' => 'Turnover'], 'rows' => $rows->all()];
    }
}