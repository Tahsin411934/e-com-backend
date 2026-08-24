<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Allow account_transactions.type to store 'investment' records
        // (owner/partner capital injection).
        Schema::table('account_transactions', function (Blueprint $table) {
            $table->enum('type', [
                'sale', 'purchase', 'expense', 'refund', 'transfer', 'adjustment', 'opening_balance', 'investment', 'other',
            ])->change();
        });

        // Link each posted investment back to its accounting transaction
        // (same pattern already used for account_expenses / account_transfers).
        Schema::table('account_investments', function (Blueprint $table) {
            $table->foreignId('transaction_id')
                ->nullable()
                ->after('status')
                ->constrained('account_transactions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('account_investments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('transaction_id');
        });

        Schema::table('account_transactions', function (Blueprint $table) {
            $table->enum('type', [
                'sale', 'purchase', 'expense', 'refund', 'transfer', 'adjustment', 'opening_balance', 'other',
            ])->change();
        });
    }
};