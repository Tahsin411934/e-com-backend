<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('banners', 'primary_btn_color')) {
            Schema::table('banners', function (Blueprint $table) {
                $table->string('primary_btn_color', 50)->nullable()->after('primary_btn_url');
                $table->string('primary_btn_text_color', 50)->nullable()->after('primary_btn_color');
                $table->string('secondary_btn_color', 50)->nullable()->after('secondary_btn_url');
                $table->string('secondary_btn_text_color', 50)->nullable()->after('secondary_btn_color');
            });
        }
    }

    public function down()
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn([
                'primary_btn_color',
                'primary_btn_text_color',
                'secondary_btn_color',
                'secondary_btn_text_color',
            ]);
        });
    }
};
