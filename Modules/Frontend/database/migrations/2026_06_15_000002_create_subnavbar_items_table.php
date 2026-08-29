<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('subnavbar_items')) {
            Schema::create('subnavbar_items', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('navbar_item_id');
                $table->string('name', 160);
                $table->string('slug', 180)->unique();
                $table->string('url', 500)->nullable();
                $table->string('icon', 255)->nullable();
                $table->integer('sort_order')->default(0);
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('navbar_item_id')
                    ->references('id')
                    ->on('navbar_items')
                    ->onDelete('cascade');

                $table->index(['status', 'deleted_at']);
                $table->index('navbar_item_id');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('subnavbar_items');
    }
};
