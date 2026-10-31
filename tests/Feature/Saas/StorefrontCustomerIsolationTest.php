<?php

namespace Tests\Feature\Saas;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Cart\Models\Cart;
use Modules\Cart\Models\Wishlist;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductVariant;
use Modules\Catalog\Models\VariantOption;
use Modules\Frontend\Models\Setting;
use Modules\Identity\Models\Customer;
use Modules\Identity\Models\User;
use Modules\Order\Models\Order;
use Modules\Reviews\Models\ProductReview;
use Modules\Store\Models\Store;
use Modules\Store\Support\CurrentStore;
use Modules\Store\Support\StoreDomainResolver;
use Tests\TestCase;

class StorefrontCustomerIsolationTest extends TestCase
{
    use RefreshDatabase;

    private Store $alpha;
    private Store $beta;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        config(['storefront.domain_suffix' => 'example.test', 'storefront.fallback_store' => null]);
        $this->alpha = $this->store('alpha');
        $this->beta = $this->store('beta');
        $this->customer = Customer::create([
            'store_id' => $this->alpha->id, 'first_name' => 'Alpha', 'last_name' => 'Customer',
            'email' => 'customer@example.test', 'password_hash' => Hash::make('password123'), 'status' => 'active',
        ]);
        $this->withHeader('X-Store-Host', 'alpha.example.test');
    }

    protected function tearDown(): void
    {
        CurrentStore::reset();
        StoreDomainResolver::reset();
        parent::tearDown();
    }

    private function store(string $slug): Store
    {
        $owner = User::create(['public_id' => (string) Str::uuid(), 'first_name' => 'Owner', 'last_name' => $slug,
            'email' => $slug.'@example.test', 'password_hash' => Hash::make('password123'), 'status' => 'active']);

        return Store::create(['owner_id' => $owner->id, 'name' => $slug, 'slug' => $slug,
            'email' => $slug.'-store@example.test', 'phone' => '12345', 'status' => 'active', 'currency_code' => 'BDT']);
    }

    private function authenticate(?Customer $customer = null): void
    {
        $this->withToken(($customer ?? $this->customer)->createToken('test')->plainTextToken);
    }

    private function product(Store $store, string $slug): ProductVariant
    {
        $product = Product::create(['store_id' => $store->id, 'name' => $slug, 'slug' => $slug,
            'status' => 'active', 'visibility' => 'public', 'delivery_charge' => 50]);

        return ProductVariant::create(['product_id' => $product->id, 'name' => 'Default', 'sku' => $slug,
            'sale_price' => 100, 'discount_percent' => 10, 'status' => 'active', 'track_inventory' => false]);
    }

    private function item(ProductVariant $variant): array
    {
        return ['product_id' => $variant->product_id, 'variant_id' => $variant->id, 'quantity' => 2];
    }

    private function delivery(): array
    {
        return ['customer_name' => 'Guest', 'delivery_address' => '10 Test Road', 'delivery_city' => 'Dhaka', 'delivery_phone' => '01700000000'];
    }

    private function order(Store $store, array $attributes = []): Order
    {
        return Order::create($attributes + ['store_id' => $store->id, 'order_number' => (string) Str::uuid(),
            'source' => 'web', 'status' => 'pending', 'payment_status' => 'unpaid', 'fulfillment_status' => 'unfulfilled',
            'currency_code' => 'BDT', 'subtotal' => 100, 'discount_total' => 0, 'tax_total' => 0, 'shipping_total' => 0, 'grand_total' => 100, 'placed_at' => now()]);
    }

    public function test_customer_token_is_rejected_on_another_store_and_legacy_api(): void
    {
        $this->authenticate();
        $this->withHeader('X-Store-Host', 'beta.example.test')->getJson('/api/v1/storefront/auth/me')->assertForbidden();
        $this->getJson('/api/v1/wishlists')->assertForbidden();
        $this->postJson('/api/v1/carts/sync', ['items' => []])->assertForbidden();
        $this->getJson('/api/v1/user')->assertForbidden();
    }

    public function test_admin_tokens_and_inactive_customers_cannot_use_customer_endpoints(): void
    {
        $this->withToken($this->alpha->owner->createToken('test')->plainTextToken)->getJson('/api/v1/storefront/auth/me')->assertForbidden();
        $this->customer->update(['status' => 'blocked']);
        $this->authenticate();
        $this->getJson('/api/v1/storefront/auth/me')->assertForbidden();
    }

    public function test_orders_use_customer_ownership_even_when_user_ids_collide(): void
    {
        $own = $this->order($this->alpha, ['customer_id' => $this->customer->id]);
        $legacy = $this->order($this->alpha, ['user_id' => $this->customer->id]);
        $foreign = $this->order($this->beta, ['customer_id' => $this->customer->id]);
        $guest = $this->order($this->alpha);
        $this->authenticate();
        $this->getJson('/api/v1/storefront/orders?store_id='.$this->beta->id)->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $own->id);
        foreach ([$legacy, $foreign, $guest] as $order) {
            $this->getJson('/api/v1/storefront/orders/'.$order->id)->assertNotFound();
        }
        $this->getJson('/api/v1/storefront/orders/'.$own->id)->assertOk();
    }

    public function test_cart_sync_rejects_foreign_products_and_mismatched_variants_without_writes(): void
    {
        $own = $this->product($this->alpha, 'own');
        $foreign = $this->product($this->beta, 'foreign');
        $this->authenticate();
        $this->postJson('/api/v1/storefront/carts/sync', ['items' => [$this->item($own), $this->item($foreign)]])->assertNotFound();
        $this->assertDatabaseCount('carts', 0);
        $this->postJson('/api/v1/storefront/carts/sync', ['items' => [['product_id' => $own->product_id, 'variant_id' => $foreign->id, 'quantity' => 1]]])->assertNotFound();
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_sync_replaces_only_own_customer_cart_and_never_trusts_prices_or_store_ids(): void
    {
        $own = $this->product($this->alpha, 'own');
        $legacy = Cart::create(['user_id' => $this->customer->id, 'store_id' => $this->alpha->id, 'status' => 'active']);
        $this->authenticate();
        $this->postJson('/api/v1/storefront/carts/sync', ['store_id' => $this->beta->id, 'customer_id' => 999,
            'items' => [$this->item($own) + ['unit_price' => 1]]])->assertOk()->assertJsonPath('data.customer_id', $this->customer->id)->assertJsonPath('data.store_id', $this->alpha->id);
        $this->assertDatabaseHas('cart_items', ['variant_id' => $own->id, 'unit_price' => 90, 'quantity' => 2]);
        $this->assertDatabaseHas('carts', ['id' => $legacy->id, 'user_id' => $this->customer->id, 'customer_id' => null]);
        $this->postJson('/api/v1/storefront/carts/sync', ['items' => []])->assertOk()->assertJsonCount(0, 'data.items');
    }

    public function test_guest_checkout_rejects_mixed_stores_and_foreign_options_atomically(): void
    {
        $own = $this->product($this->alpha, 'own');
        $foreign = $this->product($this->beta, 'foreign');
        $option = VariantOption::create(['product_variant_id' => $foreign->id, 'color_name' => 'Red', 'sku' => 'foreign-red', 'status' => 'active']);
        $this->postJson('/api/v1/storefront/guest-checkout', $this->delivery() + ['items' => [$this->item($own), $this->item($foreign)]])->assertNotFound();
        $this->postJson('/api/v1/storefront/guest-checkout', $this->delivery() + ['items' => [$this->item($own) + ['variant_option_id' => $option->id]]])->assertNotFound();
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('deliveries', 0);
    }

    public function test_guest_checkout_uses_store_and_server_prices(): void
    {
        $own = $this->product($this->alpha, 'own');
        $this->postJson('/api/v1/storefront/guest-checkout', $this->delivery() + ['store_id' => $this->beta->id,
            'items' => [$this->item($own) + ['unit_price' => 1]]])->assertOk()->assertJsonPath('order_count', 1)->assertJsonPath('split_by_store', false);
        $this->assertDatabaseHas('orders', ['store_id' => $this->alpha->id, 'customer_id' => null, 'user_id' => null, 'subtotal' => 180, 'grand_total' => 230]);
    }

    public function test_authenticated_checkout_cannot_consume_legacy_or_foreign_carts(): void
    {
        Cart::create(['user_id' => $this->customer->id, 'store_id' => $this->alpha->id, 'status' => 'active']);
        Cart::create(['customer_id' => $this->customer->id, 'store_id' => $this->beta->id, 'status' => 'active']);
        $this->authenticate();
        $this->postJson('/api/v1/storefront/checkout', $this->delivery())->assertNotFound();
        $own = $this->product($this->alpha, 'own');
        $this->postJson('/api/v1/storefront/carts/sync', ['items' => [$this->item($own)]])->assertOk();
        $this->postJson('/api/v1/storefront/checkout', $this->delivery())->assertOk();
        $this->assertDatabaseHas('orders', ['customer_id' => $this->customer->id, 'user_id' => null, 'store_id' => $this->alpha->id]);
    }

    public function test_wishlist_is_customer_owned_and_rejects_foreign_products(): void
    {
        $own = $this->product($this->alpha, 'own');
        $foreign = $this->product($this->beta, 'foreign');
        Wishlist::create(['user_id' => $this->customer->id, 'product_id' => $own->product_id]);
        $this->authenticate();
        $this->getJson('/api/v1/storefront/wishlists')->assertOk()->assertJsonCount(0, 'wishlist');
        $this->postJson('/api/v1/storefront/wishlists/toggle', ['product_id' => $foreign->product_id])->assertNotFound();
        $this->postJson('/api/v1/storefront/wishlists/toggle', ['product_id' => $own->product_id])->assertOk();
        $this->getJson('/api/v1/storefront/wishlists')->assertOk()->assertJsonCount(1, 'wishlist');
        $this->assertDatabaseHas('wishlists', ['customer_id' => $this->customer->id, 'user_id' => null, 'product_id' => $own->product_id]);
    }

    public function test_review_submission_uses_customer_ownership_and_requires_approval(): void
    {
        $own = $this->product($this->alpha, 'own');
        $foreign = $this->product($this->beta, 'foreign');
        $review = ['rating' => 5, 'title' => 'Great', 'body' => 'Review text', 'status' => 'approved', 'user_id' => 999];
        $this->authenticate();
        $this->postJson('/api/v1/storefront/products/'.$foreign->product_id.'/reviews', $review)->assertNotFound();
        $this->postJson('/api/v1/storefront/products/'.$own->product_id.'/reviews', $review)->assertCreated();
        $this->assertDatabaseHas('product_reviews', ['product_id' => $own->product_id, 'customer_id' => $this->customer->id, 'user_id' => null, 'status' => 'pending']);
        $this->getJson('/api/v1/storefront/products/'.$own->product_id.'/reviews')->assertOk()->assertJsonCount(0, 'data');
        $this->postJson('/api/v1/storefront/products/'.$own->product_id.'/reviews', $review)->assertUnprocessable();
        ProductReview::where('product_id', $own->product_id)->update(['status' => 'approved']);
        $this->getJson('/api/v1/storefront/products/'.$own->product_id.'/reviews')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_settings_never_inherit_platform_or_other_store_data(): void
    {
        foreach ([null, $this->beta->id] as $storeId) {
            foreach (['email', 'site_logo', 'gtm_header_code', 'facebook_url'] as $key) {
                Setting::create(['store_id' => $storeId, 'group' => 'general', 'key' => $key, 'value' => 'foreign-value', 'type' => 'text', 'label' => $key]);
            }
        }
        Setting::create(['store_id' => $this->alpha->id, 'group' => 'general', 'key' => 'site_name', 'value' => 'Custom name', 'type' => 'text', 'label' => 'Name']);
        $this->getJson('/api/v1/storefront/settings')->assertOk()->assertJsonPath('data.site_name', 'Custom name')
            ->assertJsonPath('data.email', $this->alpha->email)->assertDontSee('foreign-value');
    }

    public function test_reset_tokens_cannot_reset_same_email_in_another_store(): void
    {
        $other = Customer::create(['store_id' => $this->beta->id, 'first_name' => 'Other', 'last_name' => 'Customer',
            'email' => $this->customer->email, 'password_hash' => Hash::make('password123'), 'status' => 'active']);
        DB::table('customer_password_reset_tokens')->insert(['customer_id' => $this->customer->id, 'token' => Hash::make('reset-token'), 'created_at' => now()]);
        $this->withHeader('X-Store-Host', 'beta.example.test')->postJson('/api/v1/storefront/auth/reset-password', [
            'email' => $other->email, 'token' => 'reset-token', 'password' => 'changed123', 'password_confirmation' => 'changed123',
        ])->assertUnprocessable();
        $this->assertTrue(Hash::check('password123', $other->fresh()->password_hash));
    }

    public function test_unregistered_and_nested_hosts_do_not_resolve_to_a_store(): void
    {
        $this->withHeader('X-Store-Host', 'alpha.extra.example.test')->getJson('/api/v1/storefront/settings')->assertNotFound();
        $this->withHeader('X-Store-Host', 'missing.example.test')->getJson('/api/v1/storefront/settings')->assertNotFound();
        config(['storefront.fallback_store' => 'alpha']);
        $this->app->instance('env', 'production');
        $this->withHeader('X-Store-Host', 'unregistered.example.org')->getJson('/api/v1/storefront/settings')->assertNotFound();
    }
}
