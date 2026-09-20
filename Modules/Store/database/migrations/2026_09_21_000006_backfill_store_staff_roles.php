<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $roleId = DB::table('roles')->where('name', 'Store Staff')->value('id');
        if (! $roleId) {
            return;
        }

        $staffRows = DB::table('store_staff')->whereNull('deleted_at')->pluck('id');
        foreach ($staffRows as $staffId) {
            DB::table('store_staff_roles')->updateOrInsert(
                ['store_staff_id' => $staffId, 'role_id' => $roleId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        $roleId = DB::table('roles')->where('name', 'Store Staff')->value('id');
        if ($roleId) {
            DB::table('store_staff_roles')->where('role_id', $roleId)->delete();
        }
    }
};
