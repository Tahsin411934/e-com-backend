<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('sizes', 'size_group_id')) {
            Schema::table('sizes', function (Blueprint $table) {
                $table->unsignedBigInteger('size_group_id')->nullable()->after('id');
                $table->foreign('size_group_id')->references('id')->on('size_groups')->onDelete('set null');
                $table->index(['size_group_id']);
            });
        }

        // Backfill: every distinct legacy group_name becomes a SizeGroup row,
        // and each size row is linked to it.
        $existing = DB::table('sizes')
            ->whereNull('size_group_id')
            ->whereNotNull('group_name')
            ->select('group_name')
            ->distinct()
            ->pluck('group_name');

        foreach ($existing as $name) {
            $groupId = DB::table('size_groups')->where('name', $name)->value('id');
            if (! $groupId) {
                $groupId = DB::table('size_groups')->insertGetId([
                    'name' => $name,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('sizes')
                ->where('group_name', $name)
                ->whereNull('size_group_id')
                ->update(['size_group_id' => $groupId]);
        }
    }

    public function down()
    {
        if (Schema::hasColumn('sizes', 'size_group_id')) {
            Schema::table('sizes', function (Blueprint $table) {
                $table->dropForeign(['size_group_id']);
                $table->dropIndex(['size_group_id']);
                $table->dropColumn('size_group_id');
            });
        }
    }
};
