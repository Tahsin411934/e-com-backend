<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160);
            $table->string('slug', 180)->unique();
            $table->string('email', 255)->nullable();
            $table->string('phone', 32)->nullable();
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->char('currency_code', 3)->default('USD');
            $table->string('timezone', 64)->default('UTC');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
