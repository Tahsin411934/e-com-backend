<?php

namespace Modules\Identity\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Identity\Models\Permission;
use Modules\Identity\Models\Role;

/**
 * Completes the permission catalogue for routes that had no matching
 * permission name in the DB (payments, notifications, webhooks,
 * webhook-deliveries, audit-logs). Idempotent — safe to re-run.
 *
 * Run standalone:
 *   php artisan db:seed --class=Modules\\Identity\\Database\\Seeders\\SystemPermissionsSeeder
 */
class SystemPermissionsSeeder extends Seeder
{
    private const PERMISSIONS = [
        'payments.view' => 'View payments',
        'payments.create' => 'Create payments',
        'payments.edit' => 'Edit payments',
        'payments.delete' => 'Delete payments',
        'notifications.view' => 'View notifications',
        'notifications.edit' => 'Edit notifications',
        'notifications.delete' => 'Delete notifications',
        'webhooks.view' => 'View webhooks',
        'webhooks.create' => 'Create webhooks',
        'webhooks.edit' => 'Edit webhooks',
        'webhooks.delete' => 'Delete webhooks',
        'webhook-deliveries.view' => 'View webhook deliveries',
        'webhook-deliveries.delete' => 'Delete webhook deliveries',
        'audit-logs.view' => 'View audit logs',
        'audit-logs.delete' => 'Delete audit logs',
        'histories.view' => 'View histories',
        'histories.export' => 'Export histories',
        'histories.restore' => 'Restore deleted records',
    ];

    public function run(): void
    {
        $this->command->info('Seeding system completion permissions...');

        foreach (self::PERMISSIONS as $name => $description) {
            $perm = Permission::firstOrCreate(['name' => $name], ['description' => $description]);
            $perm->update(['description' => $description]);
        }

        $this->command->info('Added/verified '.count(self::PERMISSIONS).' system permissions.');

        $role = Role::where('name', 'Super Admin')->first();
        if ($role) {
            $role->permissions()->syncWithoutDetaching(Permission::all()->pluck('id'));
        }

        $this->command->info('System permissions seeded successfully!');
    }
}