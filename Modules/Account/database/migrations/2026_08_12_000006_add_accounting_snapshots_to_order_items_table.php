<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('order_items')) {
            return;
        }

        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'unit_cost')) {
                $table->decimal('unit_cost', 19, 4)->default(0)->after('unit_price');
            }

            if (!Schema::hasColumn('order_items', 'cost_total')) {
                $table->decimal('cost_total', 19, 4)->default(0)->after('line_total');
            }

            if (!Schema::hasColumn('order_items', 'gross_profit')) {
                $table->decimal('gross_profit', 19, 4)->default(0)->after('cost_total');
            }

            if (!Schema::hasColumn('order_items', 'profit_margin')) {
                $table->decimal('profit_margin', 8, 4)->default(0)->after('gross_profit');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('order_items')) {
            return;
        }

        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'profit_margin')) {
                $table->dropColumn('profit_margin');
            }

            if (Schema::hasColumn('order_items', 'gross_profit')) {
                $table->dropColumn('gross_profit');
            }

            if (Schema::hasColumn('order_items', 'cost_total')) {
                $table->dropColumn('cost_total');
            }

            if (Schema::hasColumn('order_items', 'unit_cost')) {
                $table->dropColumn('unit_cost');
            }
        });
    }
};
