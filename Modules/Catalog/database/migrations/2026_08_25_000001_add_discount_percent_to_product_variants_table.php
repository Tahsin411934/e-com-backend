<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('product_variants', 'discount_percent')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->decimal('discount_percent', 5, 2)->default(0)->after('sale_price');
            });
        }
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('discount_percent');
        });
    }
};
