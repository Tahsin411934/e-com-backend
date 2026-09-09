<?php

namespace Modules\Frontend\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Cart\Services\CampaignPricingService;
use Modules\Catalog\Models\Product;
use Modules\Frontend\Services\ProductPricingService;

class ProductDetailResource extends JsonResource
{
    /**
     * Transform the product resource for the product detail page.
     *
     * Includes: full product info, brand, gallery images, variants with prices,
     * categories, reviews with user info, average rating, and related products.
     */
    /**
     * Helper: normalize image URL to handle both old (/storage/...) and new (relative) formats.
     */
    private function imageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }
        // If already a full URL, return as-is
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        // Strip leading /storage/ if present (legacy format)
        $clean = ltrim($path, '/');
        if (str_starts_with($clean, 'storage/')) {
            $clean = substr($clean, 8);
        }

        return $clean ? asset('storage/'.$clean) : null;
    }

    public function toArray(Request $request): array
    {
        // ── Images (gallery) ──
        $images = $this->images->sortBy('sort_order');
        $mainImage = $images->firstWhere('is_main', true) ?? $images->first();

        $gallery = $images->map(fn ($img) => [
            'id' => $img->id,
            'url' => $this->imageUrl($img->image_url),
            'alt_text' => $img->alt_text ?? $this->name,
            'is_main' => (bool) $img->is_main,
            'sort_order' => $img->sort_order,
        ]);

        // ── Variants ──
        // Explicitly drop any soft-deleted (deleted_at) variants/options so they never
        // surface on the storefront, even if they were loaded through a bare relation.
        $activeVariants = $this->variants
            ->whereNull('deleted_at')
            ->where('status', 'active');
        $pricing = app(CampaignPricingService::class);
        $variantPrices = $activeVariants->map(fn ($variant) => $this->variantPricing($variant, $pricing)['final']);
        $optionPrices = $activeVariants->flatMap(fn ($variant) => $variant->options
            ->where('status', 'active')
            ->map(fn ($option) => $this->optionDiscountedPrice($variant, $option, $pricing)));
        $allPrices = $variantPrices->merge($optionPrices);
        $minPrice = $allPrices->min();
        $maxPrice = $allPrices->max();

        $variants = $activeVariants->map(function ($v) use ($pricing) {
            $variantPrice = $this->variantPricing($v, $pricing);
            $final = $variantPrice['final'];
            $regular = $variantPrice['regular'];
            $compare = $variantPrice['compare'];
            $hasDiscount = $variantPrice['has_discount'];
            $effectivePct = $hasDiscount && $regular > 0
                ? (float) round((($regular - $final) / $regular) * 100, 0)
                : 0;

            return [
                'id' => $v->id,
                'name' => $v->name,
                'sku' => $v->sku,
                'barcode' => $v->barcode,
                'sale_price' => $final,
                'compare_at_price' => $compare,
                'regular_price' => $regular,
                'discount_price' => $final,
                'discount_percent' => $effectivePct,
                'has_discount' => $hasDiscount,
                'campaign_name' => $variantPrice['campaign']?->name,
                'cost_price' => round((float) $v->cost_price, 0),
                'stock' => $v->track_inventory ? ($v->stock ?? 0) : null,
                'track_inventory' => (bool) $v->track_inventory,
                'allow_backorder' => (bool) $v->allow_backorder,
                'attributes' => collect($v->attributes ?? [])->except(['color', 'color_hex'])->all(),
                'image' => $this->imageUrl($v->images->first()?->image_url),
                'options' => $v->options->where('status', 'active')->values()->map(fn ($o) => [
                    'id' => $o->id,
                    'color_name' => $o->color_name,
                    'color_code' => $o->color_code,
                    'sku' => $o->sku,
                    'barcode' => $o->barcode,
                    'image_url' => $this->imageUrl($o->image_url),
                    'cost_price' => ($o->cost_price ?? null) !== null ? round((float) $o->cost_price, 0) : round((float) $v->cost_price, 0),
                    'sale_price' => $this->optionDiscountedPrice($v, $o, $pricing),
                    'regular_price' => $this->optionRegularPrice($v, $o, $pricing),
                    'discount_price' => $this->optionDiscountedPrice($v, $o, $pricing),
                    'compare_at_price' => $this->optionComparePrice($v, $o),
                    'discount_percent' => $this->optionDiscountPercent($v, $o, $pricing),
                    'has_discount' => $this->optionDiscountedPrice($v, $o, $pricing) < $this->optionRegularPrice($v, $o, $pricing),
                    'price_adjustment' => (float) ($o->price_adjustment ?? 0),
                    'stock' => $v->track_inventory ? (int) $o->stock : null,
                ]),
            ];
        });

        // ── Attribute options (colors / sizes) ──
        $colors = $activeVariants->flatMap(function ($variant) {
            return $variant->options->where('status', 'active')->pluck('color_name');
        })->filter()->unique()->values();
        $sizes = $activeVariants->pluck('attributes')->map(fn ($a) => $a['size'] ?? null)->filter()->unique()->values();

        $colorOptions = $colors->map(fn ($color) => [
            'value' => $color,
            'hex' => $activeVariants->flatMap(fn ($v) => $v->options->where('color_name', $color)->pluck('color_code'))->filter()->first()
                ?? null,
            'available_count' => $activeVariants->filter(fn ($v) => $v->options->where('color_name', $color)->contains(fn ($o) => ! $v->track_inventory || ($o->stock ?? 0) > 0))->count(),
            'available' => $activeVariants->contains(fn ($v) => $v->options->where('color_name', $color)->contains(fn ($o) => ! $v->track_inventory || ($o->stock ?? 0) > 0)),
        ])->values();

        $sizeOptions = $sizes->map(fn ($size) => [
            'value' => $size,
            'available_count' => $activeVariants->filter(fn ($v) => ($v->attributes['size'] ?? null) === $size && (! $v->track_inventory || $v->options->contains(fn ($o) => ($o->stock ?? 0) > 0)))->count(),
            'available' => $activeVariants->filter(fn ($v) => ($v->attributes['size'] ?? null) === $size && (! $v->track_inventory || $v->options->contains(fn ($o) => ($o->stock ?? 0) > 0)))->count() > 0,
        ])->values();

        // ── Brand ──
        $brand = $this->brand ? [
            'id' => $this->brand->id,
            'name' => $this->brand->name,
            'slug' => $this->brand->slug,
            'logo' => $this->imageUrl($this->brand->logo_url),
        ] : null;

        // ── Categories ──
        $categories = $this->categories->map(fn ($cat) => [
            'id' => $cat->id,
            'name' => $cat->name,
            'slug' => $cat->slug,
        ]);

        // ── Reviews ──
        $approvedReviews = $this->reviews->where('status', 'approved');
        $avgRating = $approvedReviews->avg('rating');
        $totalReviews = $approvedReviews->count();

        $ratingDistribution = collect(range(5, 1))->mapWithKeys(fn ($star) => [
            (string) $star => $approvedReviews->where('rating', $star)->count(),
        ]);

        $reviews = $approvedReviews->map(fn ($r) => [
            'id' => $r->id,
            'rating' => $r->rating,
            'title' => $r->title,
            'body' => $r->body,
            'is_verified_purchase' => (bool) $r->is_verified_purchase,
            'user' => $r->user ? [
                'id' => $r->user->id,
                'name' => $r->user->name,
                'avatar' => $r->user->avatar ?? null,
            ] : null,
            'created_at' => $r->created_at?->diffForHumans(),
        ]);

        // ── Related Products (same categories, excluding current) ──
        $relatedProducts = collect();
        if ($this->relationLoaded('relatedProducts')) {
            $pricing = app(ProductPricingService::class);
            $relatedProducts = $this->relatedProducts->map(fn ($p) => array_merge([
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'order_column' => $p->order_column ?? 0,
                'short_description' => $p->short_description,
                'main_image' => $this->imageUrl($p->images->firstWhere('is_main', true)?->image_url
                    ?? $p->images->first()?->image_url),
                'product_type' => $p->product_type,
            ], $pricing->priceInfo($p)));
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'order_column' => $this->order_column ?? 0,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'product_type' => $this->product_type,
            // Product-level delivery charge (৳) — the highest one in the cart
            // is taken once as the shipping charge at checkout. Falls back to
            // the default when the column is absent (pre-migration).
            'delivery_charge' => (float) ($this->delivery_charge ?? Product::DEFAULT_DELIVERY_CHARGE),
            'status' => $this->status,
            'visibility' => $this->visibility,
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            'published_at' => $this->published_at?->toIso8601String(),

            // Pricing summary
            'price_range' => [
                'min' => $minPrice ? (float) $minPrice : null,
                'max' => $maxPrice ? (float) $maxPrice : null,
            ],

            // Relations
            'brand' => $brand,
            'categories' => $categories,
            'main_image' => $this->imageUrl($mainImage?->image_url),
            'gallery' => $gallery->values(),
            'variants' => $variants->values(),
            'attribute_options' => [
                'colors' => $colorOptions,
                'sizes' => $sizeOptions,
            ],

            // Reviews
            'reviews' => [
                'average_rating' => $avgRating ? round($avgRating, 1) : 0,
                'total_reviews' => $totalReviews,
                'rating_distribution' => $ratingDistribution,
                'items' => $reviews->values(),
            ],

            // Related products
            'related_products' => $relatedProducts->values(),
        ];
    }

    /**
     * Variant pricing with discount_percent + campaign applied.
     *
     * Mirrors CartService::syncCart:
     *   final = sale_price * (1 - discount_percent / 100), then campaign pricing on top.
     *
     * @return array{price: float, regular: float, compare: float|null, campaign: object|null}
     */
    private function variantPricing($variant, $pricing): array
    {
        $salePrice = round(max(0, (float) $variant->sale_price), 0);
        $discountPercent = max(0, min(100, (float) ($variant->discount_percent ?? 0)));

        // Campaign pricing takes precedence over the variant's own discount:
        // if under a live campaign use the campaign price, otherwise the variant discount.
        $campaign = $pricing->priceFor($variant, 0);

        if ($campaign['campaign']) {
            $final = round(max(0, (float) $campaign['price']), 0);
        } else {
            $final = round(max(0, $salePrice * (1 - $discountPercent / 100)), 0);
        }

        $compare = $campaign['campaign']
            ? round(max(0, (float) $campaign['original_price']), 0)
            : ($variant->compare_at_price !== null ? round((float) $variant->compare_at_price, 0) : null);

        $hasDiscount = $final < $salePrice;

        return [
            'has_discount' => $hasDiscount,
            'final' => $final,
            // regular = base sale price (pre-discount), never lower than final.
            'regular' => $salePrice,
            // compare (strikethrough reference) only shown when there is a real discount.
            'compare' => $hasDiscount ? ($compare !== null && $compare > $final ? $compare : $salePrice) : null,
            'campaign' => $campaign['campaign'],
        ];
    }

    /**
     * Effective discount % for an option (falls back to the parent variant discount).
     */
    private function optionDiscountPercent($variant, $option, $pricing = null): float
    {
        if ($pricing === null) {
            return (float) (($option->discount_percent ?? null) !== null ? $option->discount_percent : ($variant->discount_percent ?? 0));
        }

        $regular = $this->optionRegularPrice($variant, $option, $pricing);
        $final = $this->optionDiscountedPrice($variant, $option, $pricing);

        return $regular > 0 && $final < $regular
            ? (float) round((($regular - $final) / $regular) * 100, 0)
            : 0;
    }

    /**
     * Option regular price = its own sale_price, else parent variant sale_price + adjustment (discount NOT applied yet).
     */
    private function optionRegularPrice($variant, $option, $pricing): float
    {
        $basePrice = $option->sale_price !== null
            ? (float) $option->sale_price
            : (float) $variant->sale_price + (float) ($option->price_adjustment ?? 0);

        return round(max(0, $basePrice), 0);
    }

    /**
     * Option final (discounted) price = regular price with the effective discount applied.
     */
    private function optionDiscountedPrice($variant, $option, $pricing): float
    {
        $regular = $this->optionRegularPrice($variant, $option, $pricing);

        // Campaign pricing takes precedence: optionRegularPrice already resolves it,
        // so if the variant is under a live campaign we must NOT stack the option's
        // own discount on top — the campaign price wins.
        $basePrice = $option->sale_price !== null
            ? (float) $option->sale_price
            : (float) $variant->sale_price + (float) ($option->price_adjustment ?? 0);
        $offer = $pricing->priceFor($variant, $basePrice - (float) $variant->sale_price);

        if ($offer['campaign']) {
            return round(max(0, (float) $offer['price']), 2);
        }

        $regular *= 1 - ($this->optionDiscountPercent($variant, $option) / 100);

        return round(max(0, $regular), 0);
    }

    private function optionComparePrice($variant, $option): ?float
    {
        if ($option->compare_at_price !== null) {
            return round((float) $option->compare_at_price, 0);
        }

        if ($this->optionDiscountPercent($variant, $option) > 0 && $option->sale_price !== null) {
            return round((float) $option->sale_price, 0);
        }

        return null;
    }
}
