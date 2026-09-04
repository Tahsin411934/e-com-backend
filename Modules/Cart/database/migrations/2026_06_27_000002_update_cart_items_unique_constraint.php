<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $sqlite = DB::connection()->getDriverName() === 'sqlite';

        Schema::table('cart_items', function (Blueprint $table) use ($sqlite) {
            // Drop foreign key constraints first (not supported on SQLite)
            if (! $sqlite) {
                $table->dropForeign(['cart_id']);
                $table->dropForeign(['variant_id']);
                $table->dropForeign(['variant_option_id']);
            }

            // Drop the old unique constraint
            $table->dropUnique(['cart_id', 'variant_id']);

            // Add new unique constraint including variant_option_id
            $table->unique(['cart_id', 'variant_id', 'variant_option_id'], 'cart_items_cart_id_variant_id_option_unique');

            // Re-add foreign key constraints (kept as-is on SQLite)
            if (! $sqlite) {
                $table->foreign('cart_id')->references('id')->on('carts')->onDelete('cascade');
                $table->foreign('variant_id')->references('id')->on('product_variants')->onDelete('cascade');
                $table->foreign('variant_option_id')->references('id')->on('variant_options')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        $sqlite = DB::connection()->getDriverName() === 'sqlite';

        Schema::table('cart_items', function (Blueprint $table) use ($sqlite) {
            // Drop foreign key constraints (not supported on SQLite)
            if (! $sqlite) {
                $table->dropForeign(['cart_id']);
                $table->dropForeign(['variant_id']);
                $table->dropForeign(['variant_option_id']);
            }

            // Drop the new unique constraint
            $table->dropUnique('cart_items_cart_id_variant_id_option_unique');

            // Restore the old unique constraint
            $table->unique(['cart_id', 'variant_id']);

            // Re-add foreign key constraints (kept as-is on SQLite)
            if (! $sqlite) {
                $table->foreign('cart_id')->references('id')->on('carts')->onDelete('cascade');
                $table->foreign('variant_id')->references('id')->on('product_variants')->onDelete('cascade');
                $table->foreign('variant_option_id')->references('id')->on('variant_options')->onDelete('set null');
            }
        });
    }
};
