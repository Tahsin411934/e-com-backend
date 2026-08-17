<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'order_column')) {
            Schema::table('products', function (Blueprint $table) {
                $table->integer('order_column')->default(0)->after('is_homepage');
                $table->index('order_column');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex(['order_column']);
                $table->dropColumn('order_column');
            });
        }
    }
};
