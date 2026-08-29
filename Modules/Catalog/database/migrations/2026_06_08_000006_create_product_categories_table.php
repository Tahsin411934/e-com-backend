<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('product_categories')) {
            Schema::create('product_categories', function (Blueprint $table) {
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('category_id');
                $table->softDeletes();
                $table->primary(['product_id', 'category_id']);

                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');

                $table->index(['category_id', 'product_id']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('product_categories');
    }
};
