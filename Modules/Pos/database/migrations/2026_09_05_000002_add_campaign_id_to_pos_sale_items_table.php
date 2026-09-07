<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * POS sale items now record the campaign (if any) that supplied the discount,
     * so a sale can be traced to the exact campaign that priced it.
     */
    public function up(): void
    {
        Schema::table('pos_sale_items', function (Blueprint $table) {
            $table->foreignId('campaign_id')
                ->nullable()
                ->after('variant_option_id')
                ->constrained('campaigns')
                ->nullOnDelete();
            $table->enum('discount_source', ['variant', 'campaign'])
                ->default('variant')
                ->after('campaign_id')
                ->comment('campaign or variant - which one supplied the discount');
        });
    }

    public function down(): void
    {
        Schema::table('pos_sale_items', function (Blueprint $table) {
            $table->dropColumn('discount_source');
            $table->dropConstrainedForeignId('campaign_id');
        });
    }
};
