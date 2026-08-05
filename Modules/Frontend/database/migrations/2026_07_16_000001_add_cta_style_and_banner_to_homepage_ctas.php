<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('homepage_ctas', 'cta_style')) {
            Schema::table('homepage_ctas', function (Blueprint $table) {
                $table->string('cta_style', 50)->default('style1')->after('id');
                $table->string('banner_image', 255)->nullable()->after('image');
                $table->string('overlay_color', 20)->nullable()->default('rgba(0,0,0,0.4)')->after('button_text_color');
                $table->string('badge_text', 100)->nullable()->after('overlay_color');
                $table->string('badge_color', 20)->nullable()->default('#ef4444')->after('badge_text');
                $table->string('secondary_button_text', 255)->nullable()->after('badge_color');
                $table->string('secondary_button_link', 500)->nullable()->after('secondary_button_text');
                $table->string('secondary_button_color', 20)->nullable()->default('#ffffff')->after('secondary_button_link');
                $table->string('secondary_button_text_color', 20)->nullable()->default('#1f2937')->after('secondary_button_color');
                $table->string('feature_icon_1', 100)->nullable()->after('secondary_button_text_color');
                $table->string('feature_text_1', 255)->nullable()->after('feature_icon_1');
                $table->string('feature_icon_2', 100)->nullable()->after('feature_text_1');
                $table->string('feature_text_2', 255)->nullable()->after('feature_icon_2');
                $table->string('feature_icon_3', 100)->nullable()->after('feature_text_2');
                $table->string('feature_text_3', 255)->nullable()->after('feature_icon_3');
            });
        }

        // Make title, button_text, button_link nullable
        Schema::table('homepage_ctas', function (Blueprint $table) {
            $table->string('title', 255)->nullable()->change();
            $table->string('button_text', 255)->nullable()->change();
            $table->string('button_link', 500)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('homepage_ctas', function (Blueprint $table) {
            $table->dropColumn([
                'cta_style',
                'banner_image',
                'overlay_color',
                'badge_text',
                'badge_color',
                'secondary_button_text',
                'secondary_button_link',
                'secondary_button_color',
                'secondary_button_text_color',
                'feature_icon_1',
                'feature_text_1',
                'feature_icon_2',
                'feature_text_2',
                'feature_icon_3',
                'feature_text_3',
            ]);
        });

        Schema::table('homepage_ctas', function (Blueprint $table) {
            $table->string('title', 255)->nullable(false)->change();
            $table->string('button_text', 255)->nullable(false)->change();
            $table->string('button_link', 500)->nullable(false)->change();
        });
    }
};