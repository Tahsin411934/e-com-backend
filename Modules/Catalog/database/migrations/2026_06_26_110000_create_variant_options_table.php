<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('variant_options', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('product_variant_id');
            $table->string('color_name', 100);
            $table->string('color_code', 20)->nullable();  // e.g. "#FF0000"
            $table->string('image_url', 255)->nullable();   // color-specific image
            $table->decimal('price_adjustment', 12, 4)->default(0); // extra price for this color
            $table->integer('stock')->default(0);
            $table->integer('sort_order')->default(0);
            $table->string('status', 30)->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('product_variant_id')
                ->references('id')
                ->on('product_variants')
                ->onDelete('cascade');

            $table->index(['product_variant_id', 'color_name']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variant_options');
    }
};
