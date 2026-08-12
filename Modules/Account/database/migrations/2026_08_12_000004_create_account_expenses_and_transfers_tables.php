<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_no', 60)->unique();
            $table->foreignId('account_id')->constrained('account_accounts')->restrictOnDelete();
            $table->foreignId('category_id')->constrained('account_categories')->restrictOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained('account_transactions')->nullOnDelete();
            $table->decimal('amount', 19, 4);
            $table->char('currency_code', 3)->default('BDT');
            $table->date('expense_date');
            $table->string('vendor_name', 180)->nullable();
            $table->string('reference_no', 120)->nullable();
            $table->string('attachment_path')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['account_id', 'expense_date']);
            $table->index(['category_id', 'expense_date']);
            $table->index(['deleted_at', 'expense_date']);
        });

        Schema::create('account_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_no', 60)->unique();
            $table->foreignId('from_account_id')->constrained('account_accounts')->restrictOnDelete();
            $table->foreignId('to_account_id')->constrained('account_accounts')->restrictOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained('account_transactions')->nullOnDelete();
            $table->decimal('amount', 19, 4);
            $table->decimal('transfer_fee', 19, 4)->default(0);
            $table->char('currency_code', 3)->default('BDT');
            $table->dateTime('transferred_at');
            $table->string('reference_no', 120)->nullable();
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['from_account_id', 'transferred_at']);
            $table->index(['to_account_id', 'transferred_at']);
            $table->index(['deleted_at', 'transferred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_transfers');
        Schema::dropIfExists('account_expenses');
    }
};
