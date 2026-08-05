<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('homepage_ctas', function (Blueprint $table) {
            // Button positioning & margin
            $table->string('button_position', 50)->nullable()->default('center')->after('button_text_color');
            $table->integer('button_margin_top')->nullable()->after('button_position');
            $table->integer('button_margin_bottom')->nullable()->after('button_margin_top');
            $table->integer('button_margin_left')->nullable()->after('button_margin_bottom');
            $table->integer('button_margin_right')->nullable()->after('button_margin_left');

            // Secondary button positioning & margin
            $table->string('secondary_button_position', 50)->nullable()->default('center')->after('secondary_button_text_color');
            $table->integer('secondary_button_margin_top')->nullable()->after('secondary_button_position');
            $table->integer('secondary_button_margin_bottom')->nullable()->after('secondary_button_margin_top');
            $table->integer('secondary_button_margin_left')->nullable()->after('secondary_button_margin_bottom');
            $table->integer('secondary_button_margin_right')->nullable()->after('secondary_button_margin_left');

            // Content alignment & margin
            $table->string('content_alignment', 50)->nullable()->default('center')->after('secondary_button_margin_right');
            $table->integer('content_margin_top')->nullable()->after('content_alignment');
            $table->integer('content_margin_bottom')->nullable()->after('content_margin_top');
            $table->integer('content_margin_left')->nullable()->after('content_margin_bottom');
            $table->integer('content_margin_right')->nullable()->after('content_margin_left');
        });
    }

    public function down()
    {
        Schema::table('homepage_ctas', function (Blueprint $table) {
            $table->dropColumn([
                'button_position',
                'button_margin_top',
                'button_margin_bottom',
                'button_margin_left',
                'button_margin_right',
                'secondary_button_position',
                'secondary_button_margin_top',
                'secondary_button_margin_bottom',
                'secondary_button_margin_left',
                'secondary_button_margin_right',
                'content_alignment',
                'content_margin_top',
                'content_margin_bottom',
                'content_margin_left',
                'content_margin_right',
            ]);
        });
    }
};