<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_investments', function (Blueprint $table) {
            // Relax the hard-coded ENUM so investment types can be added dynamically
            // via the Account Categories screen (type = 'investment').
            $table->string('investment_type', 120)->default('other')->change();
        });
    }

    public function down(): void
    {
        Schema::table('account_investments', function (Blueprint $table) {
            $table->enum('investment_type', [
                'marketing', 'equipment', 'property', 'stocks', 'fixed_deposit', 'business', 'other'
            ])->default('marketing')->change();
        });
    }
};
