<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('session_id', 120)->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->enum('status', ['active', 'converted', 'abandoned'])->default('active');
            $table->timestamps();
            $table->dateTime('expires_at')->nullable();
            $table->softDeletes();

            $table->index(['user_id', 'status', 'deleted_at', 'updated_at']);
            $table->index(['session_id', 'status']);
            $table->index('expires_at');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
