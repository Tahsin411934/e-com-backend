<?php

namespace Modules\Cart\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Cart\Models\Campaign;
use Modules\Catalog\Models\Product;

class CampaignService
{
    public function __construct(private CampaignPricingService $campaignPricing) {}

    // ===== Admin (web) =====

    public function list(): JsonResponse
    {
        return ApiResponse::success(Campaign::withCount('products')->latest()->get());
    }

    public function store(array $data): JsonResponse
    {
        $data = $this->storeBanner($data);
        $data['slug'] = Str::slug($data['name']).'-'.Str::lower(Str::random(5));

        return ApiResponse::created(Campaign::create($data), 'Campaign created successfully.');
    }

    public function show(Campaign $campaign): JsonResponse
    {
        return ApiResponse::success($campaign->load('products.product'));
    }

    public function update(Campaign $campaign, array $data, bool $removeBanner = false): JsonResponse
    {
        $data = $this->replaceBanner($campaign, $data, $removeBanner);
        $campaign->update($data);

        return ApiResponse::success($campaign->fresh(), 'Campaign updated successfully.');
    }

    public function destroy(Campaign $campaign): JsonResponse
    {
        $campaign->delete();

        return ApiResponse::success(null, 'Campaign deleted successfully.');
    }

    public function toggleActive(Campaign $campaign): JsonResponse
    {
        $campaign->is_active = ! $campaign->is_active;
        $campaign->save();

        return ApiResponse::success($campaign->fresh(), 'Campaign status updated.');
    }

    public function searchProducts(string $q): JsonResponse
    {
        return ApiResponse::success(
            Product::where('status', 'active')
                ->where(fn ($query) => $query->where('name', 'like', "%{$q}%")->orWhere('slug', 'like', "%{$q}%"))
                ->with('variants:id,product_id,name,sku,sale_price')
                ->limit(20)
                ->get(['id', 'name', 'slug']),
            'Products retrieved successfully.'
        );
    }

    public function addProduct(Campaign $campaign, array $data): JsonResponse
    {
        $entry = $campaign->products()->firstOrNew(['product_id' => $data['product_id'], 'variant_id' => $data['variant_id'] ?? null]);
        if (! $entry->exists) {
            $entry->sort_order = ((int) $campaign->products()->max('sort_order')) + 1;
        }
        $entry->fill($data);
        $entry->save();

        return ApiResponse::success($entry->load('product', 'variant'), 'Product added to campaign.');
    }

    public function updateProduct(Campaign $campaign, int $campaignProduct, array $data): JsonResponse
    {
        $entry = $campaign->products()->whereKey($campaignProduct)->firstOrFail();
        $entry->update($data);

        return ApiResponse::success($entry->load('product', 'variant'), 'Campaign product updated.');
    }

    public function reorderProducts(Campaign $campaign, array $orderedIds): JsonResponse
    {
        foreach ($orderedIds as $i => $id) {
            $campaign->products()->whereKey($id)->update(['sort_order' => $i + 1]);
        }
        $campaign->products()->whereNotIn('id', $orderedIds)->update(['sort_order' => 999999]);

        return ApiResponse::success(null, 'Product order updated.');
    }

    public function removeProduct(Campaign $campaign, int $campaignProduct): JsonResponse
    {
        $entry = $campaign->products()->whereKey($campaignProduct)->firstOrFail();
        $entry->delete();

        return ApiResponse::success(null, 'Product removed from campaign.');
    }

    // ===== Public API (storefront) =====

    public function liveCampaigns(): JsonResponse
    {
        $campaigns = Campaign::live()
            ->with(['products.product.images', 'products.product.variants'])
            ->orderByDesc('is_featured')
            ->orderByDesc('priority')
            ->get();

        return ApiResponse::success(
            $campaigns->map(fn (Campaign $campaign) => $this->serialize($campaign))->values(),
            'Campaigns retrieved successfully.'
        );
    }

    public function liveCampaignBySlug(string $slug): JsonResponse
    {
        $campaign = Campaign::live()
            ->where('slug', $slug)
            ->with(['products.product.images', 'products.product.variants'])
            ->firstOrFail();

        return ApiResponse::success($this->serialize($campaign), 'Campaign retrieved successfully.');
    }

    // ===== Helpers =====

    private function storeBanner(array $data): array
    {
        if (! empty($data['banner_image_file'])) {
            $data['banner_image'] = $data['banner_image_file']->store('campaigns/banners', 'public');
        }
        unset($data['banner_image_file']);

        return $data;
    }

    private function replaceBanner(Campaign $campaign, array $data, bool $removeBanner): array
    {
        if ($removeBanner) {
            if ($campaign->banner_image) {
                Storage::disk('public')->delete($campaign->banner_image);
            }
            $data['banner_image'] = null;
        } elseif (! empty($data['banner_image_file'])) {
            if ($campaign->banner_image) {
                Storage::disk('public')->delete($campaign->banner_image);
            }
            $data['banner_image'] = $data['banner_image_file']->store('campaigns/banners', 'public');
        }
        unset($data['banner_image_file'], $data['remove_banner']);

        return $data;
    }

    private function serialize(Campaign $campaign): array
    {
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
                ->map(function ($entry) {
                    $product = $entry->product;
                    $variant = $entry->variant ?? $product?->variants->firstWhere('status', 'active');
                    if (! $product || ! $variant) {
                        return null;
                    }

                    // Campaign discount is the ONLY discount here.
                    // regular_price = sale price; price = after campaign discount.
                    $campaignPrice = $this->campaignPricing->priceFor($variant, 0);
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
