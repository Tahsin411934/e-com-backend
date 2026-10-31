<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Modules\Cart\Models\Campaign;
use Modules\Cart\Services\CampaignService;
use Modules\Catalog\Models\Product;
use Modules\Store\Support\CurrentStore;
use Tests\TestCase;

class CampaignReviewTest extends TestCase
{
    use RefreshDatabase;

    private function product(string $slug): Product
    {
        CurrentStore::set(null);

        return Product::create([
            'name' => $slug, 'slug' => $slug, 'status' => 'active',
            'visibility' => 'public', 'product_type' => 'physical',
        ]);
    }

    private function campaign(): Campaign
    {
        CurrentStore::set(null);

        return Campaign::create([
            'name' => 'Review', 'slug' => 'review', 'status' => 'active',
            'is_active' => true, 'is_central_campaign' => true,
        ]);
    }

    public function test_product_variant_must_belong_to_selected_product(): void
    {
        $product = $this->product('first');
        $other = $this->product('other');
        $variant = $other->variants()->create(['name' => 'Default', 'sku' => 'REVIEW', 'sale_price' => 100]);
        $campaign = $this->campaign();
        $this->expectException(ValidationException::class);
        app(CampaignService::class)->addProduct($campaign, [
            'product_id' => $product->id, 'variant_id' => $variant->id,
            'discount_type' => 'percentage', 'discount_value' => 10,
        ]);
    }

    public function test_percentage_discount_cannot_exceed_one_hundred(): void
    {
        $product = $this->product('first');
        $campaign = $this->campaign();
        $this->expectException(ValidationException::class);
        app(CampaignService::class)->addProduct($campaign, [
            'product_id' => $product->id, 'discount_type' => 'percentage', 'discount_value' => 101,
        ]);
    }

    public function test_central_campaign_excludes_private_products_and_deleted_explicit_variants(): void
    {
        $product = $this->product('first');
        $variant = $product->variants()->create(['name' => 'Default', 'sku' => 'REVIEW', 'sale_price' => 100]);
        $product->variants()->create(['name' => 'Other', 'sku' => 'REVIEW-OTHER', 'sale_price' => 200]);
        $campaign = $this->campaign();
        $campaign->products()->create([
            'product_id' => $product->id, 'variant_id' => $variant->id,
            'discount_type' => 'percentage', 'discount_value' => 10,
        ]);
        $service = app(CampaignService::class);
        $this->assertCount(1, $service->liveCampaigns()->getData(true)['data'][0]['products']);
        $product->update(['visibility' => 'private']);
        $this->assertCount(0, $service->liveCampaigns()->getData(true)['data'][0]['products']);
        $product->update(['visibility' => 'public']);
        $variant->delete();
        $this->assertCount(0, $service->liveCampaigns()->getData(true)['data'][0]['products']);
    }
}
