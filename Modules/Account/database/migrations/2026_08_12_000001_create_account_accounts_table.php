<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('code', 50)->unique();
            $table->enum('type', ['cash', 'bank', 'mobile_banking', 'card', 'gateway', 'other'])->default('cash');
            $table->char('currency_code', 3)->default('BDT');
            $table->decimal('opening_balance', 19, 4)->default(0);
            $table->decimal('current_balance', 19, 4)->default(0);
            $table->string('bank_name', 150)->nullable();
            $table->string('branch_name', 150)->nullable();
            $table->string('account_number', 100)->nullable();
            $table->string('account_holder_name', 150)->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'is_active', 'deleted_at']);
            $table->index(['is_default', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_accounts');
    }
};
