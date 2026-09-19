<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permissionIds = DB::table('permissions')
            ->where('name', 'like', 'frontend.%')
            ->orWhere('name', 'like', 'marketing.%')
            ->pluck('id');

        foreach (['Super Admin', 'Admin'] as $roleName) {
            $roleId = DB::table('roles')->where('name', $roleName)->value('id');
            if (! $roleId) {
                continue;
            }

            foreach ($permissionIds as $permissionId) {
                DB::table('role_permissions')->updateOrInsert(
                    ['role_id' => $roleId, 'permission_id' => $permissionId],
                    ['updated_at' => now(), 'created_at' => now(), 'deleted_at' => null]
                );
            }
        }
    }

    public function down(): void
    {
        // Platform roles retain their permissions on rollback; removing them
        // could revoke access granted before this corrective migration.
    }
};
