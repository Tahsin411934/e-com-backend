<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_categories', function (Blueprint $table) {
            $table->enum('type', [
                'income', 'expense', 'asset', 'liability', 'equity', 'cost_of_goods_sold', 'investment'
            ])->default('expense')->change();
        });
    }

    public function down(): void
    {
        Schema::table('account_categories', function (Blueprint $table) {
            $table->enum('type', [
                'income', 'expense', 'asset', 'liability', 'equity', 'cost_of_goods_sold'
            ])->default('expense')->change();
        });
    }
};
