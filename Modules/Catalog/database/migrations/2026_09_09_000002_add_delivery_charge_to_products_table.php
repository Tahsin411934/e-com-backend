<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Product-level delivery charge (৳). Admin product form-এ editable input,
     * default ৳120. Checkout shipping = cart-এর প্রোডাক্টগুলোর delivery_charge
     * এর মধ্যে সর্বোচ্চটা একবার (এক পার্সেল = এক চার্জ)।
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('delivery_charge', 12, 2)
                ->default(120.00)
                ->after('product_type');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('delivery_charge');
        });
    }
};
