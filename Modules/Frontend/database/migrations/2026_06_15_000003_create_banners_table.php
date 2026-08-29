<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('banners')) {
            Schema::create('banners', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('banner_image', 255)->nullable();
                $table->string('title', 255);
                $table->string('subtitle', 500)->nullable();
                $table->string('smtag', 255)->nullable();
                $table->string('primary_btn', 255)->nullable();
                $table->string('primary_btn_url', 500)->nullable();
                $table->string('secondary_btn', 255)->nullable();
                $table->string('secondary_btn_url', 500)->nullable();
                $table->integer('sort_order')->default(0);
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
                $table->softDeletes();

                $table->index(['status', 'deleted_at']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('banners');
    }
};
