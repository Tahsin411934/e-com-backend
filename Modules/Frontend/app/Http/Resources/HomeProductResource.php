<?php

namespace Modules\Frontend\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $clean = ltrim($path, '/');
        if (str_starts_with($clean, 'storage/')) {
            $clean = substr($clean, 8);
        }

        $url = $clean ? asset('storage/' . $clean) : null;

        // Add cache-busting parameter so browser always fetches latest image
        return $url ? $url . '?v=' . filemtime(storage_path('app/public/' . $clean)) : null;
    }

    public function toArray(Request $request): array
    {
        $mainImage = $this->images->firstWhere('is_main', true)
            ?? $this->images->first();

        // Get the lowest active variant price
        $minPrice = $this->variants
            ->where('status', 'active')
            ->min('sale_price');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'main_image' => $this->imageUrl($mainImage?->image_url),
            'price' => $minPrice ? (float) $minPrice : null,
            'product_type' => $this->product_type,
            'order_column' => $this->order_column ?? 0,
        ];
    }
}