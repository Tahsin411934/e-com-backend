<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('sizes')) {
            Schema::create('sizes', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('group_name', 160);
                $table->text('sizes');
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
                $table->softDeletes();

                $table->index(['status', 'deleted_at']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('sizes');
    }
};
