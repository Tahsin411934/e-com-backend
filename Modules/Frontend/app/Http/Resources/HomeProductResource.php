<?php

namespace Modules\Frontend\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Frontend\Services\ProductPricingService;

class HomeProductResource extends JsonResource
{
    /**
     * Transform the product resource for home page display.
     */
    /**
     * Helper: normalize image URL to handle both old and new formats.
     */
    private function imageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $clean = ltrim($path, '/');
        if (str_starts_with($clean, 'storage/')) {
            $clean = substr($clean, 8);
        }

        $url = $clean ? asset('storage/'.$clean) : null;

        // Add cache-busting parameter so browser always fetches latest image
        return $url ? $url.'?v='.filemtime(storage_path('app/public/'.$clean)) : null;
    }

    public function toArray(Request $request): array
    {
        $mainImage = $this->images->firstWhere('is_main', true)
            ?? $this->images->first();

        // Discount-aware pricing (price after discount, regular price, discount %)
        $priceInfo = app(ProductPricingService::class)->priceInfo($this->resource);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'main_image' => $this->imageUrl($mainImage?->image_url),
            // Price after discount
            'price' => $priceInfo['price'],
            // Regular price (pre-discount, for strikethrough display)
            'regular_price' => $priceInfo['regular_price'],
            // Effective discount percentage
            'discount_percent' => $priceInfo['discount_percent'],
            // Discount amount = regular_price - price
            'discount_amount' => $priceInfo['discount_amount'],
            // Whether the product currently has a discount
            'has_discount' => $priceInfo['has_discount'],
            'product_type' => $this->product_type,
            'order_column' => $this->order_column ?? 0,
        ];
    }
}
