<?php

namespace Tests\Feature\Saas;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Services\ProductService;
use Modules\Identity\Models\Role;
use Modules\Identity\Models\User;
use Modules\Store\Models\Store;
use Modules\Store\Support\CurrentStore;
use Tests\TestCase;

class ProductTenancyTest extends TestCase
{
    use RefreshDatabase;

    private User $ownerA;

    private User $ownerB;

    private User $superAdmin;

    private Store $storeA;

    private Store $storeB;

    protected function setUp(): void
    {
        parent::setUp();
        CurrentStore::reset();

        foreach (['Super Admin', 'Store Owner', 'Customer'] as $role) {
            Role::firstOrCreate(['name' => $role], ['description' => $role]);
        }

        $this->ownerA = $this->createUser('owner-a@example.com', 'Store Owner');
        $this->ownerB = $this->createUser('owner-b@example.com', 'Store Owner');
        $this->superAdmin = $this->createUser('admin@example.com', 'Super Admin');

        $this->storeA = $this->createStore($this->ownerA, 'Store A', 'store-a');
        $this->storeB = $this->createStore($this->ownerB, 'Store B', 'store-b');
    }

    protected function tearDown(): void
    {
        CurrentStore::reset();
        parent::tearDown();
    }

    private function createUser(string $email, string $role): User
    {
        $user = User::create([
            'public_id' => (string) Str::uuid(),
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => $email,
            'password_hash' => Hash::make('secret1234'),
            'status' => 'active',
        ]);

        $user->roles()->attach(Role::where('name', $role)->first()->id);

        return $user;
    }

    private function createStore(User $owner, string $name, string $slug): Store
    {
        return Store::create([
            'owner_id' => $owner->id,
            'name' => $name,
            'slug' => $slug,
            'status' => 'active',
            'currency_code' => 'USD',
            'timezone' => 'UTC',
        ]);
    }

