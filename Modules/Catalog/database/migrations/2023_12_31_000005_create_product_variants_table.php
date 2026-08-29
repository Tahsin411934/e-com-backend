<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('product_variants')) {
            Schema::create('product_variants', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('product_id');
                $table->string('sku', 100)->unique();
                $table->string('barcode', 100)->nullable()->unique();
                $table->string('name', 220);
                $table->json('attributes')->nullable();
                $table->decimal('cost_price', 19, 4)->default(0.0000);
                $table->decimal('sale_price', 19, 4);
                $table->decimal('compare_at_price', 19, 4)->nullable();
                $table->unsignedInteger('weight_grams')->nullable();
                $table->unsignedInteger('length_mm')->nullable();
                $table->unsignedInteger('width_mm')->nullable();
                $table->unsignedInteger('height_mm')->nullable();
                $table->boolean('track_inventory')->default(true);
                $table->boolean('allow_backorder')->default(false);
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

                $table->index(['product_id', 'status', 'deleted_at']);
                $table->index('sale_price');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('product_variants');
    }
};
