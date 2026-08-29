<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders');
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->foreignId('store_id')->constrained('stores');
            $table->foreignId('created_by')->constrained('users');
            $table->enum('status', ['draft', 'returned', 'partially_refunded', 'refunded', 'cancelled'])->default('draft');
            $table->enum('refund_status', ['pending', 'partial', 'full'])->default('pending');
            $table->decimal('total_refund_amount', 15, 2)->default(0.00);
            $table->text('reason')->nullable();
            $table->date('return_date');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'deleted_at', 'created_at']);
            $table->index(['supplier_id', 'created_at']);
            $table->index(['purchase_order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_returns');
    }
};
