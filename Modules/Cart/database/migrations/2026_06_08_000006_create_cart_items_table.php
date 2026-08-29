<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cart_id');
            $table->unsignedBigInteger('variant_id');
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 19, 4);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['cart_id', 'variant_id']);
            $table->index('variant_id');

            $table->foreign('cart_id')->references('id')->on('carts')->onDelete('cascade');
            $table->foreign('variant_id')->references('id')->on('product_variants');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
