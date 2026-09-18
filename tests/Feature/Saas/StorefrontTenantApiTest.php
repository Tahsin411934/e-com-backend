<?php

namespace Tests\Feature\Saas;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductRequest;
use Modules\Frontend\Models\Banner;
use Modules\Frontend\Models\Setting;
use Modules\Identity\Models\Role;
use Modules\Identity\Models\User;
use Modules\Store\Models\Store;
use Modules\Store\Models\StoreDomain;
use Modules\Store\Support\CurrentStore;
use Modules\Store\Support\StoreDomainResolver;
use Tests\TestCase;

class StorefrontTenantApiTest extends TestCase
{
    use RefreshDatabase;

    private Store $storeA;

    private Store $storeB;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['Super Admin', 'Store Owner', 'Customer'] as $role) {
            Role::firstOrCreate(['name' => $role], ['description' => $role]);
        }

        $this->storeA = $this->createStore('owner-a@example.com', 'Store A', 'store-a');
        $this->storeB = $this->createStore('owner-b@example.com', 'Store B', 'store-b');

        $this->createProduct($this->storeA, 'store-a-only');
        $this->createProduct($this->storeB, 'store-b-only');
        $this->createProduct(null, 'global-product');
    }

    protected function tearDown(): void
    {
        CurrentStore::reset();
        StoreDomainResolver::reset();

        parent::tearDown();
    }

    private function servedBy(string $host): static
    {
        return $this->withHeaders(['X-Store-Host' => $host]);
    }

    private function createStore(string $ownerEmail, string $name, string $slug): Store
    {
        $owner = User::create([
            'public_id' => (string) Str::uuid(),
            'first_name' => 'Test',
            'last_name' => 'Owner',
            'email' => $ownerEmail,
            'password_hash' => Hash::make('secret1234'),
            'status' => 'active',
        ]);
        $owner->roles()->attach(Role::where('name', 'Store Owner')->first()->id);

        $store = Store::create([
            'owner_id' => $owner->id,
            'name' => $name,
            'slug' => $slug,
            'status' => 'active',
            'currency_code' => 'USD',
            'timezone' => 'UTC',
        ]);

        StoreDomain::create([
            'store_id' => $store->id,
            'domain' => $slug.'.shopio.test',
            'type' => 'subdomain',
            'is_primary' => true,
            'ssl_status' => 'active',
            'verified_at' => now(),
        ]);

        return $store;
    }

    private function createProduct(?Store $store, string $slug): Product
    {
        return Product::create([
            'store_id' => $store?->id,
            'name' => str_replace('-', ' ', $slug),
            'slug' => $slug,
            'product_type' => 'physical',
            'status' => 'active',
            'visibility' => 'public',
            'published_at' => now(),
        ]);
    }

    // ---------- tenant resolution ----------

    public function test_subdomain_host_serves_only_that_store_and_global_content(): void
    {
        $a = $this->servedBy('store-a.shopio.test');

        $a->getJson('/api/v1/storefront/products/store-a-only')->assertOk();
        $a->getJson('/api/v1/storefront/products/global-product')->assertOk();
        $a->getJson('/api/v1/storefront/products/store-b-only')->assertNotFound();

        $b = $this->servedBy('store-b.shopio.test');

        $b->getJson('/api/v1/storefront/products/store-b-only')->assertOk();
        $b->getJson('/api/v1/storefront/products/store-a-only')->assertNotFound();
    }

    public function test_verified_custom_domain_serves_its_store(): void
    {
        StoreDomain::create([
            'store_id' => $this->storeA->id,
            'domain' => 'myshop.com.bd',
            'type' => 'custom',
            'is_primary' => false,
            'ssl_status' => 'pending',
            'verified_at' => now(),
        ]);

        $served = $this->servedBy('myshop.com.bd');

        $served->getJson('/api/v1/storefront/products/store-a-only')->assertOk();
        $served->getJson('/api/v1/storefront/products/store-b-only')->assertNotFound();
    }

    public function test_unverified_custom_domain_is_never_served(): void
    {
        StoreDomain::create([
            'store_id' => $this->storeA->id,
            'domain' => 'pending-domain.com.bd',
            'type' => 'custom',
            'is_primary' => false,
            'ssl_status' => 'pending',
            'verified_at' => null,
        ]);

        $this->servedBy('pending-domain.com.bd')
            ->getJson('/api/v1/storefront/products/store-a-only')->assertNotFound();
    }

    public function test_central_reserved_and_unknown_hosts_are_rejected(): void
    {
        foreach (['shopio.test', 'www.shopio.test', 'api.shopio.test', 'admin.shopio.test', 'unknown-host.example.com', 'localhost'] as $host) {
            $this->servedBy($host)
                ->getJson('/api/v1/storefront/products/store-a-only')
                ->assertNotFound();
        }
    }

    public function test_legacy_v1_api_keeps_serving_everything_without_tenant_context(): void
    {
        $this->getJson('/api/v1/products/store-a-only')->assertOk();
        $this->getJson('/api/v1/products/store-b-only')->assertOk();
    }

    // ---------- content scoping ----------

    public function test_banners_are_scoped_to_the_resolved_store(): void
    {
        Banner::create(['store_id' => $this->storeA->id, 'banner_image' => 'banners/a.jpg', 'title' => 'A banner', 'status' => 'active']);
        Banner::create(['banner_image' => 'banners/platform.jpg', 'title' => 'Platform banner', 'status' => 'active']);
        $other = Banner::create(['store_id' => $this->storeB->id, 'banner_image' => 'banners/b.jpg', 'title' => 'B banner', 'status' => 'active']);

        $this->servedBy('store-a.shopio.test')
            ->getJson('/api/v1/storefront/banners')
            ->assertOk()
            ->assertJsonCount(2, 'data.items');

        $this->servedBy('store-a.shopio.test')
            ->getJson('/api/v1/storefront/banners/'.$other->id)
            ->assertNotFound();
    }

    public function test_store_setting_overrides_global_value_per_key(): void
    {
        Setting::create(['group' => 'general', 'key' => 'site_name', 'value' => 'Platform', 'type' => 'text', 'label' => 'Site name', 'sort_order' => 0]);
        Setting::create(['store_id' => $this->storeA->id, 'group' => 'general', 'key' => 'site_name', 'value' => 'Store A', 'type' => 'text', 'label' => 'Site name', 'sort_order' => 0]);

        $this->servedBy('store-a.shopio.test')
            ->getJson('/api/v1/storefront/settings')
            ->assertOk()
            ->assertJsonPath('data.site_name', 'Store A');

        $this->servedBy('store-b.shopio.test')
            ->getJson('/api/v1/storefront/settings')
            ->assertOk()
            ->assertJsonPath('data.site_name', 'Platform');
    }

    public function test_product_request_is_stamped_with_the_resolved_store(): void
    {
        $this->servedBy('store-a.shopio.test')
            ->postJson('/api/v1/storefront/product-requests', [
                'customer_name' => 'Karim',
                'customer_email' => 'karim@example.com',
                'product_name' => 'Blue saree',
                'quantity' => 2,
            ])->assertOk();

        $request = ProductRequest::where('customer_email', 'karim@example.com')->first();

        $this->assertNotNull($request);
        $this->assertSame($this->storeA->id, (int) $request->store_id);
    }

    public function test_sitemap_feed_is_isolated_per_store(): void
    {
        $this->servedBy('store-a.shopio.test')
            ->getJson('/api/v1/storefront/sitemap/products-count')
            ->assertOk()
            ->assertJsonPath('data.total', 2);

        $slugs = collect(
            $this->servedBy('store-a.shopio.test')
                ->getJson('/api/v1/storefront/sitemap/products')
                ->assertOk()
                ->json('data.items')
        )->pluck('slug');

        $this->assertContains('store-a-only', $slugs);
        $this->assertContains('global-product', $slugs);
        $this->assertNotContains('store-b-only', $slugs);
    }
}