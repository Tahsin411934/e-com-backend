<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('variant_options', function (Blueprint $table) {
            $table->string('sku', 100)->nullable()->unique()->after('color_code');
            $table->string('barcode', 100)->nullable()->unique()->after('sku');
            $table->decimal('sale_price', 19, 4)->nullable()->after('price_adjustment');
            $table->decimal('compare_at_price', 19, 4)->nullable()->after('sale_price');
        });
    }

    public function down(): void
    {
        Schema::table('variant_options', function (Blueprint $table) {
            $table->dropUnique(['sku']);
            $table->dropUnique(['barcode']);
            $table->dropColumn(['sku', 'barcode', 'sale_price', 'compare_at_price']);
        });
    }
};
