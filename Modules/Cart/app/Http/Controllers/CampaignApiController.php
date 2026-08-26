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
        return response()->json(['success' => true, 'data' => $campaigns->map(fn (Campaign $campaign) => $this->serialize($campaign))]);
    }

    public function show(string $slug)
    {
        $campaign = Campaign::live()->where('slug', $slug)->with(['products.product.images', 'products.product.variants'])->firstOrFail();
        return response()->json(['success' => true, 'data' => $this->serialize($campaign)]);
    }

    private function serialize(Campaign $campaign): array
    {
        $pricing = app(ProductPricingService::class);
        return ['id' => $campaign->id, 'name' => $campaign->name, 'slug' => $campaign->slug, 'description' => $campaign->description, 'banner_image' => $campaign->banner_image ? asset('storage/' . ltrim($campaign->banner_image, '/')) : null, 'button_text' => $campaign->button_text ?: 'Shop offer', 'ends_at' => $campaign->ends_at?->toIso8601String(), 'products' => $campaign->products->map(function ($entry) use ($pricing) {
            $product = $entry->product; $variant = $entry->variant ?? $product?->variants->firstWhere('status', 'active'); if (!$product || !$variant) return null;
            $info = $pricing->priceInfoForVariant($variant);
            return ['id' => $product->id, 'name' => $product->name, 'slug' => $product->slug, 'main_image' => optional($product->images->firstWhere('is_main', true) ?? $product->images->first())->image_url ? asset('storage/' . ltrim(optional($product->images->firstWhere('is_main', true) ?? $product->images->first())->image_url, '/')) : null, 'price' => $info['price'], 'regular_price' => $info['regular_price'], 'original_price' => $info['regular_price'], 'discount_percent' => $info['discount_percent'], 'discount_amount' => $info['discount_amount'], 'has_discount' => $info['has_discount']];
        })->filter()->values()];
    }
}
