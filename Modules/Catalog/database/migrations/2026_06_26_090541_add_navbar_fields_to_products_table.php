<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'navbar_item_id')) {
                $table->unsignedBigInteger('navbar_item_id')->nullable()->after('category_id');
                $table->foreign('navbar_item_id')
                    ->references('id')
                    ->on('navbar_items')
                    ->onDelete('set null');
            }

            if (! Schema::hasColumn('products', 'subnavbar_item_id')) {
                $table->unsignedBigInteger('subnavbar_item_id')->nullable()->after('navbar_item_id');
                $table->foreign('subnavbar_item_id')
                    ->references('id')
                    ->on('subnavbar_items')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'navbar_item_id')) {
                $table->dropForeign(['navbar_item_id']);
                $table->dropColumn('navbar_item_id');
            }
            if (Schema::hasColumn('products', 'subnavbar_item_id')) {
                $table->dropForeign(['subnavbar_item_id']);
                $table->dropColumn('subnavbar_item_id');
            }
        });
    }
};
