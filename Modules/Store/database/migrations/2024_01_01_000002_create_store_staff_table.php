<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('staff_code', 40)->nullable();
            $table->enum('status', ['active', 'inactive', 'terminated'])->default('active');
            $table->date('hired_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['store_id', 'user_id']);
            $table->unique(['store_id', 'staff_code']);
            $table->index(['store_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_staff');
    }
};