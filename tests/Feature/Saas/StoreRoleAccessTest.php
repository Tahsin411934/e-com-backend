<?php

namespace Tests\Feature\Saas;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Identity\Models\Permission;
use Modules\Identity\Models\Role;
use Modules\Identity\Models\User;
use Modules\Store\Models\Store;
use Modules\Store\Models\StoreStaff;
use Modules\Store\Support\CurrentStore;
use Tests\TestCase;

class StoreRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_roles_are_isolated_and_staff_can_access_their_store(): void
    {
        $posPermission = Permission::create(['name' => 'pos.sell', 'description' => 'Sell through POS']);
        $inventoryPermission = Permission::create(['name' => 'inventory-stock.view', 'description' => 'View stock']);

        $storeA = Store::create(['name' => 'Role Store A', 'slug' => 'role-store-a', 'status' => 'active']);
        $storeB = Store::create(['name' => 'Role Store B', 'slug' => 'role-store-b', 'status' => 'active']);

        $user = User::create([
            'public_id' => (string) Str::uuid(),
            'first_name' => 'Store',
            'last_name' => 'Admin',
            'email' => 'store-role-admin@example.com',
            'password_hash' => Hash::make('secret1234'),
            'status' => 'active',
        ]);

        $role = Role::create([
            'name' => 'POS Admin',
            'description' => 'POS access for Store A',
            'scope' => 'store',
            'store_id' => $storeA->id,
        ]);
        $role->permissions()->sync([$posPermission->id]);

        StoreStaff::create([
            'store_id' => $storeA->id,
            'user_id' => $user->id,
            'status' => 'active',
        ])->roles()->sync([$role->id]);

        $this->post('/login', [
            'email' => 'store-role-admin@example.com',
            'password' => 'secret1234',
        ])->assertRedirect();

        CurrentStore::set($storeA->id);
        $this->assertTrue($user->hasAdminAccess());
        $this->assertTrue($user->hasPermission('pos.sell'));
        $this->assertFalse($user->hasPermission('inventory-stock.view'));

        CurrentStore::set($storeB->id);
        $this->assertFalse($user->hasPermission('pos.sell'));
        $this->assertFalse($user->hasPermission('inventory-stock.view'));

        CurrentStore::reset();
    }
}
