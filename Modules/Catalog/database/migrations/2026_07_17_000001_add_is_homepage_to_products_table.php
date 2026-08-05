<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('products', 'is_homepage')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('is_homepage')->default(false)->after('published_at');
                $table->index('is_homepage');
            });
        }
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('is_homepage');
        });
    }
};