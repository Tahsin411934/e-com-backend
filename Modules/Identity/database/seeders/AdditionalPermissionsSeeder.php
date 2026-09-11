<?php

namespace Modules\Identity\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Modules\Identity\Models\Permission;
use Modules\Identity\Models\Role;

class AdditionalPermissionsSeeder extends Seeder
{
    /**
     * Full permission catalogue for every module in the system. The base
     * IdentityDatabaseSeeder only ships the core permissions; this seeder
     * adds every remaining module permission (POS, shipping, account,
     * reports, frontend, marketing, ...) so the Role Management UI can
     * grant/revoke them all.
     */
    private const MODULES = [
        // ===== Dashboard =====
        'dashboard' => [
            'dashboard.view' => 'View dashboard',
        ],

        // ===== Catalog =====
        'units' => [
            'units.view' => 'View units',
            'units.create' => 'Create units',
            'units.edit' => 'Edit units',
            'units.delete' => 'Delete units',
        ],
        'sizes' => [
            'sizes.view' => 'View sizes',
            'sizes.create' => 'Create sizes',
            'sizes.edit' => 'Edit sizes',
            'sizes.delete' => 'Delete sizes',
        ],
        'tax_rates' => [
            'tax-rates.view' => 'View tax rates',
            'tax-rates.create' => 'Create tax rates',
            'tax-rates.edit' => 'Edit tax rates',
            'tax-rates.delete' => 'Delete tax rates',
        ],
        'barcode' => [
            'barcode-print.view' => 'View barcode print',
        ],
        'product_requests' => [
            'product-requests.view' => 'View product requests',
            'product-requests.create' => 'Create product requests',
            'product-requests.edit' => 'Edit product requests',
            'product-requests.delete' => 'Delete product requests',
        ],
        // ===== Store & Location =====
        'stores' => [
            'stores.create' => 'Create stores',
            'stores.delete' => 'Delete stores',
        ],
        'store_staff' => [
            'store-staff.view' => 'View store staff',
            'store-staff.create' => 'Create store staff',
            'store-staff.edit' => 'Edit store staff',
            'store-staff.delete' => 'Delete store staff',
        ],
        'countries' => [
            'countries.view' => 'View countries',
            'countries.create' => 'Create countries',
            'countries.edit' => 'Edit countries',
            'countries.delete' => 'Delete countries',
        ],
        'addresses' => [
            'addresses.view' => 'View addresses',
            'addresses.create' => 'Create addresses',
            'addresses.edit' => 'Edit addresses',
            'addresses.delete' => 'Delete addresses',
        ],
        'app_settings' => [
            'app-settings.view' => 'View app settings',
            'app-settings.create' => 'Create app settings',
            'app-settings.edit' => 'Edit app settings',
            'app-settings.delete' => 'Delete app settings',
        ],

        // ===== Cart & Wishlist =====
        'carts' => [
            'carts.view' => 'View carts',
            'carts.edit' => 'Edit carts',
            'carts.delete' => 'Delete carts',
        ],
        'coupons' => [
            'coupons.view' => 'View coupons',
            'coupons.create' => 'Create coupons',
            'coupons.edit' => 'Edit coupons',
            'coupons.delete' => 'Delete coupons',
        ],
        'campaigns' => [
            'campaigns.view' => 'View campaigns',
            'campaigns.create' => 'Create campaigns',
            'campaigns.edit' => 'Edit campaigns',
            'campaigns.delete' => 'Delete campaigns',
        ],
        'wishlists' => [
            'wishlists.view' => 'View wishlists',
        ],
        // ===== Purchases =====
        'suppliers' => [
            'suppliers.view' => 'View suppliers',
            'suppliers.create' => 'Create suppliers',
            'suppliers.edit' => 'Edit suppliers',
            'suppliers.delete' => 'Delete suppliers',
        ],
        'purchase_orders' => [
            'purchase-orders.view' => 'View purchase orders',
            'purchase-orders.create' => 'Create purchase orders',
            'purchase-orders.edit' => 'Edit purchase orders',
            'purchase-orders.delete' => 'Delete purchase orders',
        ],
        'purchase_returns' => [
            'purchase-returns.view' => 'View purchase returns',
            'purchase-returns.create' => 'Create purchase returns',
            'purchase-returns.edit' => 'Edit purchase returns',
            'purchase-returns.delete' => 'Delete purchase returns',
        ],
        'supplier_payments' => [
            'supplier-payments.view' => 'View supplier payments',
            'supplier-payments.create' => 'Create supplier payments',
            'supplier-payments.edit' => 'Edit supplier payments',
            'supplier-payments.delete' => 'Delete supplier payments',
        ],

        // ===== Orders & Fulfilment =====
        'deliveries' => [
            'deliveries.view' => 'View deliveries',
            'deliveries.create' => 'Create deliveries',
            'deliveries.edit' => 'Edit deliveries',
            'deliveries.delete' => 'Delete deliveries',
        ],
        'refunds' => [
            'refunds.view' => 'View refunds',
            'refunds.create' => 'Create refunds',
            'refunds.edit' => 'Edit refunds',
            'refunds.delete' => 'Delete refunds',
        ],

        // ===== Account / Ledger =====
        'accounts' => [
            'accounts.view' => 'View accounts',
            'accounts.create' => 'Create accounts',
            'accounts.edit' => 'Edit accounts',
            'accounts.delete' => 'Delete accounts',
        ],
        'account_categories' => [
            'account-categories.view' => 'View account categories',
            'account-categories.create' => 'Create account categories',
            'account-categories.edit' => 'Edit account categories',
            'account-categories.delete' => 'Delete account categories',
        ],
        'account_expenses' => [
            'account-expenses.view' => 'View account expenses',
            'account-expenses.create' => 'Create account expenses',
            'account-expenses.edit' => 'Edit account expenses',
            'account-expenses.delete' => 'Delete account expenses',
        ],
        'account_investments' => [
            'account-investments.view' => 'View account investments',
            'account-investments.create' => 'Create account investments',
            'account-investments.edit' => 'Edit account investments',
            'account-investments.delete' => 'Delete account investments',
        ],
        'account_transfers' => [
            'account-transfers.view' => 'View account transfers',
            'account-transfers.create' => 'Create account transfers',
            'account-transfers.edit' => 'Edit account transfers',
            'account-transfers.delete' => 'Delete account transfers',
        ],
        'account_reports' => [
            'account-reports.view' => 'View account reports',
        ],
        // ===== Shipping / Delivery =====
        'shipments' => [
            'shipments.view' => 'View shipments',
            'shipments.create' => 'Create shipments',
            'shipments.edit' => 'Edit shipments',
            'shipments.delete' => 'Delete shipments',
        ],
        'shipment_events' => [
            'shipment-events.view' => 'View shipment events',
        ],
        'delivery_drivers' => [
            'delivery-drivers.view' => 'View delivery drivers',
            'delivery-drivers.create' => 'Create delivery drivers',
            'delivery-drivers.edit' => 'Edit delivery drivers',
            'delivery-drivers.delete' => 'Delete delivery drivers',
        ],
        'delivery_zones' => [
            'delivery-zones.view' => 'View delivery zones',
            'delivery-zones.create' => 'Create delivery zones',
            'delivery-zones.edit' => 'Edit delivery zones',
            'delivery-zones.delete' => 'Delete delivery zones',
        ],

        // ===== POS =====
        'pos' => [
            'pos.view' => 'View POS',
            'pos.sell' => 'Make POS sales',
            'pos-registers.view' => 'View POS registers',
            'pos-registers.create' => 'Create POS registers',
            'pos-registers.edit' => 'Edit POS registers',
            'pos-registers.delete' => 'Delete POS registers',
            'pos-shifts.view' => 'View POS shifts',
            'pos-shifts.create' => 'Create POS shifts',
            'pos-shifts.edit' => 'Edit POS shifts',
            'pos-sales.view' => 'View POS sales',
            'pos-sales.create' => 'Create POS sales',
            'pos-sales.edit' => 'Edit POS sales',
            'pos-sales.delete' => 'Delete POS sales',
        ],

        // ===== Reviews & Notifications =====
        'reviews' => [
            'reviews.view' => 'View reviews',
            'reviews.create' => 'Create reviews',
            'reviews.edit' => 'Edit reviews',
            'reviews.delete' => 'Delete reviews',
            'reviews.moderate' => 'Moderate reviews',
        ],

        // ===== History =====
        'history' => [
            'history.view' => 'View history',
            'history.export' => 'Export history',
        ],
        // ===== Frontend =====
        'frontend' => [
            'frontend.view' => 'View frontend',
            'frontend.navbar.view' => 'View navbar items',
            'frontend.navbar.create' => 'Create navbar items',
            'frontend.navbar.edit' => 'Edit navbar items',
            'frontend.navbar.delete' => 'Delete navbar items',
            'frontend.banners.view' => 'View banners',
            'frontend.banners.create' => 'Create banners',
            'frontend.banners.edit' => 'Edit banners',
            'frontend.banners.delete' => 'Delete banners',
            'frontend.sliders.view' => 'View sliders',
            'frontend.sliders.create' => 'Create sliders',
            'frontend.sliders.edit' => 'Edit sliders',
            'frontend.sliders.delete' => 'Delete sliders',
            'frontend.announcements.view' => 'View announcement bars',
            'frontend.announcements.create' => 'Create announcement bars',
            'frontend.announcements.edit' => 'Edit announcement bars',
            'frontend.announcements.delete' => 'Delete announcement bars',
            'frontend.ctas.view' => 'View homepage CTAs',
            'frontend.ctas.create' => 'Create homepage CTAs',
            'frontend.ctas.edit' => 'Edit homepage CTAs',
            'frontend.ctas.delete' => 'Delete homepage CTAs',
            'frontend.pages.view' => 'View pages',
            'frontend.pages.create' => 'Create pages',
            'frontend.pages.edit' => 'Edit pages',
            'frontend.pages.delete' => 'Delete pages',
            'frontend.testimonials.view' => 'View testimonials',
            'frontend.testimonials.create' => 'Create testimonials',
            'frontend.testimonials.edit' => 'Edit testimonials',
            'frontend.testimonials.delete' => 'Delete testimonials',
            'frontend.faqs.view' => 'View FAQs',
            'frontend.faqs.create' => 'Create FAQs',
            'frontend.faqs.edit' => 'Edit FAQs',
            'frontend.faqs.delete' => 'Delete FAQs',
            'frontend.footers.view' => 'View footers',
            'frontend.footers.create' => 'Create footers',
            'frontend.footers.edit' => 'Edit footers',
            'frontend.footers.delete' => 'Delete footers',
            'frontend.settings.view' => 'View frontend settings',
            'frontend.settings.edit' => 'Edit frontend settings',
        ],

        // ===== Marketing =====
        'marketing' => [
            'marketing.view' => 'View marketing',
            'marketing.gtm.view' => 'View Google Tag Manager',
            'marketing.gtm.edit' => 'Edit Google Tag Manager',
        ],

        // ===== Reports =====
        'reports' => [
            'reports.view' => 'View reports',
            'reports.sales' => 'View sales reports',
            'reports.products' => 'View product reports',
            'reports.inventory' => 'View inventory reports',
            'reports.orders' => 'View order reports',
            'reports.purchases' => 'View purchase reports',
            'reports.staff' => 'View staff reports',
            'reports.executive' => 'View executive dashboard',
        ],

        // ===== Settings =====
        'settings' => [
            'settings.view' => 'View settings',
            'settings.edit' => 'Edit settings',
        ],
    ];

