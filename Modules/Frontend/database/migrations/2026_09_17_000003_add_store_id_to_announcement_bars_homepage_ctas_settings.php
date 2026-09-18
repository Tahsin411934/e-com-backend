<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcement_bars', function (Blueprint $table) {
            // NULL store_id = global/platform announcement bar; owners' bars are scoped to their store.
            $table->unsignedBigInteger('store_id')->nullable();
            $table->index('store_id');
            $table->foreign('store_id')
                ->references('id')
                ->on('stores')
                ->nullOnDelete();
        });

        Schema::table('homepage_ctas', function (Blueprint $table) {
            // NULL store_id = global/platform CTA; owners' CTAs are scoped to their store.
            $table->unsignedBigInteger('store_id')->nullable();
            $table->index('store_id');
            $table->foreign('store_id')
                ->references('id')
                ->on('stores')
                ->nullOnDelete();
        });

        Schema::table('settings', function (Blueprint $table) {
            // NULL store_id = global/platform setting; store rows override global values per key.
            // The global unique index on `key` must go — a store row legitimately
            // reuses the same key — and uniqueness is enforced per (key, store_id).
            $table->dropUnique('settings_key_unique');
            $table->unsignedBigInteger('store_id')->nullable();
            $table->index('store_id');
            $table->foreign('store_id')
                ->references('id')
                ->on('stores')
                ->nullOnDelete();
            $table->unique(['key', 'store_id']);
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique(['key', 'store_id']);
            $table->dropForeign(['store_id']);
            $table->dropIndex(['store_id']);
            $table->dropColumn('store_id');
            $table->unique('key');
        });

        Schema::table('homepage_ctas', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropIndex(['store_id']);
            $table->dropColumn('store_id');
        });

        Schema::table('announcement_bars', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropIndex(['store_id']);
            $table->dropColumn('store_id');
        });
    }
};
