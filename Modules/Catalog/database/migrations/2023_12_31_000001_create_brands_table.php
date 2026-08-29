<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('brands')) {
            Schema::create('brands', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name', 160);
                $table->string('slug', 180)->unique();
                $table->string('logo_url', 500)->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
                $table->softDeletes();

                $table->index(['status', 'deleted_at']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('brands');
    }
};
