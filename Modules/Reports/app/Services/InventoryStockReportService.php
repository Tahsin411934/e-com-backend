<?php

namespace Modules\Reports\Services;

use Modules\Inventory\Models\InventoryMovement;
use Modules\Inventory\Models\InventoryStock;
use Modules\Order\Models\OrderItem;
use Modules\Reports\Support\ReportFilters;

/**
 * Inventory health and planning reports: movements, damaged/expired/lost stock,
 * dead stock, reorder recommendations and stock transfers.
 */
class InventoryStockReportService extends BaseReportService
{
    const TYPES = ['purchase' => 'Purchase','sale' => 'Sale','adjustment' => 'Adjustment','return' => 'Return','transfer_in' => 'Transfer In','transfer_out' => 'Transfer Out','damaged' => 'Damaged','expired' => 'Expired','lost' => 'Lost'];

    public function movements(ReportFilters $filters): array
    {
        $rows = InventoryMovement::query()
            ->selectRaw('movement_type')
            ->selectRaw('COALESCE(SUM(quantity),0) as qty')
            ->selectRaw('COUNT(*) as occurrences')
            ->when($filters->from, fn ($q) => $q->whereDate('created_at', '>=', $filters->from))
            ->when($filters->to, fn ($q) => $q->whereDate('created_at', '<=', $filters->to))
            ->groupBy('movement_type')->get()
            ->map(fn ($r) => ['movement' => self::TYPES[$r->movement_type] ?? $r->movement_type, 'qty' => (int) $r->qty, 'occurrences' => (int) $r->occurrences]);
        return ['columns' => ['movement' => 'Type', 'qty' => 'Qty', 'occurrences' => 'Occurrences'], 'rows' => $rows->all()];
    }

    public function damagedExpiredLost(ReportFilters $filters): array
    {
        $rows = InventoryMovement::query()
            ->selectRaw('movement_type, COALESCE(SUM(quantity),0) as qty')
            ->whereIn('movement_type', ['damaged', 'expired', 'lost'])
            ->when($filters->from, fn ($q) => $q->whereDate('created_at', '>=', $filters->from))
            ->when($filters->to, fn ($q) => $q->whereDate('created_at', '<=', $filters->to))
            ->groupBy('movement_type')->get()
            ->map(fn ($r) => ['movement' => self::TYPES[$r->movement_type] ?? $r->movement_type, 'type' => $r->movement_type, 'qty' => (int) $r->qty]);
        return ['columns' => ['movement' => 'Type', 'qty' => 'Qty'], 'rows' => $rows->all(), 'total' => $rows->sum('qty')];
    }

    public function deadStock(?ReportFilters $f = null, int $days = 90, int $limit = 50): array
    {
        $cutoff = now()->subDays($days);
        $soldRecently = OrderItem::query()->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereNull('orders.deleted_at')->where('orders.placed_at', '>=', $cutoff)->pluck('order_items.variant_id')->unique()->all();

        $rows = InventoryStock::query()
            ->join('product_variants', 'product_variants.id', '=', 'inventory_stock.variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->selectRaw('products.name as product_name, product_variants.name as variant_name, product_variants.sku')
            ->selectRaw('SUM(inventory_stock.quantity_on_hand) as on_hand')
            ->when(!empty($soldRecently), fn ($q) => $q->whereNotIn('inventory_stock.variant_id', $soldRecently))
            ->groupBy('products.id', 'product_variants.id')->havingRaw('SUM(inventory_stock.quantity_on_hand) > 0')
            ->orderByDesc('on_hand')->limit($limit)->get()
            ->map(fn ($r) => ['product' => $r->product_name . ($r->variant_name ? ' / ' . $r->variant_name : ''), 'sku' => $r->sku, 'on_hand' => (int) $r->on_hand, 'days_without_sale' => $days]);
        return ['columns' => ['product' => 'Product', 'sku' => 'SKU', 'on_hand' => 'On Hand', 'days_without_sale' => 'Days w/o Sale'], 'rows' => $rows->all()];
    }

    public function reorder(?ReportFilters $f = null): array
    {
        $rows = InventoryStock::query()
            ->join('product_variants', 'product_variants.id', '=', 'inventory_stock.variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->join('inventory_locations', 'inventory_locations.id', '=', 'inventory_stock.location_id')
            ->selectRaw('products.name as product_name, product_variants.name as variant_name, product_variants.sku')
            ->selectRaw('inventory_locations.name as location, inventory_stock.quantity_on_hand, inventory_stock.quantity_reserved, inventory_stock.reorder_point')
            ->whereRaw('inventory_stock.quantity_on_hand - inventory_stock.quantity_reserved <= inventory_stock.reorder_point')
            ->get()->map(fn ($s) => ['product' => $s->product_name . ($s->variant_name ? ' / ' . $s->variant_name : ''), 'sku' => $s->sku, 'location' => $s->location, 'available' => max(0, (int) $s->quantity_on_hand - (int) $s->quantity_reserved), 'reorder_point' => (int) $s->reorder_point, 'suggested' => max(0, (int) $s->reorder_point - ((int) $s->quantity_on_hand - (int) $s->quantity_reserved))]);
        return ['columns' => ['product' => 'Product', 'sku' => 'SKU', 'location' => 'Location', 'available' => 'Available', 'reorder_point' => 'Reorder Pt', 'suggested' => 'Suggested'], 'rows' => $rows->values()->all()];
    }

    public function transfers(?ReportFilters $f = null): array
    {
        $rows = InventoryMovement::query()
            ->selectRaw('movement_type, COUNT(*) as occurrences, COALESCE(SUM(quantity),0) as qty')
            ->whereIn('movement_type', ['transfer_in', 'transfer_out'])
            ->when($f && $f->from, fn ($q) => $q->whereDate('created_at', '>=', $f->from))
            ->when($f && $f->to, fn ($q) => $q->whereDate('created_at', '<=', $f->to))
            ->groupBy('movement_type')->get()
            ->map(fn ($r) => ['movement' => self::TYPES[$r->movement_type] ?? $r->movement_type, 'qty' => (int) $r->qty, 'occurrences' => (int) $r->occurrences]);
        return ['columns' => ['movement' => 'Transfer', 'qty' => 'Qty', 'occurrences' => 'Occurrences'], 'rows' => $rows->all()];
    }
}