<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->unsignedBigInteger('variant_option_id')->nullable()->after('variant_id');
            $table->index('variant_option_id');

            $table->foreign('variant_option_id')
                ->references('id')->on('variant_options')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropForeign(['variant_option_id']);
            $table->dropColumn('variant_option_id');
        });
    }
};
