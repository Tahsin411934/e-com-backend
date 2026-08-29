<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add a "returned" lifecycle status so a Purchase Order that has been
        // fully returned shows as returned (instead of lingering on "received").
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->enum('status', [
                'draft', 'ordered', 'partially_received', 'received', 'returned', 'cancelled',
            ])->default('draft')->change();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->enum('status', [
                'draft', 'ordered', 'partially_received', 'received', 'cancelled',
            ])->default('draft')->change();
        });
    }
};
