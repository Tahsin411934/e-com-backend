<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained('inventory_locations')->cascadeOnDelete();
            $table->unsignedBigInteger('variant_id');
            $table->integer('quantity_on_hand')->default(0);
            $table->integer('quantity_reserved')->default(0);
            $table->integer('reorder_point')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['location_id', 'variant_id']);
            $table->index('variant_id');
            $table->index(['location_id', 'quantity_on_hand', 'quantity_reserved'], 'inv_stock_avail_idx');
        });

        if (Schema::hasTable('product_variants')) {
            Schema::table('inventory_stock', function (Blueprint $table) {
                $table->foreign('variant_id')->references('id')->on('product_variants')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_stock');
    }
};
