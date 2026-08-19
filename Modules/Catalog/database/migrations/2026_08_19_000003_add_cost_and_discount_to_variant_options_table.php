<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('variant_options', function (Blueprint $table) {
            $table->decimal('cost_price', 19, 4)->nullable()->after('price_adjustment');
            $table->decimal('discount_percent', 5, 2)->default(0)->after('compare_at_price');
        });
    }

    public function down(): void
    {
        Schema::table('variant_options', function (Blueprint $table) {
            $table->dropColumn(['cost_price', 'discount_percent']);
        });
    }
};
