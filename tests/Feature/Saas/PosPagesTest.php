<?php

namespace Tests\Feature\Saas;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Cart\Models\Campaign;
use Modules\Cart\Models\CampaignProduct;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductVariant;
use Modules\Catalog\Models\VariantOption;
use Modules\Identity\Models\Role;
use Modules\Identity\Models\User;
use Modules\Pos\Models\PosRegister;
use Modules\Pos\Models\PosSaleItem;
use Modules\Pos\Models\PosShift;
use Modules\Store\Models\Store;
use Tests\TestCase;

class PosPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_pages_render_without_server_errors(): void
    {
        $role = Role::firstOrCreate(['name' => 'Super Admin'], ['description' => 'Full access']);

        $admin = User::create([
            'public_id' => (string) Str::uuid(),
            'first_name' => 'Pos',
            'last_name' => 'Admin',
            'email' => 'pos-admin@example.com',
            'password_hash' => Hash::make('secret1234'),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $admin->roles()->attach($role->id);

        foreach (['/pos-registers', '/pos-shifts', '/pos-sales'] as $url) {
            $response = $this->actingAs($admin)->get($url);

            $response->assertOk();
        }

        // The POS sell page must render a "Complete Sale" button (both in the
        // payment panel and the always-visible cart footer).
        $sell = $this->actingAs($admin)->get('/pos-sell');

        $sell->assertOk();
        $sell->assertSee('Complete Sale');
        $sell->assertSee('processSaleBtnLeft');
        $sell->assertSee('processSaleBtn');
    }

    public function test_pos_search_returns_clean_product_names(): void
    {
        $role = Role::firstOrCreate(['name' => 'Super Admin'], ['description' => 'Full access']);

        $admin = User::create([
            'public_id' => (string) Str::uuid(),
            'first_name' => 'Pos',
            'last_name' => 'Admin',
            'email' => 'pos-search-admin@example.com',
            'password_hash' => Hash::make('secret1234'),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $admin->roles()->attach($role->id);

        // Simulate a name that got saved through escaped form values several
        // times ("&" became "&amp;amp;amp;"...).
        Product::create([
            'name' => 'P9 Wireless Bluetooth Headset with ANC &amp;amp;amp;amp;amp; Noise Cancelling Mic',
            'slug' => 'p9-wireless-bt-headset',
            'product_type' => 'physical',
            'status' => 'active',
            'visibility' => 'public',
        ]);

        $response = $this->actingAs($admin)->getJson('/pos-sell/search-products?term=P9');

        $response->assertOk();

        $names = collect($response->json('data'))->pluck('name');

        $this->assertContains('P9 Wireless Bluetooth Headset with ANC & Noise Cancelling Mic', $names);
        $this->assertStringNotContainsString('&amp;', $names->join(' '));
    }

    public function test_pos_search_returns_variants_colors_and_discounted_price(): void
    {
        $this->actingAs($this->createAdmin());

        $product = Product::create([
            'name' => 'Wireless Headset',
            'slug' => 'wireless-headset-pos',
            'product_type' => 'physical',
            'status' => 'active',
            'visibility' => 'public',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Bluetooth 5.3',
            'sku' => 'WH-BT53',
            'sale_price' => 800,
            'discount_percent' => 10,
            'compare_at_price' => 900,
            'status' => 'active',
        ]);

        VariantOption::create([
            'product_variant_id' => $variant->id,
            'color_name' => 'Black',
            'color_code' => '#000000',
            'sku' => 'WH-BT53-BK',
            'sale_price' => 850,
            'discount_percent' => 10,
            'status' => 'active',
        ]);

        VariantOption::create([
            'product_variant_id' => $variant->id,
            'color_name' => 'Blue',
            'color_code' => '#0000ff',
            'sku' => 'WH-BT53-BL',
            'sale_price' => 850,
            'price_adjustment' => 20,
            'status' => 'active',
        ]);

        $response = $this->getJson('/pos-sell/search-products?term=Wireless');

        $response->assertOk();

        $data = collect($response->json('data'))->firstWhere('name', 'Wireless Headset');

        $this->assertNotNull($data, 'Product not found in search results');
        $this->assertCount(1, $data['variants']);
        $this->assertCount(2, $data['variants'][0]['options']);
        $this->assertTrue($data['has_discount']);

        $black = collect($data['variants'][0]['options'])->firstWhere('color_name', 'Black');
        // 850 - 10% = 765
        $this->assertEquals(765.0, (float) $black['price']);
        $this->assertEquals(850.0, (float) $black['original_price']);
        $this->assertEquals(10.0, (float) $black['discount_percent']);
        $this->assertEquals(765.0, (float) $black['price']);
    }

    public function test_pos_sale_stores_variant_option_and_discount(): void
    {
        $admin = $this->createAdmin();

        $product = Product::create([
            'name' => 'Headset Pro',
            'slug' => 'headset-pro-pos',
            'product_type' => 'physical',
            'status' => 'active',
            'visibility' => 'public',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Standard',
            'sku' => 'HP-STD',
            'sale_price' => 700,
            'discount_percent' => 15,
            'status' => 'active',
        ]);

        $option = VariantOption::create([
            'product_variant_id' => $variant->id,
            'color_name' => 'Red',
            'sku' => 'HP-STD-RD',
            'sale_price' => 700,
            'discount_percent' => 15,
            'status' => 'active',
        ]);

        $store = Store::create([
            'name' => 'Pos Test Store',
            'slug' => 'pos-test-store',
            'status' => 'active',
            'currency_code' => 'USD',
            'timezone' => 'UTC',
        ]);

        $register = PosRegister::create([
            'store_id' => $store->id,
            'name' => 'Test Register',
            'code' => 'REG-T',
            'type' => 'cash',
            'status' => 'active',
        ]);

        $shift = PosShift::create([
            'register_id' => $register->id,
            'user_id' => $admin->id,
            'opened_at' => now(),
            'opening_balance' => 0,
            'status' => 'open',
        ]);

        $finalPrice = round(700 * 0.85, 2); // 595
        $perUnitDiscount = round(700 - 595, 2); // 105

        $response = $this->actingAs($admin)->postJson('/pos-sell/process', [
            'register_id' => $register->id,
            'shift_id' => $shift->id,
            'items' => [[
                'product_id' => $product->id,
                'variant_id' => $variant->id,
                'option_id' => $option->id,
                'product_name' => $product->name,
                'variant_name' => $variant->name,
                'color_name' => $option->color_name,
                'sku' => $option->sku,
                'unit_price' => $finalPrice,
                'original_price' => 700,
                'discount_amount' => $perUnitDiscount,
                'quantity' => 2,
                'subtotal' => round($finalPrice * 2, 2),
                'total' => round($finalPrice * 2, 2),
            ]],
            'subtotal' => round($finalPrice * 2, 2),
            'total' => round($finalPrice * 2, 2),
            'payment_status' => 'paid',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success');

        $saleItem = PosSaleItem::where('variant_id', $variant->id)->first();

        $this->assertNotNull($saleItem);
        $this->assertSame($option->id, (int) $saleItem->variant_option_id);
        $this->assertEquals($perUnitDiscount * 2, (float) $saleItem->discount_amount); // line total (per unit x qty 2)
        $this->assertEquals(595.0, (float) $saleItem->unit_price);
    }

    public function test_pos_campaign_discount_wins_and_is_stored_on_sale_item(): void
    {
        $admin = $this->createAdmin();

        $product = Product::create([
            'name' => 'Campaign Headset',
            'slug' => 'campaign-headset-pos',
            'product_type' => 'physical',
            'status' => 'active',
            'visibility' => 'public',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Standard',
            'sku' => 'CH-STD',
            'sale_price' => 700,
            'discount_percent' => 15, // 15% variant discount — must LOSE to the campaign
            'status' => 'active',
        ]);

        $option = VariantOption::create([
            'product_variant_id' => $variant->id,
            'color_name' => 'Red',
            'sku' => 'CH-STD-RD',
            'sale_price' => 700,
            'status' => 'active',
        ]);

        $campaign = Campaign::create([
            'name' => 'Summer Sale',
            'slug' => 'summer-sale-pos',
            'status' => 'active',
            'is_active' => true,
            'priority' => 50,
        ]);

        CampaignProduct::create([
            'campaign_id' => $campaign->id,
            'product_id' => $product->id,
            'discount_type' => 'percentage',
            'discount_value' => 20, // 20% campaign discount
        ]);

        $store = Store::create([
            'name' => 'Pos Campaign Store',
            'slug' => 'pos-campaign-store',
            'status' => 'active',
            'currency_code' => 'USD',
            'timezone' => 'UTC',
        ]);

        $register = PosRegister::create([
            'store_id' => $store->id,
            'name' => 'Test Register',
            'code' => 'REG-C',
            'type' => 'cash',
            'status' => 'active',
        ]);

        $shift = PosShift::create([
            'register_id' => $register->id,
            'user_id' => $admin->id,
            'opened_at' => now(),
            'opening_balance' => 0,
            'status' => 'open',
        ]);

        // 1. Search payload must surface the campaign with source + bracket price.
        $search = $this->actingAs($admin)->getJson('/pos-sell/search-products?term=Campaign');

        $search->assertOk();

        $data = collect($search->json('data'))->firstWhere('name', 'Campaign Headset');

        $this->assertNotNull($data, 'Campaign product not found in search results');
        $this->assertSame('Summer Sale', $data['campaign']['name']);
        $this->assertTrue($data['has_discount'], 'Campaign discount should flag has_discount');

        $variantPayload = $data['variants'][0];
        $optionPayload = collect($variantPayload['options'])->firstWhere('color_name', 'Red');

        // 20% campaign wins over the 15% variant/option discount.
        $this->assertSame('campaign', $optionPayload['campaign']['source']);
        $this->assertSame('Summer Sale', $optionPayload['campaign']['name']);
        $this->assertEquals(560.0, (float) $optionPayload['price']); // 700 * 0.80
        $this->assertEquals(700.0, (float) $optionPayload['original_price']);

        // 2. processSale must persist campaign_id + discount_source server-side.
        $response = $this->actingAs($admin)->postJson('/pos-sell/process', [
            'register_id' => $register->id,
            'shift_id' => $shift->id,
            'items' => [[
                'product_id' => $product->id,
                'variant_id' => $variant->id,
                'option_id' => $option->id,
                'product_name' => $product->name,
                'variant_name' => $variant->name,
                'color_name' => $option->color_name,
                'sku' => $option->sku,
                'unit_price' => 560,
                'original_price' => 700,
                'discount_amount' => 140,
                'quantity' => 2,
                'subtotal' => 1120,
                'total' => 1120,
            ]],
            'subtotal' => 1120,
            'total' => 1120,
            'payment_status' => 'paid',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success');

        $saleItem = PosSaleItem::where('variant_id', $variant->id)->first();

        $this->assertNotNull($saleItem);
        $this->assertSame($campaign->id, (int) $saleItem->campaign_id);
        $this->assertSame('campaign', $saleItem->discount_source);
        $this->assertEquals(560.0, (float) $saleItem->unit_price);
        $this->assertEquals(280.0, (float) $saleItem->discount_amount); // 140 per unit x 2
        $this->assertEquals(1120.0, (float) $saleItem->total);
    }

    private function createAdmin(): User
    {
        $role = Role::firstOrCreate(['name' => 'Super Admin'], ['description' => 'Full access']);

        $admin = User::create([
            'public_id' => (string) Str::uuid(),
            'first_name' => 'Pos',
            'last_name' => 'Admin',
            'email' => Str::lower(Str::random(8)).'@example.com',
            'password_hash' => Hash::make('secret1234'),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $admin->roles()->attach($role->id);

        return $admin;
    }
}
