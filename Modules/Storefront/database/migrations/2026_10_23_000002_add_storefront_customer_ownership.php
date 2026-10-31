<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['carts', 'wishlists', 'orders', 'product_reviews'] as $name) {
            Schema::table($name, function (Blueprint $table) use ($name) {
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                if (in_array($name, ['wishlists', 'product_reviews'], true)) {
                    $table->unsignedBigInteger('user_id')->nullable()->change();
                }
                $table->index(['customer_id', $name === 'orders' || $name === 'carts' ? 'store_id' : 'product_id']);
            });
        }
        Schema::table('wishlists', fn (Blueprint $table) => $table->unique(['customer_id', 'product_id']));
        Schema::create('customer_password_reset_tokens', function (Blueprint $table) {
            $table->foreignId('customer_id')->primary()->constrained('customers')->cascadeOnDelete();
            $table->string('token');
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_password_reset_tokens');
        Schema::table('wishlists', fn (Blueprint $table) => $table->dropUnique(['customer_id', 'product_id']));
        foreach (['carts', 'wishlists', 'orders', 'product_reviews'] as $name) {
            Schema::table($name, function (Blueprint $table) use ($name) {
                $table->dropForeign(['customer_id']);
                $table->dropIndex(['customer_id', $name === 'orders' || $name === 'carts' ? 'store_id' : 'product_id']);
                $table->dropColumn('customer_id');
            });
        }
        // user_id stays nullable: customer-owned rows must never be reassigned to users.
    }
};
