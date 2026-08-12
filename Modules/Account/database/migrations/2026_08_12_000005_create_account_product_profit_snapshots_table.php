<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_product_profit_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained('orders')->cascadeOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->cascadeOnDelete();
            $table->foreignId('pos_sale_id')->nullable()->constrained('pos_sales')->cascadeOnDelete();
            $table->foreignId('pos_sale_item_id')->nullable()->constrained('pos_sale_items')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->foreignId('store_id')->nullable()->constrained('stores')->nullOnDelete();
            $table->string('sku', 100)->nullable();
            $table->string('product_name', 220);
            $table->string('variant_name', 220)->nullable();
            $table->decimal('quantity', 19, 4);
            $table->decimal('unit_cost', 19, 4)->default(0);
            $table->decimal('unit_sale_price', 19, 4)->default(0);
            $table->decimal('gross_sales', 19, 4)->default(0);
            $table->decimal('discount_total', 19, 4)->default(0);
            $table->decimal('tax_total', 19, 4)->default(0);
            $table->decimal('net_sales', 19, 4)->default(0);
            $table->decimal('cost_total', 19, 4)->default(0);
            $table->decimal('gross_profit', 19, 4)->default(0);
            $table->decimal('profit_margin', 8, 4)->default(0);
            $table->char('currency_code', 3)->default('BDT');
            $table->dateTime('sold_at');
            $table->timestamps();
            $table->softDeletes();

            $table->unique('order_item_id', 'account_profit_order_item_unique');
            $table->unique('pos_sale_item_id', 'account_profit_pos_sale_item_unique');
            $table->index(['product_id', 'sold_at']);
            $table->index(['variant_id', 'sold_at']);
            $table->index(['store_id', 'sold_at']);
            $table->index(['gross_profit', 'sold_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_product_profit_snapshots');
    }
};
