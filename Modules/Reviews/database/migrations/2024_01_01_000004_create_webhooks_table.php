<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160);
            $table->string('url', 500);
            $table->string('secret', 255)->nullable();
            $table->json('events')->nullable();
            $table->enum('status', ['active', 'inactive', 'failed'])->default('active');
            $table->unsignedInteger('retry_count')->default(3);
            $table->unsignedInteger('timeout_seconds')->default(30);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhooks');
    }
};