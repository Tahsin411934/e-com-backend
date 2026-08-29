<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_registers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->string('name', 160);
            $table->string('code', 50)->unique();
            $table->enum('type', ['counter', 'mobile', 'kiosk'])->default('counter');
            $table->enum('status', ['active', 'inactive', 'offline'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['store_id', 'status', 'deleted_at']);
        });

        if (Schema::hasTable('stores')) {
            Schema::table('pos_registers', function (Blueprint $table) {
                $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_registers');
    }
};
