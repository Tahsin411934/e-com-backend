<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('homepage_ctas')) {
            Schema::create('homepage_ctas', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('title', 255);
                $table->string('subtitle', 500)->nullable();
                $table->text('description')->nullable();
                $table->string('image', 255)->nullable();
                $table->string('button_text', 255);
                $table->string('button_link', 500);
                $table->string('background_color', 20)->default('#f8f9fa');
                $table->string('text_color', 20)->default('#1f2937');
                $table->string('button_color', 20)->default('#1e3a8a');
                $table->string('button_text_color', 20)->default('#ffffff');
                $table->integer('sort_order')->default(0);
                $table->string('status', 20)->default('active');
                $table->timestamps();
                $table->softDeletes();

                $table->index(['status', 'deleted_at']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('homepage_ctas');
    }
};
