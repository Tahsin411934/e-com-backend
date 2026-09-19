<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->unique(['store_id', 'slug'], 'campaigns_store_slug_unique');
        });

        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropUnique('campaigns_slug_unique');
        });
    }

    public function down(): void
    {
        if (DB::table('campaigns')->select('slug')->groupBy('slug')->havingRaw('COUNT(*) > 1')->exists()) {
            throw new RuntimeException('Cannot restore global campaign slug uniqueness while duplicate slugs exist.');
        }

        Schema::table('campaigns', function (Blueprint $table) {
            $table->unique('slug', 'campaigns_slug_unique');
            $table->dropUnique('campaigns_store_slug_unique');
        });
    }
};

