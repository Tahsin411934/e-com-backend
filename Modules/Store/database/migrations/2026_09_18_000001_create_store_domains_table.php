<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SaaS: every store is reachable through one or more hostnames.
     *
     *  - type = subdomain → the free wildcard host ({store_slug}.{suffix})
     *    provisioned automatically at registration. Always trusted.
     *  - type = custom    → an owner-owned domain (Shopify-style). It is only
     *    served once DNS ownership has been proven (verified_at set).
     */
    public function up(): void
    {
        Schema::create('store_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('domain', 255)->unique();
            $table->enum('type', ['subdomain', 'custom'])->default('subdomain');
            $table->boolean('is_primary')->default(false);
            $table->string('ssl_status', 20)->default('pending'); // pending | provisioning | active | failed
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_domains');
    }
};
