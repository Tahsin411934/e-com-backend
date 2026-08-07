<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('provider', 80);
            $table->string('provider_payment_id', 180)->nullable();
            $table->enum('method', ['card','cash','bank_transfer','wallet','cod','gift_card','other']);
            $table->enum('status', ['pending','authorized','captured','failed','cancelled','refunded'])->default('pending');
            $table->decimal('amount', 19, 4);
            $table->char('currency_code', 3)->default('USD');
            $table->dateTime('paid_at')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['order_id', 'status']);
            $table->index(['provider', 'provider_payment_id']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};