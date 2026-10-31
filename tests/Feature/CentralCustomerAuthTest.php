<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Catalog\Models\Product;
use Modules\Identity\Models\Customer;
use Modules\Identity\Models\User;
use Modules\Store\Models\Store;
use Modules\Store\Support\CurrentStore;
use Modules\Store\Support\StoreDomainResolver;
use Tests\TestCase;

class CentralCustomerAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CurrentStore::reset();
        StoreDomainResolver::reset();
        parent::tearDown();
    }

    private function registration(array $extra = []): array
    {
        return $extra + ['first_name' => 'Central', 'last_name' => 'Customer', 'email' => 'customer@example.test',
            'password' => 'password123', 'password_confirmation' => 'password123'];
    }

    private function store(): Store
    {
        $owner = User::create(['public_id' => (string) Str::uuid(), 'first_name' => 'Store', 'last_name' => 'Owner',
            'email' => 'owner@example.test', 'password_hash' => Hash::make('password123'), 'status' => 'active']);

        return Store::create(['owner_id' => $owner->id, 'name' => 'Alpha', 'slug' => 'alpha',
            'email' => 'alpha@example.test', 'phone' => '12345', 'status' => 'active', 'currency_code' => 'BDT']);
    }

    private function customer(?int $storeId = null): Customer
    {
        return Customer::create(['store_id' => $storeId, 'first_name' => 'Test', 'last_name' => 'Customer',
            'email' => 'customer@example.test', 'password_hash' => Hash::make('password123'), 'status' => 'active']);
    }

    public function test_central_registration_needs_no_store_and_ignores_store_assignment(): void
    {
        $response = $this->postJson('/api/v1/central/auth/register', $this->registration(['store_id' => 999]));
        $response->assertCreated()->assertJsonPath('data.customer.store_id', null);
        $this->assertDatabaseHas('customers', ['email' => 'customer@example.test', 'store_id' => null]);
        $this->assertDatabaseMissing('users', ['email' => 'customer@example.test']);
        $this->assertTrue(Hash::check('password123', Customer::firstOrFail()->password_hash));
        $response->assertJsonMissingPath('data.customer.password_hash')->assertJsonMissingPath('data.customer.central_email');
        $this->withToken($response->json('data.token'))->getJson('/api/v1/central/auth/me')->assertOk()->assertJsonPath('data.customer.id', $response->json('data.customer.id'));
    }

    public function test_same_email_can_register_centrally_and_per_store_but_not_twice_centrally(): void
    {
        $this->customer($this->store()->id);
        $this->postJson('/api/v1/central/auth/register', $this->registration())->assertCreated();
        $this->postJson('/api/v1/central/auth/register', $this->registration(['email' => 'CUSTOMER@example.test']))->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_login_token_restores_session_across_fresh_authenticated_requests(): void
    {
        $customer = $this->customer();
        $login = $this->postJson('/api/v1/central/auth/login', [
            'email' => $customer->email, 'password' => 'password123',
        ])->assertOk();
        $token = $login->json('data.token');

        // A refresh cannot rely on the User/Customer object used during login.
        foreach (range(1, 3) as $request) {
            auth()->forgetGuards();
            $this->withToken($token)->getJson('/api/v1/central/auth/me')
                ->assertOk()->assertJsonPath('data.customer.id', $customer->id);
        }

        auth()->forgetGuards();
        $this->postJson('/api/v1/central/auth/logout')->assertOk();
        auth()->forgetGuards();
        $this->getJson('/api/v1/central/auth/me')->assertUnauthorized();
    }

    public function test_central_login_does_not_authenticate_a_store_customer(): void
    {
        $this->customer($this->store()->id);
        $this->postJson('/api/v1/central/auth/login', ['email' => 'customer@example.test', 'password' => 'password123'])->assertUnauthorized();
        $central = $this->customer();
        $this->postJson('/api/v1/central/auth/login', ['email' => 'customer@example.test', 'password' => 'password123'])->assertOk()->assertJsonPath('data.customer.id', $central->id);
        $central->update(['status' => 'blocked']);
        $this->postJson('/api/v1/central/auth/login', ['email' => 'customer@example.test', 'password' => 'password123'])->assertForbidden();
    }

    public function test_existing_store_registration_stays_store_scoped(): void
    {
        config(['storefront.domain_suffix' => 'example.test', 'storefront.fallback_store' => null]);
        $store = $this->store();
        $this->customer();
        $this->withHeader('X-Store-Host', 'alpha.example.test')
            ->postJson('/api/v1/storefront/auth/register', $this->registration())
            ->assertCreated()->assertJsonPath('data.customer.store_id', $store->id);
        $this->assertDatabaseCount('customers', 2);
    }

    public function test_central_and_store_tokens_cannot_cross_account_boundaries(): void
    {
        config(['storefront.domain_suffix' => 'example.test', 'storefront.fallback_store' => null]);
        $store = $this->store();
        $tenant = $this->customer($store->id);
        $central = $this->customer();
        $this->withToken($tenant->createToken('storefront-customer')->plainTextToken)->getJson('/api/v1/central/auth/me')->assertForbidden();
        auth()->forgetGuards();
        $this->withToken($central->createToken('central-customer')->plainTextToken)->withHeader('X-Store-Host', 'alpha.example.test')->getJson('/api/v1/storefront/auth/me')->assertForbidden();
        $this->getJson('/api/v1/user')->assertForbidden();
    }

    public function test_central_password_reset_never_changes_store_account_with_same_email(): void
    {
        $tenant = $this->customer($this->store()->id);
        $central = $this->customer();
        DB::table('customer_password_reset_tokens')->insert(['customer_id' => $central->id, 'token' => Hash::make('reset-token'), 'created_at' => now()]);
        $this->postJson('/api/v1/central/auth/reset-password', ['email' => $central->email, 'token' => 'reset-token',
            'password' => 'newpassword123', 'password_confirmation' => 'newpassword123'])->assertOk();
        $this->assertTrue(Hash::check('newpassword123', $central->fresh()->password_hash));
        $this->assertTrue(Hash::check('password123', $tenant->fresh()->password_hash));
        $this->assertDatabaseMissing('customer_password_reset_tokens', ['customer_id' => $central->id]);
    }

    public function test_central_checkout_records_customer_ownership_and_history(): void
    {
        $central = $this->customer();
        $product = Product::create(['name' => 'Central product', 'slug' => 'central-product',
            'status' => 'active', 'visibility' => 'public', 'product_type' => 'physical', 'delivery_charge' => 50]);
        $variant = $product->variants()->create(['name' => 'Default', 'sku' => 'CENTRAL-001', 'sale_price' => 100, 'status' => 'active']);
        $this->withToken($central->createToken('central-customer')->plainTextToken);
        $this->postJson('/api/v1/central/carts/sync', ['items' => [['product_id' => $product->id, 'variant_id' => $variant->id, 'quantity' => 2]]])->assertOk();
        $checkout = $this->postJson('/api/v1/central/checkout', ['delivery_address' => 'Test road', 'delivery_city' => 'Dhaka', 'delivery_phone' => '01700000000']);
        $checkout->assertOk()->assertJsonPath('status', 'success')->assertJsonPath('order_count', 1);
        $this->assertDatabaseHas('orders', ['id' => $checkout->json('orders.0.id'), 'customer_id' => $central->id, 'user_id' => null]);
        $this->getJson('/api/v1/central/orders')->assertOk()->assertJsonCount(1, 'data');
    }
}
