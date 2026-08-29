<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('register_id');
            $table->unsignedBigInteger('shift_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('receipt_number', 50)->unique();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('cash_amount', 15, 2)->default(0);
            $table->decimal('card_amount', 15, 2)->default(0);
            $table->decimal('other_amount', 15, 2)->default(0);
            $table->decimal('change_amount', 15, 2)->default(0);
            $table->enum('payment_status', ['paid', 'partial', 'pending', 'refunded'])->default('paid');
            $table->enum('status', ['completed', 'voided', 'refunded'])->default('completed');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['register_id', 'shift_id', 'user_id', 'status', 'deleted_at']);
            $table->index('receipt_number');
        });

        if (Schema::hasTable('pos_registers')) {
            Schema::table('pos_sales', function (Blueprint $table) {
                $table->foreign('register_id')->references('id')->on('pos_registers')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('pos_shifts')) {
            Schema::table('pos_sales', function (Blueprint $table) {
                $table->foreign('shift_id')->references('id')->on('pos_shifts')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('pos_sales', function (Blueprint $table) {
                $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('pos_sales', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_sales');
    }
};
