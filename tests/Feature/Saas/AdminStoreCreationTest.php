<?php

namespace Tests\Feature\Saas;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\Identity\Models\User;
use Modules\Store\Models\Store;
use Modules\Store\Services\StoreService;
use Tests\TestCase;

class AdminStoreCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_store_from_admin_creates_owner_credentials(): void
    {
        $service = app(StoreService::class);

        $response = $service->saveStore([
            'name' => 'Admin Created Store',
            'slug' => 'admin-created-store',
            'email' => 'owner@example.com',
            'phone' => '01700000001',
            'status' => 'active',
            'currency_code' => 'USD',
            'timezone' => 'UTC',
            'owner_first_name' => 'Karim',
            'owner_last_name' => 'Uddin',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $this->assertSame(200, $response->status());

        // The owner's user account is created with the Store Owner role.
        $owner = User::where('email', 'owner@example.com')->first();
        $this->assertNotNull($owner);
        $this->assertTrue($owner->roles->pluck('name')->contains('Store Owner'));
        $this->assertTrue(Hash::check('secret1234', $owner->password_hash));
        $this->assertTrue($owner->hasAdminAccess(), 'Store owner must access the admin panel');

        // The store is linked to the owner.
        $store = Store::where('slug', 'admin-created-store')->first();
        $this->assertNotNull($store);
        $this->assertSame($owner->id, $store->owner_id);
        $this->assertSame('Admin Created Store', $store->name);
    }

    public function test_updating_a_store_ignores_owner_credential_fields(): void
    {
        $service = app(StoreService::class);

        $service->saveStore([
            'name' => 'Original Store',
            'slug' => 'original-store',
            'email' => 'owner@example.com',
            'status' => 'active',
            'currency_code' => 'USD',
            'timezone' => 'UTC',
            'owner_first_name' => 'Karim',
            'owner_last_name' => 'Uddin',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $store = Store::where('slug', 'original-store')->first();
        $originalOwner = $store->owner_id;

        // Editing sends the same form; owner credential inputs are ignored.
        $service->saveStore([
            'store_id' => $store->id,
            'name' => 'Renamed Store',
            'slug' => 'renamed-store',
            'email' => 'newemail@example.com',
            'status' => 'inactive',
            'currency_code' => 'BDT',
            'timezone' => 'Asia/Dhaka',
            'owner_first_name' => 'Someone',
            'owner_last_name' => 'Else',
            'password' => 'anotherpass1',
            'password_confirmation' => 'anotherpass1',
        ]);

        $store->refresh();
        $this->assertSame('Renamed Store', $store->name);
        $this->assertSame($originalOwner, $store->owner_id, 'Owner must not change on update');

        $owner = User::where('email', 'owner@example.com')->first();
        $this->assertNotNull($owner, 'Original owner login must remain');
        $this->assertTrue(Hash::check('secret1234', $owner->password_hash), 'Owner password must not change on store update');
        $this->assertNull(User::where('email', 'someone@example.com')->first());
    }

    public function test_duplicate_owner_email_is_blocked(): void
    {
        // This email is already taken by a user (e.g. a customer).
        User::create([
            'public_id' => 'existing-user',
            'first_name' => 'Existing',
            'last_name' => 'User',
            'email' => 'taken@example.com',
            'password_hash' => Hash::make('secret1234'),
            'status' => 'active',
        ]);

        $service = app(StoreService::class);

        // Through HTTP the FormRequest blocks this with a 422 first; calling
        // the service directly hits its safety net, which returns an error
        // response and rolls everything back.
        $response = $service->saveStore([
            'name' => 'Duplicate Email Store',
            'slug' => 'duplicate-email-store',
            'email' => 'taken@example.com',
            'status' => 'active',
            'currency_code' => 'USD',
            'timezone' => 'UTC',
            'owner_first_name' => 'Karim',
            'owner_last_name' => 'Uddin',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $this->assertSame(500, $response->status());
        $this->assertNull(Store::where('slug', 'duplicate-email-store')->first());
        $this->assertNull(User::where('first_name', 'Karim')->where('last_name', 'Uddin')->first());
        $this->assertSame(1, User::where('email', 'taken@example.com')->count());
    }
}
