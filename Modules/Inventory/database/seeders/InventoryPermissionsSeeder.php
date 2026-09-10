<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Modules\Identity\Models\Permission;
use Modules\Identity\Models\Role;

/**
 * Seeds every permission referenced by the Inventory module routes:
 *   - inventory-locations.*
 *   - inventory-stock.*
 *   - inventory-movements.*
 *   - inventory.*            (legacy umbrella - used by dashboards/reports)
 *   - suppliers.*
 *   - purchase-orders.*
 *   - purchase-returns.*
 *   - supplier-payments.*
 *
 * Permissions are idempotent (firstOrCreate) so it is safe to run in
 * production as many times as needed — nothing is ever revoked/deleted.
 * Super Admin automatically receives every permission; all other roles
 * are granted manually via the Role Management UI.
 *
 * Run standalone:
 *   php artisan db:seed --class=Modules\\Inventory\\Database\\Seeders\\InventoryPermissionsSeeder
 */
class InventoryPermissionsSeeder extends Seeder
{
    /**
     * module_key => [permission name => description]
     */
    private const MODULES = [
        'inventory_locations' => [
            'inventory-locations.view' => 'View inventory locations',
            'inventory-locations.create' => 'Create inventory locations',
            'inventory-locations.edit' => 'Edit inventory locations',
            'inventory-locations.delete' => 'Delete inventory locations',
        ],
        'inventory_stock' => [
            'inventory-stock.view' => 'View inventory stock',
            'inventory-stock.create' => 'Create inventory stock',
            'inventory-stock.edit' => 'Edit inventory stock',
            'inventory-stock.delete' => 'Delete inventory stock',
        ],
        'inventory_movements' => [
            'inventory-movements.view' => 'View inventory movements',
            'inventory-movements.create' => 'Create inventory movements',
            'inventory-movements.edit' => 'Edit inventory movements',
            'inventory-movements.delete' => 'Delete inventory movements',
        ],
        'inventory' => [
            'inventory.view' => 'View inventory (legacy umbrella)',
            'inventory.create' => 'Create inventory (legacy umbrella)',
            'inventory.edit' => 'Edit inventory (legacy umbrella)',
            'inventory.delete' => 'Delete inventory (legacy umbrella)',
        ],
        'suppliers' => [
            'suppliers.view' => 'View suppliers',
            'suppliers.create' => 'Create suppliers',
            'suppliers.edit' => 'Edit suppliers',
            'suppliers.delete' => 'Delete suppliers',
        ],
        'purchase_orders' => [
            'purchase-orders.view' => 'View purchase orders',
            'purchase-orders.create' => 'Create purchase orders',
            'purchase-orders.edit' => 'Edit purchase orders',
            'purchase-orders.delete' => 'Delete purchase orders',
        ],
        'purchase_returns' => [
            'purchase-returns.view' => 'View purchase returns',
            'purchase-returns.create' => 'Create purchase returns',
            'purchase-returns.edit' => 'Edit purchase returns',
            'purchase-returns.delete' => 'Delete purchase returns',
        ],
        'supplier_payments' => [
            'supplier-payments.view' => 'View supplier payments',
            'supplier-payments.create' => 'Create supplier payments',
            'supplier-payments.edit' => 'Edit supplier payments',
            'supplier-payments.delete' => 'Delete supplier payments',
        ],
    ];

    public function run(): void
    {
        $this->command->info('Seeding Inventory module permissions...');

        $created = 0;

        foreach (self::MODULES as $module => $permissions) {
            foreach ($permissions as $name => $description) {
                $perm = Permission::firstOrCreate(['name' => $name], ['description' => $description]);
                $perm->update(['description' => $description]);

                $created++;
            }

            $this->command->info("  [{$module}] ".count($permissions).' permissions.');
        }

        $this->command->info("Added/verified {$created} inventory permissions.");

        // Super Admin always holds every permission (system convention).
        $role = Role::where('name', 'Super Admin')->first();
        if ($role) {
            $role->permissions()->syncWithoutDetaching(Permission::all()->pluck('id'));
        }

        $this->command->info('Inventory module permissions seeded successfully!');
    }
}