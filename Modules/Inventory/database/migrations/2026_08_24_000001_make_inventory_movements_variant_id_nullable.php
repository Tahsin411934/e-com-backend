<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // variant_id was NOT NULL + required FK, but purchase-return movements
        // (aggregate entries) have no single variant and used to send a fake id = 0,
        // which violated the foreign key. Make it nullable so return/summary
        // movements can be stored without a variant.
        if (! Schema::hasColumn('inventory_movements', 'variant_id')) {
            return;
        }

        $sqlite = DB::connection()->getDriverName() === 'sqlite';

        Schema::table('inventory_movements', function (Blueprint $table) use ($sqlite) {
            if (! $sqlite) {
                $table->dropForeign('inventory_movements_variant_id_foreign');
            }

            $table->unsignedBigInteger('variant_id')->nullable()->change();
        });

        if (! $sqlite && Schema::hasTable('product_variants')) {
            Schema::table('inventory_movements', function (Blueprint $table) {
                $table->foreign('variant_id')->references('id')->on('product_variants');
            });
        }
    }

    public function down(): void
    {
        $sqlite = DB::connection()->getDriverName() === 'sqlite';

        Schema::table('inventory_movements', function (Blueprint $table) use ($sqlite) {
            if (! $sqlite) {
                $table->dropForeign('inventory_movements_variant_id_foreign');
            }

            $table->unsignedBigInteger('variant_id')->change();

            if (! $sqlite) {
                $table->foreign('variant_id')->references('id')->on('product_variants');
            }
        });
    }
};
