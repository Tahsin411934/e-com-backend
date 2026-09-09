<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Guest orders carry a customer name instead of a user_id
        // (user_id stays null for guests).
        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_name', 191)->nullable()->after('user_id');
        });

        // Guest orders have no user account — deliveries.user_id must be
        // nullable so a guest delivery record can be created without a user.
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
        Schema::table('deliveries', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->unsignedBigInteger('user_id')->change();
        });
        Schema::table('deliveries', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('customer_name');
        });
    }
};