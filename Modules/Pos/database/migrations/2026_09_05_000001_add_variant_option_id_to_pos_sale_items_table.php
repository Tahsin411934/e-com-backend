<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * POS sale items now record the exact color option when a variant has
     * multiple options, so a sale can be traced to variant+color precisely.
     */
    public function up(): void
    {
        Schema::table('pos_sale_items', function (Blueprint $table) {
            $table->foreignId('variant_option_id')
                ->nullable()
                ->after('variant_id')
                ->constrained('variant_options')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pos_sale_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('variant_option_id');
        });
    }
};
