<?php

namespace Tests\Feature\Saas;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Identity\Models\Role;
use Modules\Identity\Models\User;
use Modules\Store\Models\Store;
use Tests\TestCase;

class SidebarVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['Super Admin', 'Store Owner', 'Customer'] as $role) {
            Role::firstOrCreate(['name' => $role], ['description' => $role]);
        }

        foreach ([
            'products.view', 'products.create', 'products.edit', 'products.delete',
            'categories.view', 'categories.create', 'categories.edit',
            'brands.view', 'brands.create', 'brands.edit',
            'inventory.view', 'orders.view', 'stores.view', 'settings.view',
        ] as $perm) {
            \Modules\Identity\Models\Permission::firstOrCreate(['name' => $perm]);
        }

        $this->owner = $this->createUser('owner@example.com', 'Store Owner');
        $this->createStore($this->owner);
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

    private function createStore(User $owner): Store
    {
        return Store::create([
            'owner_id' => $owner->id,
            'name' => 'Owner Store',
            'slug' => 'owner-store',
            'status' => 'active',
            'currency_code' => 'USD',
            'timezone' => 'UTC',
        ]);
    }

    public function test_store_owner_sees_only_dashboard_and_catalog(): void
    {
        // Give the store owner catalog access only (as the platform admin configured).
        $ownerRole = Role::where('name', 'Store Owner')->first();
        $ownerRole->permissions()->sync(
            \Modules\Identity\Models\Permission::where('name', 'like', 'products.%')
                ->orWhere('name', 'like', 'categories.%')
                ->orWhere('name', 'like', 'brands.%')
                ->pluck('id')
        );

        $response = $this->actingAs($this->owner)->get(route('dashboard'));

        $response->assertOk();
        $content = $response->getContent();

        // Should see Dashboard + Catalog
        $this->assertStringContainsString('data-label="Dashboard"', $content);
        $this->assertStringContainsString('data-label="Catalog"', $content);

        // Should NOT see admin-only modules
        $this->assertStringNotContainsString('data-label="Identity"', $content);
        $this->assertStringNotContainsString('data-label="Store"', $content);
        $this->assertStringNotContainsString('data-label="Inventory"', $content);
        $this->assertStringNotContainsString('data-label="Purchases"', $content);
        $this->assertStringNotContainsString('data-label="Orders"', $content);
        $this->assertStringNotContainsString('data-label="POS"', $content);
        $this->assertStringNotContainsString('data-label="Reports"', $content);
        $this->assertStringNotContainsString('data-label="Settings"', $content);
    }

    public function test_super_admin_sees_all_modules_in_sidebar(): void
    {
        $admin = $this->createUser('super@example.com', 'Super Admin');

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $content = $response->getContent();

        foreach (['data-label="Dashboard"', 'data-label="Catalog"', 'data-label="Store"', 'data-label="Inventory"', 'data-label="Purchases"', 'data-label="Orders"', 'data-label="POS"', 'data-label="Reports"', 'data-label="Settings"'] as $menu) {
            $this->assertStringContainsString($menu, $content);
        }
    }
}