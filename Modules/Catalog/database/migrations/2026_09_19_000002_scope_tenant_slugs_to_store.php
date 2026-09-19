<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['brands', 'categories'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->unique(['store_id', 'slug'], $tableName.'_store_slug_unique');
            });
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->dropUnique($tableName.'_slug_unique');
            });
        }

        foreach (['navbar_items', 'subnavbar_items'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->unique(['store_id', 'slug'], $tableName.'_store_slug_unique');
            });
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->dropUnique($tableName.'_slug_unique');
            });
        }
    }

    public function down(): void
    {
        foreach (['brands', 'categories', 'navbar_items', 'subnavbar_items'] as $tableName) {
            if (DB::table($tableName)->select('slug')->groupBy('slug')->havingRaw('COUNT(*) > 1')->exists()) {
                throw new RuntimeException("Cannot restore global {$tableName} slug uniqueness while duplicate slugs exist.");
            }
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->unique('slug', $tableName.'_slug_unique');
                $table->dropUnique($tableName.'_store_slug_unique');
            });
        }
    }
};