    /**
     * Default role → permission grants for the newly added module
     * permissions. Roles that already received these permissions keep them;
     * this syncs the grants the app expects.
     */
    private const ROLE_GRANTS = [
        'Admin' => [
            'dashboard.view',
            'users.view',
            'products.*', 'categories.*', 'brands.*',
            'units.*', 'sizes.*', 'tax-rates.*', 'barcode-print.view',
            'product-requests.view', 'product-requests.create', 'product-requests.edit',
            'stores.create', 'stores.delete', 'stores.view', 'store-staff.*', 'countries.*',
            'addresses.*', 'app-settings.*',
            'inventory.*', 'inventory-stock.*', 'inventory-locations.*', 'inventory-movements.*',
            'carts.*', 'coupons.*', 'campaigns.*', 'wishlists.view',
            'suppliers.*', 'purchase-orders.*', 'purchase-returns.*', 'supplier-payments.*',
            'orders.*', 'payments.*', 'deliveries.*', 'refunds.*',
            'accounts.*', 'account-categories.*', 'account-expenses.*',
            'account-investments.*', 'account-transfers.*', 'account-reports.view',
            'shipments.*', 'shipment-events.view', 'delivery-drivers.*', 'delivery-zones.*',
            'pos.*',
            'reviews.*', 'history.*', 'histories.*', 'notifications.*',
            'webhooks.*', 'webhook-deliveries.*',
            'frontend.*', 'marketing.*',
            'reports.*', 'settings.*',
        ],
        'Manager' => [
            'dashboard.view',
            'products.view', 'products.create', 'products.edit',
            'categories.view', 'categories.create', 'categories.edit',
            'brands.view', 'brands.create', 'brands.edit',
            'units.view', 'sizes.view', 'barcode-print.view',
            'stores.view',
            'inventory.view', 'inventory-stock.view', 'inventory-locations.view', 'inventory-movements.view',
            'suppliers.view', 'purchase-orders.view', 'purchase-returns.view',
            'supplier-payments.view',
            'orders.view', 'payments.view', 'deliveries.view', 'refunds.view',
            'shipments.view', 'shipment-events.view', 'delivery-drivers.view', 'delivery-zones.view',
            'pos.view', 'pos.sell', 'pos-registers.view', 'pos-shifts.view', 'pos-sales.view',
            'reports.*', 'reviews.view', 'notifications.view', 'histories.view',
            'webhooks.view', 'webhook-deliveries.view',
        ],
        'Staff' => [
            'dashboard.view',
            'products.view', 'categories.view', 'brands.view',
            'inventory.view', 'inventory-stock.view', 'inventory-locations.view', 'inventory-movements.view',
            'orders.view', 'payments.view', 'refunds.view', 'stores.view',
            'suppliers.view', 'purchase-orders.view', 'purchase-returns.view', 'supplier-payments.view',
            'shipments.view', 'shipment-events.view', 'delivery-drivers.view', 'delivery-zones.view', 'deliveries.view',
            'pos.view', 'pos.sell', 'pos-registers.view', 'pos-shifts.view', 'pos-sales.view',
            'reviews.view', 'notifications.view', 'histories.view', 'webhooks.view', 'webhook-deliveries.view',
            'reports.*',
        ],
        'Store Owner' => [
            'dashboard.view',
            'products.*', 'categories.*', 'brands.*',
            'units.view', 'sizes.view', 'tax-rates.view', 'barcode-print.view',
            'orders.view', 'stores.view',
            'reports.view',
        ],
    ];

