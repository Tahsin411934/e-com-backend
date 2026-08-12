<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('account_categories')->nullOnDelete();
            $table->string('name', 150);
            $table->string('slug', 180)->unique();
            $table->enum('type', ['income', 'expense', 'asset', 'liability', 'equity', 'cost_of_goods_sold'])->default('expense');
            $table->string('system_key', 100)->nullable()->unique();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'is_active', 'deleted_at']);
            $table->index(['parent_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_categories');
    }
};
