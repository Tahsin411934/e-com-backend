<?php

namespace Modules\Frontend\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
        if (!$path) return null;
        // If already a full URL, return as-is
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) return $path;
        // Strip leading /storage/ if present (legacy format)
        $clean = ltrim($path, '/');
        if (str_starts_with($clean, 'storage/')) {
            $clean = substr($clean, 8);
        }
        return $clean ? asset('storage/' . $clean) : null;
    }

    public function toArray(Request $request): array
    {
        // ── Images (gallery) ──
        $images = $this->images->sortBy('sort_order');
        $mainImage = $images->firstWhere('is_main', true) ?? $images->first();

        $gallery = $images->map(fn($img) => [
            'id'       => $img->id,
            'url'      => $this->imageUrl($img->image_url),
            'alt_text' => $img->alt_text ?? $this->name,
            'is_main'  => (bool) $img->is_main,
            'sort_order' => $img->sort_order,
        ]);

        // ── Variants ──
        $activeVariants = $this->variants->where('status', 'active');
        $pricing = app(\Modules\Cart\Services\CampaignPricingService::class);
        $pricedVariants = $activeVariants->map(fn ($variant) => $pricing->priceFor($variant));
        $optionPrices = $activeVariants->flatMap(fn ($variant) => $variant->options
            ->where('status', 'active')
            ->map(fn ($option) => $this->optionPrice($variant, $option, $pricing)));
        $allPrices = $pricedVariants->pluck('price')->merge($optionPrices);
        $minPrice = $allPrices->min();
        $maxPrice = $allPrices->max();

        $variants = $activeVariants->map(function ($v) use ($pricing) {
            $campaignPrice = $pricing->priceFor($v);
            return [
            'id'              => $v->id,
            'name'            => $v->name,
            'sku'             => $v->sku,
            'barcode'         => $v->barcode,
            'sale_price'      => $campaignPrice['price'],
            'compare_at_price'=> $campaignPrice['campaign'] ? $campaignPrice['original_price'] : ($v->compare_at_price ? (float) $v->compare_at_price : null),
            'campaign_name'   => $campaignPrice['campaign']?->name,
            'cost_price'      => (float) $v->cost_price,
            'stock'           => $v->track_inventory ? ($v->stock ?? 0) : null,
            'track_inventory' => (bool) $v->track_inventory,
            'allow_backorder' => (bool) $v->allow_backorder,
            'attributes'      => collect($v->attributes ?? [])->except(['color', 'color_hex'])->all(),
            'image'           => $this->imageUrl($v->images->first()?->image_url),
            'options'         => $v->options->where('status', 'active')->values()->map(fn($o) => [
                'id'               => $o->id,
                'color_name'       => $o->color_name,
                'color_code'       => $o->color_code,
                'sku'              => $o->sku,
                'barcode'          => $o->barcode,
                'image_url'        => $this->imageUrl($o->image_url),
                'cost_price'       => $o->cost_price !== null ? (float) $o->cost_price : (float) $v->cost_price,
                'sale_price'       => $this->optionPrice($v, $o, $pricing),
                'compare_at_price' => $this->optionComparePrice($o),
                'discount_percent' => (float) ($o->discount_percent ?? 0),
                'price_adjustment' => (float) $o->price_adjustment,
                'stock'            => $v->track_inventory ? (int) $o->stock : null,
            ]),
            ];
        });

        // ── Attribute options (colors / sizes) ──
        $colors = $activeVariants->flatMap(function ($variant) {
            return $variant->options->where('status', 'active')->pluck('color_name');
        })->filter()->unique()->values();
        $sizes = $activeVariants->pluck('attributes')->map(fn($a) => $a['size'] ?? null)->filter()->unique()->values();

        $colorOptions = $colors->map(fn($color) => [
            'value' => $color,
            'hex' => $activeVariants->flatMap(fn($v) => $v->options->where('color_name', $color)->pluck('color_code'))->filter()->first()
                ?? null,
            'available_count' => $activeVariants->filter(fn($v) => $v->options->where('color_name', $color)->contains(fn($o) => ! $v->track_inventory || ($o->stock ?? 0) > 0))->count(),
            'available' => $activeVariants->contains(fn($v) => $v->options->where('color_name', $color)->contains(fn($o) => ! $v->track_inventory || ($o->stock ?? 0) > 0)),
        ])->values();

        $sizeOptions = $sizes->map(fn($size) => [
            'value' => $size,
            'available_count' => $activeVariants->filter(fn($v) => ($v->attributes['size'] ?? null) === $size && (! $v->track_inventory || $v->options->contains(fn($o) => ($o->stock ?? 0) > 0)))->count(),
            'available' => $activeVariants->filter(fn($v) => ($v->attributes['size'] ?? null) === $size && (! $v->track_inventory || $v->options->contains(fn($o) => ($o->stock ?? 0) > 0)))->count() > 0,
        ])->values();

        // ── Brand ──
        $brand = $this->brand ? [
            'id'   => $this->brand->id,
            'name' => $this->brand->name,
            'slug' => $this->brand->slug,
            'logo' => $this->imageUrl($this->brand->logo_url),
        ] : null;

        // ── Categories ──
        $categories = $this->categories->map(fn($cat) => [
            'id'   => $cat->id,
            'name' => $cat->name,
            'slug' => $cat->slug,
        ]);

        // ── Reviews ──
        $approvedReviews = $this->reviews->where('status', 'approved');
        $avgRating = $approvedReviews->avg('rating');
        $totalReviews = $approvedReviews->count();

        $ratingDistribution = collect(range(5, 1))->mapWithKeys(fn($star) => [
            (string) $star => $approvedReviews->where('rating', $star)->count(),
        ]);

        $reviews = $approvedReviews->map(fn($r) => [
            'id'                => $r->id,
            'rating'            => $r->rating,
            'title'             => $r->title,
            'body'              => $r->body,
            'is_verified_purchase' => (bool) $r->is_verified_purchase,
            'user'              => $r->user ? [
                'id'   => $r->user->id,
                'name' => $r->user->name,
                'avatar' => $r->user->avatar ?? null,
            ] : null,
            'created_at'        => $r->created_at?->diffForHumans(),
        ]);

        // ── Related Products (same categories, excluding current) ──
        $relatedProducts = collect();
        if ($this->relationLoaded('relatedProducts')) {
            $relatedProducts = $this->relatedProducts->map(fn($p) => [
                'id'               => $p->id,
                'name'             => $p->name,
                'slug'             => $p->slug,
                'order_column'     => $p->order_column ?? 0,
                'short_description'=> $p->short_description,
                'main_image'       => $this->imageUrl($p->images->firstWhere('is_main', true)?->image_url
                    ?? $p->images->first()?->image_url),
                'price'            => $p->variants->where('status', 'active')->min('sale_price'),
                'product_type'     => $p->product_type,
            ]);
        }

        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'slug'              => $this->slug,
            'order_column'      => $this->order_column ?? 0,
            'short_description' => $this->short_description,
            'description'       => $this->description,
            'product_type'      => $this->product_type,
            'status'            => $this->status,
            'visibility'        => $this->visibility,
            'seo_title'         => $this->seo_title,
            'seo_description'   => $this->seo_description,
            'published_at'      => $this->published_at?->toIso8601String(),

            // Pricing summary
            'price_range'       => [
                'min' => $minPrice ? (float) $minPrice : null,
                'max' => $maxPrice ? (float) $maxPrice : null,
            ],

            // Relations
            'brand'             => $brand,
            'categories'        => $categories,
            'main_image'        => $this->imageUrl($mainImage?->image_url),
            'gallery'           => $gallery->values(),
            'variants'          => $variants->values(),
            'attribute_options' => [
                'colors' => $colorOptions,
                'sizes'  => $sizeOptions,
            ],

            // Reviews
            'reviews'           => [
                'average_rating'       => $avgRating ? round($avgRating, 1) : 0,
                'total_reviews'        => $totalReviews,
                'rating_distribution'  => $ratingDistribution,
                'items'                => $reviews->values(),
            ],

            // Related products
            'related_products'  => $relatedProducts->values(),
        ];
    }

    private function optionPrice($variant, $option, $pricing): float
    {
        $basePrice = $option->sale_price !== null
            ? (float) $option->sale_price
            : (float) $variant->sale_price + (float) $option->price_adjustment;
        $basePrice *= 1 - ((float) ($option->discount_percent ?? 0) / 100);

        return (float) $pricing->priceFor($variant, $basePrice - (float) $variant->sale_price)['price'];
    }

    private function optionComparePrice($option): ?float
    {
        if ($option->compare_at_price !== null) {
            return (float) $option->compare_at_price;
        }

        return ($option->discount_percent ?? 0) > 0 && $option->sale_price !== null
            ? (float) $option->sale_price
            : null;
    }
}
