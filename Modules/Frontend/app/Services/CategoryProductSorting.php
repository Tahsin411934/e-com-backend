<?php

namespace Modules\Frontend\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CategoryProductSorting
{
    public function apply(Builder $query, string $sort): void
    {
        // Customer-selected ordering takes precedence over merchandising order.
        $query->reorder();

        if ($sort === 'name') {
            $query->orderBy('products.name')->orderBy('products.id');
            return;
        }

        if (! in_array($sort, ['price_asc', 'price_desc'], true)) {
            $query->orderByDesc('products.published_at')->orderByDesc('products.id');
            return;
        }

        // Match ProductPricingService: round the base price, clamp discounts,
        // and let a live campaign override the variant discount without stacking.
        $base = 'ROUND(CASE WHEN v.sale_price < 0 THEN 0 ELSE v.sale_price END, 0)';
        $discount = 'CASE WHEN v.discount_percent < 0 THEN 0 WHEN v.discount_percent > 100 THEN 100 ELSE COALESCE(v.discount_percent, 0) END';
        $fallback = "ROUND($base * (1 - ($discount) / 100.0), 0)";
        $offerPrice = "CASE cp.discount_type
            WHEN 'percentage' THEN v.sale_price * (CASE WHEN cp.discount_value > 100 THEN 0 ELSE 1 - cp.discount_value / 100.0 END)
            WHEN 'fixed_amount' THEN CASE WHEN cp.discount_value > v.sale_price THEN 0 ELSE v.sale_price - cp.discount_value END
            WHEN 'fixed_price' THEN CASE WHEN cp.discount_value < 0 THEN 0 ELSE cp.discount_value END
            ELSE v.sale_price END";
        $now = now();
        $offer = DB::table('campaign_products as cp')
            ->join('campaigns as c', 'c.id', '=', 'cp.campaign_id')
            ->selectRaw("CASE WHEN ROUND(v.sale_price - ($offerPrice), 4) > 0 THEN ROUND(ROUND(($offerPrice), 4), 0) ELSE $fallback END")
            ->whereColumn('cp.product_id', 'v.product_id')
            ->where(fn ($q) => $q->whereNull('cp.variant_id')->orWhereColumn('cp.variant_id', 'v.id'))
            ->whereNull('cp.deleted_at')->whereNull('c.deleted_at')
            ->where('c.is_active', true)->where('c.status', 'active')
            ->where(fn ($q) => $q->whereNull('c.starts_at')->orWhere('c.starts_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('c.ends_at')->orWhere('c.ends_at', '>=', $now))
            ->where(fn ($q) => $q->whereColumn('c.store_id', 'products.store_id')
                ->orWhere(fn ($q) => $q->whereNull('c.store_id')->whereNull('products.store_id')))
            ->orderByRaw('CASE WHEN cp.variant_id IS NULL THEN 0 ELSE 1 END DESC')
            ->orderByDesc('c.priority')->orderBy('cp.id')->limit(1);

        $price = DB::table('product_variants as v')
            ->selectRaw('MIN(COALESCE(('.$offer->toSql()."), $fallback))", $offer->getBindings())
            ->whereColumn('v.product_id', 'products.id')
            ->where('v.status', 'active')->whereNull('v.deleted_at');

        $query->select('products.*')->selectSub($price, 'category_sort_price')
            ->orderByRaw('category_sort_price IS NULL')
            ->orderBy('category_sort_price', $sort === 'price_asc' ? 'asc' : 'desc')
            ->orderBy('products.id');
    }
}
