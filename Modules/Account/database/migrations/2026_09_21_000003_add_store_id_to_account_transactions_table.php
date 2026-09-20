<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void { if (!Schema::hasColumn('account_transactions', 'store_id')) Schema::table('account_transactions', fn (Blueprint $t) => [$t->foreignId('store_id')->nullable()->after('id')->constrained('stores')->nullOnDelete(), $t->index(['store_id', 'type', 'status', 'transaction_date'])]); }
    public function down(): void { if (Schema::hasColumn('account_transactions', 'store_id')) Schema::table('account_transactions', function (Blueprint $t) { $t->dropForeign(['store_id']); $t->dropIndex(['store_id', 'type', 'status', 'transaction_date']); $t->dropColumn('store_id'); }); }
};
