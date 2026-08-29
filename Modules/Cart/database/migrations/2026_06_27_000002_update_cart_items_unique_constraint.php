<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['cart_id']);
            $table->dropForeign(['variant_id']);
            $table->dropForeign(['variant_option_id']);

            // Drop the old unique constraint
            $table->dropUnique(['cart_id', 'variant_id']);

            // Add new unique constraint including variant_option_id
            $table->unique(['cart_id', 'variant_id', 'variant_option_id'], 'cart_items_cart_id_variant_id_option_unique');

            // Re-add foreign key constraints
            $table->foreign('cart_id')->references('id')->on('carts')->onDelete('cascade');
            $table->foreign('variant_id')->references('id')->on('product_variants')->onDelete('cascade');
            $table->foreign('variant_option_id')->references('id')->on('variant_options')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            // Drop foreign key constraints
            $table->dropForeign(['cart_id']);
            $table->dropForeign(['variant_id']);
            $table->dropForeign(['variant_option_id']);

            // Drop the new unique constraint
            $table->dropUnique('cart_items_cart_id_variant_id_option_unique');

            // Restore the old unique constraint
            $table->unique(['cart_id', 'variant_id']);

            // Re-add foreign key constraints
            $table->foreign('cart_id')->references('id')->on('carts')->onDelete('cascade');
            $table->foreign('variant_id')->references('id')->on('product_variants')->onDelete('cascade');
            $table->foreign('variant_option_id')->references('id')->on('variant_options')->onDelete('set null');
        });
    }
};
