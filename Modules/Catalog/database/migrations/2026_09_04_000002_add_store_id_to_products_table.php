<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SaaS: products belong to a store (tenant). Nullable so legacy /
     * platform-level products remain visible in the public catalog.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('store_id')
                ->nullable()
                ->after('brand_id')
                ->constrained('stores')
                ->nullOnDelete();

            $table->index(['store_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('store_id');
            $table->dropIndex(['store_id', 'status']);
        });
    }
};
