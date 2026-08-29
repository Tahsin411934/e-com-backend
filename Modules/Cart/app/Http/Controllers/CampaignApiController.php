<?php

namespace Modules\Cart\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Modules\Cart\Models\Campaign;
use Modules\Cart\Services\CampaignPricingService;

class CampaignApiController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::live()->with(['products.product.images', 'products.product.variants'])->orderByDesc('is_featured')->orderByDesc('priority')->get();

        return ApiResponse::success($campaigns->map(fn (Campaign $campaign) => $this->serialize($campaign))->values(), 'Campaigns retrieved successfully.');
    }

    public function show(string $slug)
    {
        $campaign = Campaign::live()->where('slug', $slug)->with(['products.product.images', 'products.product.variants'])->firstOrFail();

        return ApiResponse::success($this->serialize($campaign), 'Campaign retrieved successfully.');
    }

    private function serialize(Campaign $campaign): array
    {
        $campaignPricing = app(CampaignPricingService::class);

        return [
            'id' => $campaign->id,
            'name' => $this->cleanText($campaign->name),
            'slug' => $campaign->slug,
            'description' => $this->cleanText($campaign->description),
            'banner_image' => $this->absoluteUrl($campaign->banner_image),
            'button_text' => $campaign->button_text ?: 'Shop offer',
            'starts_at' => $campaign->starts_at?->toIso8601String(),
            'ends_at' => $campaign->ends_at?->toIso8601String(),
            'products' => $campaign->products
                ->map(function ($entry) use ($campaignPricing) {
                    $product = $entry->product;
                    $variant = $entry->variant ?? $product?->variants->firstWhere('status', 'active');
                    if (! $product || ! $variant) {
                        return null;
                    }

                    // Campaign discount is the ONLY discount here.
                    // regular_price = sale price; price = after campaign discount.
                    $campaignPrice = $campaignPricing->priceFor($variant, 0);
                    $price = round(max(0, (float) $campaignPrice['price']), 0);
                    $regular = round(max(0, (float) $campaignPrice['original_price']), 0);
                    $hasDiscount = $price < $regular && $regular > 0;
                    $discountAmt = round($regular - $price, 0);
                    $mainImage = $product->images->firstWhere('is_main', true) ?? $product->images->first();

                    return [
                        'id' => $product->id,
                        'name' => $this->cleanText($product->name),
                        'slug' => $product->slug,
                        'main_image' => $this->absoluteUrl($mainImage?->image_url),
                        'price' => $price,
                        'regular_price' => $regular,
                        'original_price' => $regular,
                        'discount_percent' => $hasDiscount ? (float) round(($discountAmt / $regular) * 100, 0) : 0,
                        'discount_amount' => $hasDiscount ? $discountAmt : 0,
                        'has_discount' => $hasDiscount,
                    ];
                })
                ->filter()
                ->values(),
        ];
    }

    /**
     * Normalize stored image paths -> absolute URL.
     * Handles full URLs already stored in the DB (no double storage/ prefix).
     */
    private function absoluteUrl(?string $path): ?string
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

        return $clean ? asset('storage/'.$clean) : null;
    }

    /**
     * Remove the repeated HTML-entity escaping that exists in some DB rows
     * (e.g. "&amp;amp;amp;" -> "&").
     */
    private function cleanText(?string $text): ?string
    {
        if ($text === null || $text === '') {
            return $text;
        }

        $decoded = $text;
        while (true) {
            $next = html_entity_decode($decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($next === $decoded) {
                break;
            }
            $decoded = $next;
        }

        return $decoded;
    }
}
