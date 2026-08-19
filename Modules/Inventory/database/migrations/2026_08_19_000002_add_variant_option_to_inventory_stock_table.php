<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_stock', function (Blueprint $table) {
            $table->dropUnique(['location_id', 'variant_id']);
            $table->unsignedBigInteger('variant_option_id')->nullable()->after('variant_id');
            $table->index('variant_option_id');
            $table->unique(['location_id', 'variant_id', 'variant_option_id'], 'inventory_stock_location_variant_option_unique');
            $table->foreign('variant_option_id')
                ->references('id')
                ->on('variant_options')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_stock', function (Blueprint $table) {
            $table->dropForeign(['variant_option_id']);
            $table->dropUnique('inventory_stock_location_variant_option_unique');
            $table->dropIndex(['variant_option_id']);
            $table->dropColumn('variant_option_id');
            $table->unique(['location_id', 'variant_id']);
        });
    }
};
