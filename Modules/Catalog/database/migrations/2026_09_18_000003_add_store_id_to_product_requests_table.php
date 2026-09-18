<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SaaS: product requests (customer enquiries) belong to the storefront
     * they were submitted from, so each owner only sees their own leads.
     *
     *  - NULL store_id = legacy/central enquiry (submitted before the
     *    multi-tenant storefront existed)
     *  - store row      = submitted on that store's storefront
     */
    public function up(): void
    {
        if (Schema::hasColumn('product_requests', 'store_id')) {
            return;
        }

        Schema::table('product_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('store_id')->nullable()->after('user_id');
            $table->foreign('store_id')
                ->references('id')
                ->on('stores')
                ->nullOnDelete();
            $table->index(['store_id', 'status']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('product_requests', 'store_id')) {
            return;
        }

        Schema::table('product_requests', function (Blueprint $table) {
            $table->dropIndex(['store_id', 'status']);
            $table->dropForeign(['store_id']);
            $table->dropColumn('store_id');
        });
    }
};