    private function makeProduct(Store $store, string $name, array $overrides = []): Product
    {
        CurrentStore::set($store->id);

        return Product::create(array_merge([
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(5)),
            'product_type' => 'physical',
            'status' => 'active',
            'visibility' => 'public',
        ], $overrides));
    }

    public function test_product_creation_is_auto_scoped_to_the_owner_store(): void
    {
        $this->actingAs($this->ownerA);

        $product = Product::create([
            'name' => 'Owner A Product',
            'slug' => 'owner-a-product',
            'product_type' => 'physical',
            'status' => 'active',
            'visibility' => 'public',
        ]);

        $this->assertSame($this->storeA->id, $product->store_id);
    }

    public function test_store_owner_admin_catalog_only_shows_their_own_products(): void
    {
        $productA = $this->makeProduct($this->storeA, 'Product A1');
        $this->makeProduct($this->storeB, 'Product B1');

        $this->actingAs($this->ownerA);
        CurrentStore::reset();

        $this->assertSame(1, Product::forCurrentStore()->count());
        $this->assertTrue(Product::forCurrentStore()->find($productA->id)?->is($productA));
        $this->assertNull(Product::forCurrentStore()->find(Product::where('store_id', $this->storeB->id)->first()->id));
    }

    public function test_platform_admin_sees_all_products_and_can_filter_by_store(): void
    {
        $this->makeProduct($this->storeA, 'Product A1');
        $this->makeProduct($this->storeB, 'Product B1');

        $this->actingAs($this->superAdmin);
        CurrentStore::reset();

        // Super Admin without a store filter sees everything
        $this->assertSame(2, Product::forCurrentStore()->count());

        // Explicit ?store_id= filter narrows the admin catalog
        CurrentStore::reset();
        request()->query->set('store_id', $this->storeB->id);

        $this->assertSame(1, Product::forCurrentStore()->count());
        $this->assertSame('Product B1', Product::forCurrentStore()->first()->name);
    }

    public function test_store_owner_cannot_assign_another_store_via_payload(): void
    {
        $this->actingAs($this->ownerA);
        CurrentStore::reset();

        $response = app(ProductService::class)->saveProduct([
            'name' => 'Sneaky Product',
            'slug' => 'sneaky-product',
            'product_type' => 'physical',
            'status' => 'active',
            'visibility' => 'public',
            'store_id' => $this->storeB->id, // tampered payload
        ]);

        $this->assertSame(201, $response->status());

        $product = Product::where('slug', 'sneaky-product')->first();

        $this->assertNotNull($product);
        $this->assertSame($this->storeA->id, $product->store_id);
    }

    public function test_plain_product_queries_are_not_store_scoped(): void
    {
        // Simulates the public frontend API: a raw Product query must return
        // everything regardless of the authenticated user, so the storefront
        // behaves exactly as before this SaaS change.
        $this->makeProduct($this->storeA, 'Frontend A');
        $this->makeProduct($this->storeB, 'Frontend B');

        $this->actingAs($this->ownerA);
        CurrentStore::reset();

        $this->assertSame(2, Product::count());
    }

    public function test_same_slug_is_allowed_in_different_stores_but_not_the_same_store(): void
    {
        $this->makeProduct($this->storeA, 'Shared', ['slug' => 'shared']);
        $this->makeProduct($this->storeB, 'Shared', ['slug' => 'shared']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        $this->makeProduct($this->storeA, 'Duplicate', ['slug' => 'shared']);
    }

    public function test_create_slug_validation_uses_owner_store_despite_tampered_payload(): void
    {
        $this->makeProduct($this->storeA, 'Existing', ['slug' => 'existing']);
        $this->makeProduct($this->storeB, 'Other', ['slug' => 'other']);
        $this->actingAs($this->ownerA);
        CurrentStore::reset();

        $request = \Modules\Catalog\Http\Requests\StoreProductRequest::create('/', 'POST', ['store_id' => $this->storeB->id]);
        $request->setUserResolver(fn () => $this->ownerA);
        $rules = ['slug' => $request->rules()['slug']];

        $this->assertTrue(\Illuminate\Support\Facades\Validator::make(['slug' => 'other'], $rules)->passes());
        $this->assertFalse(\Illuminate\Support\Facades\Validator::make(['slug' => 'existing'], $rules)->passes());
    }

    public function test_update_slug_validation_uses_admin_destination_store_and_ignores_current_product(): void
    {
        $product = $this->makeProduct($this->storeA, 'Shared', ['slug' => 'shared']);
        $this->makeProduct($this->storeB, 'Shared', ['slug' => 'shared']);
        $this->actingAs($this->superAdmin);
        CurrentStore::reset();

        $request = \Modules\Catalog\Http\Requests\UpdateProductRequest::create('/', 'PUT');
        $request->setUserResolver(fn () => $this->superAdmin);
        $route = new \Illuminate\Routing\Route('PUT', 'products/{id}', fn () => null);
        $route->bind(\Illuminate\Http\Request::create('/products/'.$product->id, 'PUT'));
        $request->setRouteResolver(fn () => $route);

        $this->assertTrue(\Illuminate\Support\Facades\Validator::make(['slug' => 'shared'], ['slug' => $request->rules()['slug']])->passes());
        $request->merge(['store_id' => $this->storeB->id]);
        $this->assertFalse(\Illuminate\Support\Facades\Validator::make(['slug' => 'shared'], ['slug' => $request->rules()['slug']])->passes());
    }

    public function test_duplicate_slug_generation_only_checks_source_store(): void
    {
        $source = $this->makeProduct($this->storeA, 'Source', ['slug' => 'source']);
        $this->makeProduct($this->storeB, 'Copy', ['slug' => 'copy']);
        CurrentStore::set($this->storeA->id);

        $response = app(ProductService::class)->duplicateProduct($source->id, ['name' => 'Copy']);
        $this->assertSame(201, $response->status());
        $this->assertTrue(Product::where('store_id', $this->storeA->id)->where('slug', 'copy')->exists());
    }
}
