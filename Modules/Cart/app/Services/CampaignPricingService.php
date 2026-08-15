<?php

namespace Modules\Cart\Services;

use Modules\Cart\Models\CampaignProduct;
use Modules\Catalog\Models\ProductVariant;

class CampaignPricingService
{
    public function priceFor(ProductVariant $variant, float $optionAdjustment = 0): array
    {
        $originalPrice = (float) $variant->sale_price + $optionAdjustment;
        $offer = CampaignProduct::query()->with('campaign')->where('product_id', $variant->product_id)
            ->whereHas('campaign', fn ($query) => $query->live())
            ->where(fn ($query) => $query->whereNull('variant_id')->orWhere('variant_id', $variant->id))
            ->get()->sortByDesc(fn (CampaignProduct $item) => [
                $item->variant_id === $variant->id ? 1 : 0,
                $item->campaign->priority,
            ])->first();

        if (!$offer) return ['price' => $originalPrice, 'original_price' => $originalPrice, 'discount_amount' => 0, 'campaign' => null];

        $price = match ($offer->discount_type) {
            'percentage' => $originalPrice * max(0, 1 - ((float) $offer->discount_value / 100)),
            'fixed_amount' => max(0, $originalPrice - (float) $offer->discount_value),
            'fixed_price' => max(0, (float) $offer->discount_value + $optionAdjustment),
        };

        return ['price' => round($price, 4), 'original_price' => $originalPrice, 'discount_amount' => round($originalPrice - $price, 4), 'campaign' => $offer->campaign];
    }
}
