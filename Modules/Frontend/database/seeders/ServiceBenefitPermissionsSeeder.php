<?php

namespace Modules\Frontend\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Identity\Models\Permission;
use Modules\Identity\Models\Role;

class ServiceBenefitPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $ids = [];
        foreach (['view', 'create', 'edit', 'delete'] as $action) {
            $ids[] = Permission::firstOrCreate(
                ['name' => 'frontend.service-benefits.'.$action],
                ['description' => ucfirst($action).' store service benefits']
            )->id;
        }

        foreach (Role::whereIn('name', ['Super Admin', 'Admin', 'Store Owner'])->get() as $role) {
            $role->permissions()->syncWithoutDetaching($ids);
        }
    }
}