    public function run(): void
    {
        $this->command->info('Seeding additional module permissions...');

        $created = 0;

        foreach (self::MODULES as $module => $permissions) {
            foreach ($permissions as $name => $description) {
                $perm = Permission::firstOrCreate(['name' => $name], ['description' => $description]);
                $perm->update(['description' => $description]);

                $created++;
            }
        }

        $this->command->info("Added/verified {$created} permissions.");

        // Super Admin always holds every permission.
        $role = Role::where('name', 'Super Admin')->first();
        if ($role) {
            $role->permissions()->syncWithoutDetaching(Permission::all()->pluck('id'));
        }

        // Grant the default module permissions to the standard roles.
        foreach (self::ROLE_GRANTS as $roleName => $permissionPatterns) {
            $role = Role::where('name', $roleName)->first();
            if (! $role) {
                continue;
            }

            $ids = collect($permissionPatterns)
                ->flatMap(fn ($pattern) => $this->matchingPermissions($pattern))
                ->unique()
                ->pluck('id');

            $role->permissions()->syncWithoutDetaching($ids);
        }

        $this->command->info('Additional permissions seeded successfully!');
    }

    /**
     * Resolve a permission pattern (e.g. "stores.*") into matching Permission models.
     */
    private function matchingPermissions(string $pattern): Collection
    {
        if (str_ends_with($pattern, '.*')) {
            $prefix = substr($pattern, 0, -1);

            return Permission::where('name', 'like', $prefix.'%')->get();
        }

        return collect([Permission::where('name', $pattern)->first()])->filter();
    }
}
