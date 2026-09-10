<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SaaS: brands & categories belong to a store (tenant). Nullable so
     * legacy / platform-level reference data remains visible globally.
     */
    public function up(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->foreignId('store_id')
                ->nullable()
                ->after('id')
                ->constrained('stores')
                ->nullOnDelete();

            $table->index(['store_id', 'status']);
        });

        Schema::table('categories', function (Blueprint $table) {
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
        Schema::table('brands', function (Blueprint $table) {
            $table->dropConstrainedForeignId('store_id');
            $table->dropIndex(['store_id', 'status']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('store_id');
            $table->dropIndex(['store_id', 'status']);
        });
    }
};
