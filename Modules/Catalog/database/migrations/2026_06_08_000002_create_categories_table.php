<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->string('name', 160);
                $table->string('slug', 180)->unique();
                $table->text('description')->nullable();
                $table->string('image_url', 500)->nullable();
                $table->integer('sort_order')->default(0);
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('parent_id')->references('id')->on('categories')->onDelete('set null');

                $table->index(['parent_id', 'sort_order']);
                $table->index(['status', 'deleted_at', 'sort_order']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('categories');
    }
};
