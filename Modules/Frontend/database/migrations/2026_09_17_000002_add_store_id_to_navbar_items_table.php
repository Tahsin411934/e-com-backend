<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('navbar_items', function (Blueprint $table) {
            // NULL store_id = global/platform navbar item; owners' items are scoped to their store.
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
        Schema::table('navbar_items', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropIndex(['store_id']);
            $table->dropColumn('store_id');
        });
    }
};
