<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_no', 60)->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            $table->foreignId('store_id')->nullable()->constrained('stores')->nullOnDelete();
            $table->foreignId('account_id')->constrained('account_accounts')->restrictOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained('account_transactions')->nullOnDelete();
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('payment_date');
            $table->enum('payment_method', ['cash', 'bank', 'mobile_banking', 'card', 'gateway', 'other'])->default('cash');
            $table->string('reference_no', 120)->nullable();
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['supplier_id', 'payment_date']);
            $table->index(['purchase_order_id', 'payment_date']);
            $table->index(['account_id', 'payment_date']);
            $table->index(['deleted_at', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_payments');
    }
};