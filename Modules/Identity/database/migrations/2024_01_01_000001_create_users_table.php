<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->char('public_id', 36);
                $table->string('first_name', 100)->nullable();
                $table->string('last_name', 100)->nullable();
                $table->string('email', 255)->unique();
                $table->string('phone', 32)->nullable()->unique();
                $table->string('password_hash', 255);
                $table->enum('status', ['active', 'inactive', 'blocked', 'deleted'])->default('active');
                $table->dateTime('email_verified_at')->nullable();
                $table->dateTime('phone_verified_at')->nullable();
                $table->dateTime('last_login_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique('public_id');
                $table->index(['status', 'deleted_at', 'created_at']);
                $table->index('last_login_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
