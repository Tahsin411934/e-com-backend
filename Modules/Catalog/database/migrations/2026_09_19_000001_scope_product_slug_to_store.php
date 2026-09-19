<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unique(['store_id', 'slug']);
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_slug_unique');
        });
    }

    public function down(): void
    {
        // Refuse rollback before changing indexes if tenants now share slugs.
        if (DB::table('products')->select('slug')->groupBy('slug')->havingRaw('COUNT(*) > 1')->exists()) {
            throw new RuntimeException('Cannot restore global product slug uniqueness while duplicate slugs exist.');
        }

        Schema::table('products', function (Blueprint $table) {
            $table->unique('slug');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['store_id', 'slug']);
        });
    }
};
