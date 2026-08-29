<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('product_images', function (Blueprint $table) {
            if (! Schema::hasColumn('product_images', 'is_main')) {
                $table->boolean('is_main')->default(false)->after('sort_order');
            }
        });
    }

    public function down()
    {
        Schema::table('product_images', function (Blueprint $table) {
            if (Schema::hasColumn('product_images', 'is_main')) {
                $table->dropColumn('is_main');
            }
        });
    }
};
