<?php

namespace Modules\Cart\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Cart\Models\Campaign;
use Modules\Frontend\Services\ProductPricingService;

class CampaignApiController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::live()->with(['products.product.images', 'products.product.variants'])->orderByDesc('is_featured')->orderByDesc('priority')->get();
        return response()->json(['success' => true, 'data' => $campaigns->map(fn (Campaign $campaign) => $this->serialize($campaign))->values()]);
    }

    public function show(string $slug)
    {
        $campaign = Campaign::live()->where('slug', $slug)->with(['products.product.images', 'products.product.variants'])->firstOrFail();
        return response()->json(['success' => true, 'data' => $this->serialize($campaign)]);
    }

    private function serialize(Campaign $campaign): array
    {
        $pricing = app(ProductPricingService::class);

        return [
            'id'           => $campaign->id,
            'name'         => $this->cleanText($campaign->name),
            'slug'         => $campaign->slug,
            'description'  => $this->cleanText($campaign->description),
            'banner_image' => $this->absoluteUrl($campaign->banner_image),
            'button_text'  => $campaign->button_text ?: 'Shop offer',
            'starts_at'    => $campaign->starts_at?->toIso8601String(),
            'ends_at'      => $campaign->ends_at?->toIso8601String(),
            'products'     => $campaign->products
                ->map(function ($entry) use ($pricing) {
                    $product = $entry->product;
                    $variant = $entry->variant ?? $product?->variants->firstWhere('status', 'active');
                    if (! $product || ! $variant) {
                        return null;
                    }

                    $info       = $pricing->priceInfoForVariant($variant);
                    $mainImage  = $product->images->firstWhere('is_main', true) ?? $product->images->first();

                    return [
                        'id'               => $product->id,
                        'name'             => $this->cleanText($product->name),
                        'slug'             => $product->slug,
                        'main_image'       => $this->absoluteUrl($mainImage?->image_url),
                        'price'            => $info['price'],
                        'regular_price'    => $info['regular_price'],
                        'original_price'   => $info['regular_price'],
                        'discount_percent' => $info['discount_percent'],
                        'discount_amount'  => $info['discount_amount'],
                        'has_discount'     => $info['has_discount'],
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

        return $clean ? asset('storage/' . $clean) : null;
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
