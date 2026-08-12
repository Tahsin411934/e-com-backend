<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_investments', function (Blueprint $table) {
            $table->id();
            $table->string('investment_no', 60)->unique();
            $table->string('title', 180);
            $table->foreignId('account_id')->constrained('account_accounts')->restrictOnDelete();
            $table->decimal('amount', 19, 4);
            $table->char('currency_code', 3)->default('BDT');
            $table->date('investment_date');
            $table->enum('investment_type', [
                'marketing', 'equipment', 'property', 'stocks', 'fixed_deposit', 'business', 'other'
            ])->default('marketing');
            $table->decimal('expected_return', 19, 4)->default(0);
            $table->decimal('actual_return', 19, 4)->default(0);
            $table->enum('status', ['active', 'completed', 'failed'])->default('active');
            $table->string('partner_name', 180)->nullable();
            $table->string('reference_no', 120)->nullable();
            $table->string('attachment_path')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['account_id', 'investment_date']);
            $table->index(['investment_type', 'status']);
            $table->index(['deleted_at', 'investment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_investments');
    }
};