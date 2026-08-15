<?php

namespace Modules\Reports\Services;

use Modules\Inventory\Models\InventoryLocation;
use Modules\Inventory\Models\InventoryStock;
use Modules\Reports\Support\ReportFilters;

/**
 * Inventory reports: current stock by location, low & out of stock,
 * variant-level stock and stock valuation at cost price.
 */
class InventoryReportService extends BaseReportService
{
    public function currentStock(ReportFilters $filters): array
    {
        $stock = InventoryStock::query()->join('product_variants', 'product_variants.id', '=', 'inventory_stock.variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->selectRaw('COALESCE(product_variants.sku,"-") as sku, products.name as product_name, product_variants.name as variant_name')
            ->selectRaw('inventory_stock.location_id, inventory_stock.quantity_on_hand, inventory_stock.quantity_reserved, inventory_stock.reorder_point')
            ->when($filters->productId, fn ($q) => $q->where('products.id', $filters->productId))->get();
        $loc = InventoryLocation::pluck('name', 'id');
        $rows = $stock->map(fn ($s) => ['product' => $s->product_name . ($s->variant_name ? ' / ' . $s->variant_name : ''), 'sku' => $s->sku, 'location' => $loc->get($s->location_id) ?? '-', 'on_hand' => (int) $s->quantity_on_hand, 'reserved' => (int) $s->quantity_reserved, 'available' => max(0, (int) $s->quantity_on_hand - (int) $s->quantity_reserved), 'reorder_point' => (int) $s->reorder_point]);
        return ['columns' => ['product' => 'Product', 'sku' => 'SKU', 'location' => 'Location', 'on_hand' => 'On Hand', 'reserved' => 'Reserved', 'available' => 'Available', 'reorder_point' => 'Reorder'], 'rows' => $rows->sortBy('location')->values()->all()];
    }

    public function lowOutOfStock(ReportFilters $filters): array
    {
        $rows = InventoryStock::query()->join('product_variants', 'product_variants.id', '=', 'inventory_stock.variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->selectRaw('product_variants.sku as sku, products.name as product_name, product_variants.name as variant_name, inventory_stock.quantity_on_hand, inventory_stock.quantity_reserved')
            ->whereRaw('inventory_stock.quantity_on_hand - inventory_stock.quantity_reserved <= inventory_stock.reorder_point')
            ->orderByRaw('inventory_stock.quantity_on_hand - inventory_stock.quantity_reserved asc')->get()
            ->map(fn ($s) => ['product' => $s->product_name . ($s->variant_name ? ' / ' . $s->variant_name : ''), 'sku' => $s->sku, 'available' => max(0, (int) $s->quantity_on_hand - (int) $s->quantity_reserved), 'status' => ((int) $s->quantity_on_hand - (int) $s->quantity_reserved) <= 0 ? 'Out of stock' : 'Low stock'])->values();
        return ['columns' => ['product' => 'Product', 'sku' => 'SKU', 'available' => 'Available', 'status' => 'Status'], 'rows' => $rows->all()];
    }

    public function variantLevelStock(ReportFilters $filters): array
    {
        $rows = InventoryStock::query()->join('product_variants', 'product_variants.id', '=', 'inventory_stock.variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->selectRaw('products.name as product_name, product_variants.name as variant_name, product_variants.sku')
            ->selectRaw('SUM(inventory_stock.quantity_on_hand) as on_hand, SUM(inventory_stock.quantity_reserved) as reserved, COUNT(DISTINCT inventory_stock.location_id) as locations')
            ->groupBy('products.id', 'product_variants.id')->orderByDesc('on_hand')->get()
            ->map(fn ($r) => ['product' => $r->product_name, 'variant' => $r->variant_name ?: '-', 'sku' => $r->sku, 'on_hand' => (int) $r->on_hand, 'reserved' => (int) $r->reserved, 'available' => max(0, (int) $r->on_hand - (int) $r->reserved), 'locations' => (int) $r->locations]);
        return ['columns' => ['product' => 'Product', 'variant' => 'Variant', 'sku' => 'SKU', 'on_hand' => 'On Hand', 'reserved' => 'Reserved', 'available' => 'Available', 'locations' => 'Locations'], 'rows' => $rows->all()];
    }

    public function valuation(ReportFilters $filters): array
    {
        $rows = InventoryStock::query()->join('product_variants', 'product_variants.id', '=', 'inventory_stock.variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->selectRaw('products.name as product_name, product_variants.name as variant_name, product_variants.sku')
            ->selectRaw('SUM(inventory_stock.quantity_on_hand) as on_hand, MAX(product_variants.cost_price) as cost_price')
            ->groupBy('products.id', 'product_variants.id')->get()
            ->map(fn ($r) => ['product' => $r->product_name . ($r->variant_name ? ' / ' . $r->variant_name : ''), 'sku' => $r->sku, 'on_hand' => (int) $r->on_hand, 'unit_cost' => round((float) $r->cost_price, 2), 'total_value' => round((float) $r->on_hand * (float) $r->cost_price, 2)]);
        return ['summary' => ['total_value' => round($rows->sum('total_value'), 2)], 'columns' => ['product' => 'Product', 'sku' => 'SKU', 'on_hand' => 'Units', 'unit_cost' => 'Unit Cost', 'total_value' => 'Value'], 'rows' => $rows->sortByDesc('total_value')->values()->all()];
    }
}