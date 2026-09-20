<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('categories') && ! Schema::hasColumn('categories', 'is_central_category')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->boolean('is_central_category')->default(false)->index();
            });
        }

        if (Schema::hasTable('navbar_items') && ! Schema::hasColumn('navbar_items', 'is_central_navbar_item')) {
            Schema::table('navbar_items', function (Blueprint $table) {
                $table->boolean('is_central_navbar_item')->default(false)->index();
            });
        }

        if (Schema::hasTable('banners') && ! Schema::hasColumn('banners', 'is_central_banner')) {
            Schema::table('banners', function (Blueprint $table) {
                $table->boolean('is_central_banner')->default(false)->index();
            });
        }
    }

    public function down(): void
    {
        foreach ([
            'categories' => 'is_central_category',
            'navbar_items' => 'is_central_navbar_item',
            'banners' => 'is_central_banner',
        ] as $table => $column) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropColumn($column));
            }
        }
    }
};
