<?php

namespace Modules\Identity\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Identity\Models\Permission;
use Modules\Identity\Models\Role;
use Modules\Identity\Models\User;

class IdentityDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ===== Permissions =====
        $permissions = [
            ['name' => 'users.view', 'description' => 'View users'],
            ['name' => 'users.create', 'description' => 'Create users'],
            ['name' => 'users.edit', 'description' => 'Edit users'],
            ['name' => 'users.delete', 'description' => 'Delete users'],
            ['name' => 'roles.view', 'description' => 'View roles'],
            ['name' => 'roles.create', 'description' => 'Create roles'],
            ['name' => 'roles.edit', 'description' => 'Edit roles'],
            ['name' => 'roles.delete', 'description' => 'Delete roles'],
            ['name' => 'permissions.view', 'description' => 'View permissions'],
            ['name' => 'permissions.create', 'description' => 'Create permissions'],
            ['name' => 'permissions.edit', 'description' => 'Edit permissions'],
            ['name' => 'permissions.delete', 'description' => 'Delete permissions'],
            ['name' => 'products.view', 'description' => 'View products'],
            ['name' => 'products.create', 'description' => 'Create products'],
            ['name' => 'products.edit', 'description' => 'Edit products'],
            ['name' => 'products.delete', 'description' => 'Delete products'],
            ['name' => 'categories.view', 'description' => 'View categories'],
            ['name' => 'categories.create', 'description' => 'Create categories'],
            ['name' => 'categories.edit', 'description' => 'Edit categories'],
            ['name' => 'categories.delete', 'description' => 'Delete categories'],
            ['name' => 'brands.view', 'description' => 'View brands'],
            ['name' => 'brands.create', 'description' => 'Create brands'],
            ['name' => 'brands.edit', 'description' => 'Edit brands'],
            ['name' => 'brands.delete', 'description' => 'Delete brands'],
            ['name' => 'inventory.view', 'description' => 'View inventory'],
            ['name' => 'inventory.create', 'description' => 'Create inventory'],
            ['name' => 'inventory.edit', 'description' => 'Edit inventory'],
            ['name' => 'inventory.delete', 'description' => 'Delete inventory'],
            ['name' => 'orders.view', 'description' => 'View orders'],
            ['name' => 'orders.create', 'description' => 'Create orders'],
            ['name' => 'orders.edit', 'description' => 'Edit orders'],
            ['name' => 'orders.delete', 'description' => 'Delete orders'],
            ['name' => 'stores.view', 'description' => 'View stores'],
            ['name' => 'stores.edit', 'description' => 'Edit stores'],
            ['name' => 'settings.view', 'description' => 'View settings'],
            ['name' => 'settings.edit', 'description' => 'Edit settings'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm['name']], $perm);
        }

        // ===== Roles =====
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin'], ['description' => 'Full system access']);
        $adminRole = Role::firstOrCreate(['name' => 'Admin'], ['description' => 'Administrative access']);
        $managerRole = Role::firstOrCreate(['name' => 'Manager'], ['description' => 'Day-to-day management']);
        $staffRole = Role::firstOrCreate(['name' => 'Staff'], ['description' => 'Limited staff access']);
        $customerRole = Role::firstOrCreate(['name' => 'Customer'], ['description' => 'Customer account']);

        // Super Admin gets all permissions
        $superAdminRole->permissions()->sync(Permission::all()->pluck('id'));

        // Admin gets everything except permissions management
        $adminPermissionNames = Permission::whereNotIn('name', [
            'permissions.view', 'permissions.create', 'permissions.edit', 'permissions.delete',
            'roles.create', 'roles.edit', 'roles.delete', 'roles.view',
        ])->pluck('id');
        $adminRole->permissions()->sync($adminPermissionNames);

        // Manager gets product, category, brand, inventory, order, store access
        $managerPermissionNames = Permission::whereIn('name', [
            'products.view', 'products.create', 'products.edit',
            'categories.view', 'categories.create', 'categories.edit',
            'brands.view', 'brands.create', 'brands.edit',
            'inventory.view', 'inventory.create', 'inventory.edit',
            'orders.view', 'orders.create', 'orders.edit',
            'stores.view',
        ])->pluck('id');
        $managerRole->permissions()->sync($managerPermissionNames);

        // Staff gets view-only access
        $staffPermissionNames = Permission::whereIn('name', [
            'products.view', 'categories.view', 'brands.view', 'inventory.view', 'orders.view',
        ])->pluck('id');
        $staffRole->permissions()->sync($staffPermissionNames);

        // ===== Users =====
        $passwordHash = Hash::make('password');

        $users = [
            [
                'public_id' => (string) Str::uuid(),
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'email' => 'superadmin@onehaatbd.com',
                'phone' => '01700000000',
                'password_hash' => $passwordHash,
                'status' => 'active',
            ],
            [
                'public_id' => (string) Str::uuid(),
                'first_name' => 'John',
                'last_name' => 'Manager',
                'email' => 'manager1@onehaatbd.com',
                'phone' => '01700000009',
                'password_hash' => $passwordHash,
                'status' => 'active',
            ],
            [
                'public_id' => (string) Str::uuid(),
                'first_name' => 'Jane',
                'last_name' => 'Staff',
                'email' => 'staff1@onehaatbd.com',
                'phone' => '01700000008',
                'password_hash' => $passwordHash,
                'status' => 'active',
            ],
            [
                'public_id' => (string) Str::uuid(),
                'first_name' => 'Bob',
                'last_name' => 'Customer',
                'email' => 'customer1@onehaatbd.com',
                'phone' => '01700000007',
                'password_hash' => $passwordHash,
                'status' => 'active',
            ],
        ];

        $roleMap = [
            'superadmin@onehaatbd.com' => 'Super Admin',
            'manager1@onehaatbd.com' => 'Manager',
            'staff1@onehaatbd.com' => 'Staff',
            'customer1@onehaatbd.com' => 'Customer',
        ];

        foreach ($users as $userData) {
            $email = $userData['email'];
            $user = User::firstOrCreate(
                ['email' => $email],
                $userData
            );
            if (isset($roleMap[$email])) {
                $role = Role::where('name', $roleMap[$email])->first();
                if ($role) {
                    $user->roles()->syncWithoutDetaching([$role->id]);
                }
            }
        }

        $this->command->info('Identity module seeded successfully!');
    }
}
