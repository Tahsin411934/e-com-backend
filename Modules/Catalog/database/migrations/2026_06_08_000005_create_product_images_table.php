<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('product_images')) {
            Schema::create('product_images', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('variant_id')->nullable();
                $table->string('image_url', 500);
                $table->string('alt_text', 255)->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                $table->foreign('variant_id')->references('id')->on('product_variants')->onDelete('set null');

                $table->index(['product_id', 'sort_order']);
                $table->index('variant_id');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('product_images');
    }
};
