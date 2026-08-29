<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('announcement_bars')) {
            Schema::create('announcement_bars', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('left_text', 500)->nullable();
                $table->string('center_text', 500)->nullable();
                $table->string('right_text', 500)->nullable();
                $table->string('background_color', 20)->default('#0F1115');
                $table->string('text_color', 20)->default('#ffffff');
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
        Schema::dropIfExists('announcement_bars');
    }
};
