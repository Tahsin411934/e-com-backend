<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SaaS: subnavbar items belong to a storefront.
     *
     * Until now subnavbar items were only bound to a navbar item, so every
     * store displayed the platform's menu tree. With store_id they follow the
     * exact same convention as navbar_items/banners:
     *
     *  - NULL store_id = global/platform subnavbar item (visible on every storefront)
     *  - store row      = only that store's storefront shows it
     */
    public function up(): void
    {
        if (Schema::hasColumn('subnavbar_items', 'store_id')) {
            return;
        }

        Schema::table('subnavbar_items', function (Blueprint $table) {
            $table->unsignedBigInteger('store_id')->nullable()->after('id');
            $table->index('store_id');
            $table->foreign('store_id')
                ->references('id')
                ->on('stores')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('subnavbar_items', 'store_id')) {
            return;
        }

        Schema::table('subnavbar_items', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropIndex(['store_id']);
            $table->dropColumn('store_id');
        });
    }
};