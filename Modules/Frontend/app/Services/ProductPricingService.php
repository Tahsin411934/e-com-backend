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
     * The cheapest variant defines the product price. The regular price is the
     * variant's base sale price (before any discount). The price after discount
     * is never higher than the regular price, so discount_percent can never be
     * negative.
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

            $priceInfo = $this->variantPriceInfo($variant);

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

    /**
     * Public helper for a single variant (used by campaign API etc).
     */
    public function priceInfoForVariant(ProductVariant $variant): array
    {
        return $this->variantPriceInfo($variant);
    }

    /**
     * Compute the final (price after discount), regular (base sale price) and
     * discount fields for one variant.
     *
     * Mapping (per requirement):
     *   regular_price = product_variants.sale_price   (base price before any discount)
     *   price         = sale_price * (1 - discount_percent/100)  (what customer pays, after discount)
     *
     * compare_at_price is NOT used as the regular price (it can be lower than the
     * sale price for some rows), so it can never cause a negative/backwards discount.
     */
    private function variantPriceInfo(ProductVariant $variant): array
    {
        $salePrice   = round(max(0, (float) $variant->sale_price), 0);
        $discountPct = max(0, min(100, (float) ($variant->discount_percent ?? 0)));

        // Campaign pricing takes precedence over the variant's own discount:
        // if the variant is covered by a live campaign, its price wins.
        // Otherwise the variant's own discount_percent is applied.
        $campaign = $this->campaignPricing->priceFor($variant, 0);

        if ($campaign['campaign']) {
            // Campaign price computed from the raw sale price (no product-discount stacking).
            $final = round(max(0, (float) $campaign['price']), 0);
        } else {
            // No campaign -> apply the variant's own discount.
            $final = round(max(0, $salePrice * (1 - $discountPct / 100)), 0);
        }

        // regular_price always = the base sale price (never null, never lower than price).
        $regular = $salePrice;

        $hasDiscount = $final < $regular && $regular > 0;
        $discountAmt = round($regular - $final, 0);

        return [
            'price'            => $final,
            'regular_price'    => $regular,
            'discount_percent' => $hasDiscount ? (float) round(($discountAmt / $regular) * 100, 0) : 0,
            'discount_amount'  => $hasDiscount ? $discountAmt : 0,
            'has_discount'     => $hasDiscount,
        ];
    }
}