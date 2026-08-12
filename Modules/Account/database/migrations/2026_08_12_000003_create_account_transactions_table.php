<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_no', 60)->unique();
            $table->enum('type', ['sale', 'purchase', 'expense', 'refund', 'transfer', 'adjustment', 'opening_balance', 'other']);
            $table->enum('status', ['draft', 'posted', 'void'])->default('posted');
            $table->nullableMorphs('source');
            $table->char('currency_code', 3)->default('BDT');
            $table->decimal('total_debit', 19, 4)->default(0);
            $table->decimal('total_credit', 19, 4)->default(0);
            $table->dateTime('transaction_date');
            $table->string('reference_no', 120)->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('posted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'status', 'transaction_date']);
            $table->index(['status', 'deleted_at', 'transaction_date']);
            $table->index(['reference_no']);
        });

        Schema::create('account_transaction_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('account_transactions')->cascadeOnDelete();
            $table->foreignId('account_id')->nullable()->constrained('account_accounts')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('account_categories')->nullOnDelete();
            $table->enum('entry_type', ['debit', 'credit']);
            $table->decimal('amount', 19, 4);
            $table->string('memo', 500)->nullable();
            $table->timestamps();

            $table->index(['account_id', 'entry_type']);
            $table->index(['category_id', 'entry_type']);
            $table->index(['transaction_id', 'entry_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_transaction_lines');
        Schema::dropIfExists('account_transactions');
    }
};
