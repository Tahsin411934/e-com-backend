<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('products', 'tax_rate_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedBigInteger('tax_rate_id')->nullable()->after('size_id');
                $table->foreign('tax_rate_id')->references('id')->on('tax_rates')->onDelete('set null');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('products', 'tax_rate_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropForeign(['tax_rate_id']);
                $table->dropColumn('tax_rate_id');
            });
        }
    }
};
