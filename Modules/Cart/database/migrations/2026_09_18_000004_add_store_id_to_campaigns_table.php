<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SaaS: campaigns belong to a storefront.
     *
     * Same convention as banners/navbar_items: NULL store_id = global/platform
     * campaign (visible on every storefront); a store row is only served on
     * that store's storefront. Admin-created campaigns are auto-stamped with
     * the creator's store (BelongsToStore trait).
     *
     * NOTE: the global slug unique index is kept — the admin service generates
     * slugs with a random suffix, so collisions are impractical. If owners
     * ever need identical campaign names/slugs, move to unique(slug, store_id).
     */
    public function up(): void
    {
        if (Schema::hasColumn('campaigns', 'store_id')) {
            return;
        }

        Schema::table('campaigns', function (Blueprint $table) {
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
        if (! Schema::hasColumn('campaigns', 'store_id')) {
            return;
        }

        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropIndex(['store_id']);
            $table->dropColumn('store_id');
        });
    }
};