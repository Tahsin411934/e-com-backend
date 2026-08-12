<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_daily_summaries', function (Blueprint $table) {
            $table->id();
            $table->date('summary_date');
            $table->foreignId('account_id')->nullable()->constrained('account_accounts')->cascadeOnDelete();
            $table->foreignId('store_id')->nullable()->constrained('stores')->cascadeOnDelete();
            $table->decimal('opening_balance', 19, 4)->default(0);
            $table->decimal('closing_balance', 19, 4)->default(0);
            $table->decimal('total_income', 19, 4)->default(0);
            $table->decimal('total_expense', 19, 4)->default(0);
            $table->decimal('total_sales', 19, 4)->default(0);
            $table->decimal('total_refunds', 19, 4)->default(0);
            $table->decimal('total_cogs', 19, 4)->default(0);
            $table->decimal('gross_profit', 19, 4)->default(0);
            $table->decimal('net_profit', 19, 4)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['summary_date', 'account_id', 'store_id'], 'account_daily_summary_unique');
            $table->index(['summary_date', 'store_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_daily_summaries');
    }
};
