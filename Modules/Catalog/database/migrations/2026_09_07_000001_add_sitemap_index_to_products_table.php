<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sitemap feed-এর জন্য কম্পোজিট ইনডেক্স।
     *
     * Next.js সাইটম্যাপের কোয়েরি:
     *   WHERE status='active' AND visibility='public' AND published_at IS NOT NULL
     *   ORDER BY id
     *   LIMIT 25000 OFFSET N
     *
     * MySQL এই ইনডেক্স স্ক্যান করেই WHERE + ORDER + LIMIT মেটায় —
     * ৫০k+ রোতেও offset-pagination-এ full-scan হয় না।
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index(
                ['status', 'visibility', 'published_at', 'id'],
                'idx_products_sitemap',
            );
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_sitemap');
        });
    }
};