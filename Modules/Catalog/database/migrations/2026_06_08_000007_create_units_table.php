<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('units')) {
            Schema::create('units', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name', 120);
                $table->string('slug', 140)->unique();
                $table->string('short_name', 30);
                $table->enum('type', ['quantity', 'weight', 'volume', 'length', 'area', 'time'])->default('quantity');
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
                $table->softDeletes();

                $table->index(['status', 'deleted_at']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('units');
    }
};
