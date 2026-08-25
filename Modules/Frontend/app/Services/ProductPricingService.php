<?php

namespace Modules\Frontend\Services;

use Modules\Cart\Services\CampaignPricingService;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductVariant;

class ProductPricingService
{
    public function __construct(private readonly CampaignPricingService $campaignPricing)
    {
    }

    /**
     * Compute discount-aware price info for a product based on its active variants.
     *
     * The final "price" (price after discount) mirrors the cart's unit-price calc
     * (see Modules\Cart\Services\CartService::syncCart):
     *   1. start from the variant sale_price
     *   2. apply the variant discount_percent
     *   3. apply any live campaign pricing on top
     *
     * The cheapest variant defines the product price. The regular price (the
     * strikethrough reference) is the compare-at price if set, otherwise the
     * campaign original price, otherwise the sale price.
     *
     * @return array{price: float|null, regular_price: float|null, discount_percent: float, discount_amount: float, has_discount: bool}
     */
    public function priceInfo(Product $product): array
    {
        $variants = $product->relationLoaded('variants')
            ? $product->variants->where('status', 'active')
            : $product->variants()->where('status', 'active')->get();

        $best = null;

        foreach ($variants as $variant) {
            if (! $variant instanceof ProductVariant) {
                continue;
            }

            $salePrice   = (float) $variant->sale_price;
            $discountPct = (float) ($variant->discount_percent ?? 0);
            $discountPct = max(0, min(100, $discountPct));
            $compareAt   = $variant->compare_at_price !== null ? (float) $variant->compare_at_price : null;

            // 1) Apply the variant discount % (matches CartService::syncCart)
            $final = $salePrice * (1 - $discountPct / 100);

            // 2) Apply live campaign pricing on top (matches CartService::syncCart)
            $campaign = $this->campaignPricing->priceFor($variant, $final - $salePrice);
            $final    = (float) $campaign['price'];

            // Regular (strikethrough) price
            if ($campaign['campaign']) {
                $regular = (float) $campaign['original_price'];
            } elseif ($compareAt !== null) {
                $regular = $compareAt;
            } else {
                $regular = $salePrice;
            }

            $final   = round(max(0, $final), 2);
            $regular = round(max(0, $regular), 2);

            $priceInfo = [
                'price'            => $final,
                'regular_price'    => $regular,
                'discount_percent' => $regular > 0 ? (float) round((($regular - $final) / $regular) * 100, 0) : 0,
                'discount_amount'  => round(max(0, $regular - $final), 2),
                'has_discount'     => $final < $regular,
            ];

            if ($best === null || $priceInfo['price'] < $best['price']) {
                $best = $priceInfo;
            }
        }

        return $best ?? [
            'price'            => null,
            'regular_price'    => null,
            'discount_percent' => 0,
            'discount_amount'  => 0,
            'has_discount'     => false,
        ];
    }
}