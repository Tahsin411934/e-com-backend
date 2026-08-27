<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'users',
        'roles',
        'permissions',
        'user_sessions',
        'account_accounts',
        'account_categories',
        'account_daily_summaries',
        'account_expenses',
        'account_investments',
        'account_product_profit_snapshots',
        'account_transactions',
        'account_transaction_lines',
        'account_transfers',
        'campaigns',
        'campaign_products',
        'carts',
        'cart_items',
        'coupons',
        'wishlists',
        'brands',
        'categories',
        'products',
        'product_categories',
        'product_images',
        'product_requests',
        'product_variants',
        'sizes',
        'tax_rates',
        'units',
        'variant_options',
        'product_supplier',
        'announcement_bars',
        'banners',
        'homepage_ctas',
        'navbar_items',
        'settings',
        'subnavbar_items',
        'inventory_locations',
        'inventory_movements',
        'inventory_stock',
        'purchase_orders',
        'purchase_order_items',
        'purchase_returns',
        'suppliers',
        'supplier_payments',
        'deliveries',
        'orders',
        'order_items',
        'payments',
        'refunds',
        'delivery_drivers',
        'delivery_zones',
        'shipments',
        'shipment_events',
        'addresses',
        'app_settings',
        'countries',
        'stores',
        'store_staff',
        'webhook_deliveries',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'deleted_at')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'deleted_at')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
