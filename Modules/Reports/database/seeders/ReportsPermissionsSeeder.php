<?php

namespace Modules\Reports\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Identity\Models\Permission;
use Modules\Identity\Models\Role;

/**
 * Seeds every permission referenced by the Reports module routes
 * (reports.dashboard, reports.sales, reports.products, reports.inventory,
 * reports.orders, reports.shipping, reports.customers, reports.campaigns,
 * reports.finance, reports.refunds, reports.purchases, reports.staff).
 *
 * Idempotent (firstOrCreate) and safe to re-run in production.
 * Super Admin automatically holds every permission.
 *
 * Run standalone:
 *   php artisan db:seed --class=Modules\\Reports\\Database\\Seeders\\ReportsPermissionsSeeder
 */
class ReportsPermissionsSeeder extends Seeder
{
    private const PERMISSIONS = [
        'reports.dashboard' => 'View dashboard reports',
        'reports.sales' => 'View sales reports',
        'reports.products' => 'View product reports',
        'reports.inventory' => 'View inventory reports',
        'reports.orders' => 'View order reports',
        'reports.shipping' => 'View shipping reports',
        'reports.customers' => 'View customer reports',
        'reports.campaigns' => 'View campaign reports',
        'reports.finance' => 'View finance reports',
        'reports.refunds' => 'View refund reports',
        'reports.purchases' => 'View purchase & supplier reports',
        'reports.staff' => 'View staff reports',
        // Legacy aliases some UI/sidebar checks still rely on
        'reports.view' => 'View reports (legacy)',
        'reports.executive' => 'View executive reports (legacy)',
    ];

    /**
     * Role => permissions to grant. Store owners manage their own
     * inventory & purchases so they get the matching reports too.
     */
    private const ROLE_GRANTS = [
        'Store Owner' => [
            'reports.inventory',
            'reports.purchases',
            'reports.products',
            'reports.orders',
            'reports.sales',
        ],
        'Store Staff' => [
            'reports.inventory',
            'reports.purchases',
        ],
    ];

    public function run(): void
    {
        $this->command->info('Seeding Reports module permissions...');

        foreach (self::PERMISSIONS as $name => $description) {
            $perm = Permission::firstOrCreate(['name' => $name], ['description' => $description]);
            $perm->update(['description' => $description]);
        }

        $this->command->info('Added/verified '.count(self::PERMISSIONS)." reports permissions.\n");

        // Super Admin holds every permission (system convention).
        $role = Role::where('name', 'Super Admin')->first();
        if ($role) {
            $role->permissions()->syncWithoutDetaching(Permission::all()->pluck('id'));
        }

        // Role grants (never revokes existing).
        foreach (self::ROLE_GRANTS as $roleName => $names) {
            $role = Role::where('name', $roleName)->first();
            if (! $role) {
                continue;
            }

            $ids = Permission::whereIn('name', $names)->pluck('id');
            $role->permissions()->syncWithoutDetaching($ids);

            $this->command->info("Granted ".$ids->count()." report permissions to '{$roleName}'.");
        }

        $this->command->info('Reports module permissions seeded successfully!');
    }
}