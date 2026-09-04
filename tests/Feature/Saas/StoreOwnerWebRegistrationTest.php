<?php

namespace Tests\Feature\Saas;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Identity\Models\Role;
use Modules\Identity\Models\User;
use Tests\TestCase;

class StoreOwnerWebRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Store Owner'], ['description' => 'SaaS tenant']);
        Role::firstOrCreate(['name' => 'Customer'], ['description' => 'Frontend customer']);
    }

    public function test_store_owner_can_register_via_the_public_web_form(): void
    {
        $response = $this->from('/register/store-owner')->post('/register/store-owner', [
            'first_name' => 'Rahim',
            'last_name' => 'Uddin',
            'email' => 'rahim@example.com',
            'phone' => '01711223344',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
            'store_name' => 'rahim electronics bd',
            'currency_code' => 'BDT',
            'timezone' => 'Asia/Dhaka',
        ]);

        $response->assertRedirect('/dashboard');

        $this->assertDatabaseHas('users', ['email' => 'rahim@example.com']);

        $user = User::where('email', 'rahim@example.com')->first();
        $this->assertTrue($user->roles->pluck('name')->contains('Store Owner'));
        $this->assertNotNull($user->ownedStore);
        $this->assertSame('Rahim Electronics Bd', $user->ownedStore->name);
        $this->assertSame('rahim-electronics-bd', $user->ownedStore->slug);
        $this->assertSame('active', $user->ownedStore->status);
        $this->assertTrue($this->isAuthenticated());
    }

    public function test_invalid_store_name_returns_validation_errors(): void
    {
        $response = $this->from('/register/store-owner')->post('/register/store-owner', [
            'first_name' => 'Rahim',
            'last_name' => 'Uddin',
            'email' => 'rahim@example.com',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
            'store_name' => '!!! @@@',
        ]);

        $response->assertSessionHasErrors('store_name');
        $this->assertDatabaseMissing('users', ['email' => 'rahim@example.com']);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        $this->from('/register/store-owner')->post('/register/store-owner', [
            'first_name' => 'Rahim',
            'last_name' => 'Uddin',
            'email' => 'rahim@example.com',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
            'store_name' => 'Rahim Electronics',
        ])->assertRedirect('/dashboard');

        // The first registration signs the owner in, so log out before
        // attempting a second registration on the guest-only route.
        auth('web')->logout();

        $response = $this->from('/register/store-owner')->post('/register/store-owner', [
            'first_name' => 'Karim',
            'last_name' => 'Uddin',
            'email' => 'rahim@example.com',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
            'store_name' => 'Karim Electronics',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertSame(1, User::where('email', 'rahim@example.com')->count());
    }
}
