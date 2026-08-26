<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('campaign_products', 'sort_order')) {
            Schema::table('campaign_products', function (Blueprint $table) {
                $table->unsignedInteger('sort_order')->default(0)->after('discount_value');
                $table->index(['campaign_id', 'sort_order']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('campaign_products', 'sort_order')) {
            Schema::table('campaign_products', function (Blueprint $table) {
                $table->dropIndex(['campaign_id', 'sort_order']);
                $table->dropColumn('sort_order');
            });
        }
    }
};