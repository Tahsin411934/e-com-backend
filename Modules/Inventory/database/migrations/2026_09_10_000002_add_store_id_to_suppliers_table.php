<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SaaS: suppliers belong to a store (tenant) so store owners only see
     * their own suppliers. Nullable so legacy / platform suppliers remain.
     */
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->foreignId('store_id')
                ->nullable()
                ->after('id')
                ->constrained('stores')
                ->nullOnDelete();

            $table->index(['store_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('store_id');
            $table->dropIndex(['store_id', 'status']);
        });
    }
};