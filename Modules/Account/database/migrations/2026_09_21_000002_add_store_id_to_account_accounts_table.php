<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('account_accounts', 'store_id')) {
            Schema::table('account_accounts', function (Blueprint $table) {
                $table->foreignId('store_id')->nullable()->after('id')->constrained('stores')->nullOnDelete();
                $table->index(['store_id', 'is_active', 'deleted_at']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('account_accounts', 'store_id')) {
            Schema::table('account_accounts', function (Blueprint $table) {
                $table->dropForeign(['store_id']);
                $table->dropIndex(['store_id', 'is_active', 'deleted_at']);
                $table->dropColumn('store_id');
            });
        }
    }
};
