<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->decimal('amount', 19, 4);
            $table->string('reason', 500)->nullable();
            $table->enum('status', ['pending','processed','failed'])->default('pending');
            $table->dateTime('processed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['order_id', 'created_at']);
            $table->index(['payment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};