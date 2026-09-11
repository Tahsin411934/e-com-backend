<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Multi-tenant orders: each checkout is split into one order per store,
     * so orders carry their owning store. Index keeps store-wise listing fast.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasIndex('orders', 'orders_store_id_created_at_index')) {
                $table->index(['store_id', 'created_at']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasIndex('orders', 'orders_store_id_created_at_index')) {
                $table->dropIndex(['store_id', 'created_at']);
            }
        });
    }
};