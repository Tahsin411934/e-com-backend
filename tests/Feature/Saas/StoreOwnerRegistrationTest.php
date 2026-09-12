<?php

namespace Tests\Feature\Saas;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Identity\Models\Role;
use Modules\Identity\Models\User;
use Modules\Store\Models\Store;
use Tests\TestCase;

class StoreOwnerRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private array $payload;

    protected function setUp(): void
    {
        parent::setUp();

        // Registration depends on the seeded roles
        Role::firstOrCreate(['name' => 'Store Owner'], ['description' => 'SaaS tenant']);
        Role::firstOrCreate(['name' => 'Customer'], ['description' => 'Frontend customer']);

        $this->payload = [
            'first_name' => 'Rahim',
            'last_name' => 'Uddin',
            'email' => 'rahim@example.com',
            'phone' => '01711223344',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
            'store_name' => 'rahim electronics bd',
            'currency_code' => 'BDT',
            'timezone' => 'Asia/Dhaka',
        ];
    }

    public function test_store_owner_can_register_with_their_store(): void
    {
        $response = $this->postJson('/api/v1/register/store-owner', $this->payload);

        $response->assertCreated()
            ->assertJsonPath('data.store.slug', 'rahim-electronics-bd')
            ->assertJsonPath('data.store.name', 'Rahim Electronics Bd')
            ->assertJsonStructure(['data' => ['token']]);

        $user = User::where('email', 'rahim@example.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->roles->pluck('name')->contains('Store Owner'));

        $store = Store::where('owner_id', $user->id)->first();
        $this->assertNotNull($store);
        $this->assertSame('active', $store->status);
        $this->assertSame('BDT', $store->currency_code);
        $this->assertSame('Asia/Dhaka', $store->timezone);
    }

    public function test_store_name_must_be_professional(): void
    {
        $this->payload['store_name'] = '!!! @@@';

        $response = $this->postJson('/api/v1/register/store-owner', $this->payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('store_name');
    }

    public function test_registration_returns_json_validation_errors_without_accept_header(): void
    {
        $this->post('/api/v1/register/store-owner', [])
            ->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonValidationErrors(['first_name', 'last_name', 'email', 'password', 'store_name']);

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('stores', 0);
    }

    public function test_registration_works_without_seeded_roles_or_optional_fields(): void
    {
        Role::where('name', User::STORE_OWNER_ROLE)->forceDelete();
        unset($this->payload['phone'], $this->payload['currency_code'], $this->payload['timezone']);

        $this->postJson('/api/v1/register/store-owner', $this->payload)
            ->assertCreated()
            ->assertJsonPath('data.store.currency_code', 'USD')
            ->assertJsonPath('data.store.timezone', 'UTC');

        $user = User::where('email', $this->payload['email'])->firstOrFail();
        $this->assertTrue($user->hasAdminAccess());
        $this->assertTrue($user->roles->pluck('name')->contains(User::STORE_OWNER_ROLE));
        $this->assertGuest('web');
    }

    public function test_duplicate_email_is_rejected_without_creating_another_store(): void
    {
        $this->postJson('/api/v1/register/store-owner', $this->payload)->assertCreated();
        $this->payload['phone'] = '01799887766';

        $this->postJson('/api/v1/register/store-owner', $this->payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('stores', 1);
    }

    public function test_password_confirmation_must_match(): void
    {
        $this->payload['password_confirmation'] = 'different1234';

        $this->postJson('/api/v1/register/store-owner', $this->payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors('password');

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('stores', 0);
    }

    public function test_old_web_registration_endpoints_are_removed(): void
    {
        $this->get('/register/store-owner')->assertNotFound();
        $this->post('/register/store-owner', $this->payload)->assertNotFound();
    }

    public function test_duplicate_store_names_receive_unique_slugs(): void
    {
        $this->postJson('/api/v1/register/store-owner', $this->payload)->assertCreated();

        $second = $this->payload;
        $second['email'] = 'second@example.com';
        $second['phone'] = '01799887766';

        $response = $this->postJson('/api/v1/register/store-owner', $second);

        $response->assertCreated()
            ->assertJsonPath('data.store.slug', 'rahim-electronics-bd-2');
    }

    public function test_store_owner_can_login_and_receives_store_context(): void
    {
        $this->postJson('/api/v1/register/store-owner', $this->payload)->assertCreated();

        $response = $this->postJson('/api/v1/login', [
            'email' => 'rahim@example.com',
            'password' => 'secret1234',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.store.slug', 'rahim-electronics-bd')
            ->assertJsonPath('data.user.email', 'rahim@example.com');
    }

    public function test_my_store_endpoint_returns_owned_store(): void
    {
        $register = $this->postJson('/api/v1/register/store-owner', $this->payload);
        $register->assertCreated();

        $token = $register->json('data.token');

        $response = $this->getJson('/api/v1/store', ['Authorization' => "Bearer {$token}"]);

        $response->assertOk()
            ->assertJsonPath('data.store.slug', 'rahim-electronics-bd');
    }

    public function test_store_owner_cannot_manage_platform_users(): void
    {
        $register = $this->postJson('/api/v1/register/store-owner', $this->payload);
        $register->assertCreated();

        $token = $register->json('data.token');

        $response = $this->getJson('/api/v1/users', ['Authorization' => "Bearer {$token}"]);

        $response->assertStatus(403);
    }
}
