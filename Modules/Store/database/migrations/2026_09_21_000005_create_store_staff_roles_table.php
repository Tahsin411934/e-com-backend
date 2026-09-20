<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_staff_roles', function (Blueprint $table) {
            $table->foreignId('store_staff_id')->constrained('store_staff')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->primary(['store_staff_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_staff_roles');
    }
};
