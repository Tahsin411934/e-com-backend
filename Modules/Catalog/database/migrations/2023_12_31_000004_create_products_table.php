<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('brand_id')->nullable();
                $table->string('name', 220);
                $table->string('slug', 240)->unique();
                $table->string('short_description', 500)->nullable();
                $table->longText('description')->nullable();
                $table->enum('product_type', ['physical','digital','service','bundle'])->default('physical');
                $table->enum('status', ['draft','active','archived'])->default('draft');
                $table->enum('visibility', ['public','hidden','private'])->default('public');
                $table->string('seo_title')->nullable();
                $table->string('seo_description', 500)->nullable();
                $table->timestamp('published_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('brand_id')->references('id')->on('brands')->onDelete('set null');

                $table->index(['brand_id', 'status']);
                $table->index(['status', 'deleted_at', 'visibility', 'published_at']);
            });

            $driver = DB::connection()->getDriverName();
            if ($driver !== 'sqlite') {
                DB::statement('ALTER TABLE products ADD FULLTEXT ft_products_search (name, short_description, description)');
            }
        }
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};
