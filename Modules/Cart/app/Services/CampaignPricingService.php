<?php

namespace Modules\Cart\Services;

use Modules\Cart\Models\CampaignProduct;
use Modules\Catalog\Models\ProductVariant;
use Modules\Catalog\Models\VariantOption;

class CampaignPricingService
{
    /**
     * Resolve the live campaign offer for a variant (variant-specific rows win,
     * then highest campaign priority).
     */
    protected function offerFor(ProductVariant $variant): ?CampaignProduct
    {
        return CampaignProduct::query()->with('campaign')->where('product_id', $variant->product_id)
            ->whereHas('campaign', fn ($query) => $query->live())
            ->where(fn ($query) => $query->whereNull('variant_id')->orWhere('variant_id', $variant->id))
            ->get()->sortByDesc(fn (CampaignProduct $item) => [
                $item->variant_id === $variant->id ? 1 : 0,
                $item->campaign->priority,
            ])->first();
    }

    public function priceFor(ProductVariant $variant, float $optionAdjustment = 0): array
    {
        $originalPrice = (float) $variant->sale_price + $optionAdjustment;
        $offer = $this->offerFor($variant);

        if (! $offer) {
            return ['price' => $originalPrice, 'original_price' => $originalPrice, 'discount_amount' => 0, 'campaign' => null];
        }

        $price = match ($offer->discount_type) {
            'percentage' => $originalPrice * max(0, 1 - ((float) $offer->discount_value / 100)),
            'fixed_amount' => max(0, $originalPrice - (float) $offer->discount_value),
            'fixed_price' => max(0, (float) $offer->discount_value + $optionAdjustment),
        };

        $discountAmount = round($originalPrice - $price, 4);

        // A campaign only counts when it actually gives a discount greater
        // than 0. If the live campaign's discount is 0 (or its "price" is not
        // below the original price), treat it as "no campaign" so every caller
        // falls back to the variant / variant-option discount instead of the
        // campaign suppressing the variant's own discount.
        if ($discountAmount <= 0) {
            return ['price' => $originalPrice, 'original_price' => $originalPrice, 'discount_amount' => 0, 'campaign' => null];
        }

        return ['price' => round($price, 4), 'original_price' => $originalPrice, 'discount_amount' => $discountAmount, 'campaign' => $offer->campaign];
    }

    /**
     * Single source of truth for the final unit price of a variant (optionally
     * with a selected variant option). Shared by the cart add/sync, checkout
     * and storefront pricing APIs so discount handling can never diverge.
     *
     * Rule (applies to every API):
     *   1. If a LIVE campaign covers this product/variant AND its discount is
     *      greater than 0 -> the campaign price wins (discounts never stack).
     *   2. Otherwise -> the variant-option discount is applied, falling back to
     *      the parent variant discount when the option has none.
     *
     * @return array{unit_price: float, campaign: object|null, source: string}
     */
    public function finalPriceFor(ProductVariant $variant, ?VariantOption $option = null): array
    {
        $basePrice = $option !== null
            ? ($option->sale_price !== null
                ? (float) $option->sale_price
                : (float) $variant->sale_price + (float) ($option->price_adjustment ?? 0))
            : (float) $variant->sale_price;

        $campaign = $this->priceFor($variant, $basePrice - (float) $variant->sale_price);

        if ($campaign['campaign']) {
            return [
                'unit_price' => round(max(0, (float) $campaign['price']), 4),
                'campaign' => $campaign['campaign'],
                'source' => 'campaign',
            ];
        }

        // Option discount overrides parent; otherwise fall back to parent discount.
        $discountPercent = $option !== null
            ? (float) ($option->discount_percent ?? $variant->discount_percent ?? 0)
            : (float) ($variant->discount_percent ?? 0);

        return [
            'unit_price' => round(max(0, $basePrice * (1 - $discountPercent / 100)), 4),
            'campaign' => null,
            'source' => 'variant',
        ];
    }
}
