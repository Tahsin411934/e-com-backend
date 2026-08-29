<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('products', 'unit_id') && ! Schema::hasColumn('products', 'size_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedBigInteger('unit_id')->nullable()->after('brand_id');
                $table->unsignedBigInteger('size_id')->nullable()->after('unit_id');

                $table->foreign('unit_id')->references('id')->on('units')->onDelete('set null');
                $table->foreign('size_id')->references('id')->on('sizes')->onDelete('set null');

                $table->index(['unit_id']);
                $table->index(['size_id']);
            });
        }
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropForeign(['size_id']);
            $table->dropColumn(['unit_id', 'size_id']);
        });
    }
};
