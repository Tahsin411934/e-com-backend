<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 40)->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('store_id')->nullable()->constrained('stores')->nullOnDelete();
            $table->enum('source', ['web','mobile','pos','admin','marketplace'])->default('web');
            $table->enum('status', ['pending','confirmed','processing','ready','completed','cancelled','refunded'])->default('pending');
            $table->enum('payment_status', ['unpaid','authorized','paid','partially_refunded','refunded','failed'])->default('unpaid');
            $table->enum('fulfillment_status', ['unfulfilled','partial','fulfilled','returned'])->default('unfulfilled');
            $table->char('currency_code', 3)->default('USD');
            $table->decimal('subtotal', 19, 4)->default(0);
            $table->decimal('discount_total', 19, 4)->default(0);
            $table->decimal('tax_total', 19, 4)->default(0);
            $table->decimal('shipping_total', 19, 4)->default(0);
            $table->decimal('grand_total', 19, 4)->default(0);
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->foreignId('billing_address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->foreignId('shipping_address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->string('customer_note', 1000)->nullable();
            $table->dateTime('placed_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'created_at']);
            $table->index(['store_id', 'status', 'deleted_at', 'created_at']);
            $table->index(['status', 'payment_status', 'deleted_at', 'created_at']);
            $table->index(['source', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};