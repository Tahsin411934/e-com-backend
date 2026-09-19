<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $roleId = DB::table('roles')->where('name', 'Store Owner')->value('id');
        if (! $roleId) {
            return;
        }

        $permissionIds = DB::table('permissions')
            ->where(function ($query) {
                $query->where('name', 'like', 'frontend.%')
                    ->orWhere('name', 'like', 'marketing.%');
            })
            ->pluck('id');

        foreach ($permissionIds as $permissionId) {
            DB::table('role_permissions')->updateOrInsert(
                ['role_id' => $roleId, 'permission_id' => $permissionId],
                ['updated_at' => now(), 'created_at' => now(), 'deleted_at' => null]
            );
        }
    }

    public function down(): void
    {
        $roleId = DB::table('roles')->where('name', 'Store Owner')->value('id');
        if ($roleId) {
            DB::table('role_permissions')
                ->where('role_id', $roleId)
                ->whereIn('permission_id', function ($query) {
                    $query->select('id')->from('permissions')
                        ->where('name', 'like', 'frontend.%')
                        ->orWhere('name', 'like', 'marketing.%');
                })->delete();
        }
    }
};
