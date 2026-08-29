<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Catalog\Models\ProductVariant;
use Modules\Identity\Models\User;
use Modules\Inventory\Models\InventoryLocation;
use Modules\Inventory\Models\InventoryMovement;
use Modules\Inventory\Models\InventoryStock;
use Modules\Inventory\Models\PurchaseOrder;
use Modules\Inventory\Models\PurchaseOrderItem;
use Modules\Inventory\Models\Supplier;
use Modules\Store\Models\Store;

class InventoryDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $mainStore = Store::where('slug', 'main-store-dhaka')->first();
        $onlineStore = Store::where('slug', 'online-store')->first();

        if (! $mainStore) {
            $this->command->warn('No stores found. Skipping inventory seeding.');

            return;
        }

        // ===== Inventory Locations =====
        $locations = [
            ['store_id' => $mainStore->id, 'name' => 'Main Warehouse - Dhaka', 'location_type' => 'warehouse', 'status' => 'active'],
            ['store_id' => $mainStore->id, 'name' => 'Retail Shop - Gulshan', 'location_type' => 'retail', 'status' => 'active'],
            ['store_id' => $mainStore->id, 'name' => 'Delivery Hub - Mirpur', 'location_type' => 'delivery_hub', 'status' => 'active'],
        ];

        if ($onlineStore) {
            $locations[] = ['store_id' => $onlineStore->id, 'name' => 'Online Warehouse', 'location_type' => 'warehouse', 'status' => 'active'];
        }

        $createdLocations = [];
        foreach ($locations as $locData) {
            $loc = InventoryLocation::firstOrCreate(
                ['store_id' => $locData['store_id'], 'name' => $locData['name']],
                $locData
            );
            $createdLocations[] = $loc;
        }

        // ===== Inventory Stock =====
        $variants = ProductVariant::all();
        if ($variants->isEmpty()) {
            $this->command->warn('No product variants found. Skipping stock seeding.');

            return;
        }

        $warehouseLocation = $createdLocations[0] ?? null;
        $retailLocation = $createdLocations[1] ?? null;

        if ($warehouseLocation) {
            foreach ($variants as $variant) {
                InventoryStock::firstOrCreate(
                    ['location_id' => $warehouseLocation->id, 'variant_id' => $variant->id],
                    [
                        'quantity_on_hand' => rand(50, 200),
                        'quantity_reserved' => rand(5, 20),
                        'reorder_point' => 20,
                    ]
                );

                if ($retailLocation) {
                    InventoryStock::firstOrCreate(
                        ['location_id' => $retailLocation->id, 'variant_id' => $variant->id],
                        [
                            'quantity_on_hand' => rand(5, 30),
                            'quantity_reserved' => rand(1, 5),
                            'reorder_point' => 5,
                        ]
                    );
                }
            }
        }

        // ===== Inventory Movements =====
        $adminUser = User::where('email', 'admin@example.com')->first();
        if ($warehouseLocation && ! $variants->isEmpty()) {
            $firstVariant = $variants->first();
            InventoryMovement::create([
                'location_id' => $warehouseLocation->id,
                'variant_id' => $firstVariant->id,
                'movement_type' => 'purchase',
                'quantity' => 100,
                'reference_type' => 'PO',
                'reference_id' => 1001,
                'note' => 'Initial stock purchase order',
                'created_by' => $adminUser?->id,
            ]);
        }

        // ===== Suppliers =====
        $suppliersData = [
            [
                'name' => 'Dhaka Electronics Wholesale',
                'slug' => 'dhaka-electronics-wholesale',
                'email' => 'info@dhakaelectronics.com',
                'phone' => '+880-1712-345678',
                'contact_person' => 'Rahman Ahmed',
                'address' => '123 Electronics Market, Bongshal',
                'city' => 'Dhaka',
                'country' => 'Bangladesh',
                'tax_number' => 'BD-TAX-12345',
                'payment_terms' => 'Net 30',
                'notes' => 'Primary electronics supplier',
                'status' => 'active',
            ],
            [
                'name' => 'Global Fashion Imports',
                'slug' => 'global-fashion-imports',
                'email' => 'orders@globalfashion.com',
                'phone' => '+880-1812-987654',
                'contact_person' => 'Fatima Khan',
                'address' => '456 Garment Street, Gazipur',
                'city' => 'Gazipur',
                'country' => 'Bangladesh',
                'tax_number' => 'BD-TAX-67890',
                'payment_terms' => 'Net 15',
                'notes' => 'Clothing and fashion items',
                'status' => 'active',
            ],
            [
                'name' => 'Chittagong raw Materials Co.',
                'slug' => 'chittagong-raw-materials',
                'email' => 'sales@ctgmaterials.com',
                'phone' => '+880-1912-555666',
                'contact_person' => 'Karim Uddin',
                'address' => '789 Port Road, Chittagong',
                'city' => 'Chittagong',
                'country' => 'Bangladesh',
                'tax_number' => 'BD-TAX-11223',
                'payment_terms' => 'Net 45',
                'notes' => 'Raw materials and packaging',
                'status' => 'active',
            ],
        ];

        $createdSuppliers = [];
        foreach ($suppliersData as $supplierData) {
            $supplier = Supplier::firstOrCreate(
                ['slug' => $supplierData['slug']],
                $supplierData
            );
            $createdSuppliers[] = $supplier;
        }

        // ===== Purchase Orders =====
        $adminUser = User::where('email', 'admin@example.com')->first();
        $variants = ProductVariant::all();

        if ($adminUser && $variants->isNotEmpty() && ! empty($createdSuppliers[0])) {
            // PO 1 - Received
            $po1 = PurchaseOrder::firstOrCreate(
                ['po_number' => 'PO-2026-0001'],
                [
                    'supplier_id' => $createdSuppliers[0]->id,
                    'store_id' => $mainStore->id,
                    'status' => 'received',
                    'total_amount' => 5000.00,
                    'shipping_cost' => 200.00,
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                    'payment_status' => 'paid',
                    'order_date' => now()->subDays(30),
                    'expected_delivery_date' => now()->subDays(15),
                    'received_date' => now()->subDays(16),
                    'notes' => 'Initial electronics stock',
                    'created_by' => $adminUser->id,
                ]
            );

            $variant1 = $variants->first();
            if ($variant1) {
                PurchaseOrderItem::firstOrCreate([
                    'purchase_order_id' => $po1->id,
                    'variant_id' => $variant1->id,
                ], [
                    'quantity' => 100,
                    'received_quantity' => 100,
                    'unit_cost' => 50.00,
                    'subtotal' => 5000.00,
                    'tax' => 0,
                    'discount' => 0,
                    'notes' => 'Bulk order',
                ]);
            }

            // PO 2 - Ordered (pending delivery)
            $po2 = PurchaseOrder::firstOrCreate(
                ['po_number' => 'PO-2026-0002'],
                [
                    'supplier_id' => $createdSuppliers[1]->id,
                    'store_id' => $mainStore->id,
                    'status' => 'ordered',
                    'total_amount' => 3500.00,
                    'shipping_cost' => 150.00,
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                    'payment_status' => 'unpaid',
                    'order_date' => now()->subDays(5),
                    'expected_delivery_date' => now()->addDays(10),
                    'received_date' => null,
                    'notes' => 'Fashion items restocking',
                    'created_by' => $adminUser->id,
                ]
            );

            if ($variants->count() > 1) {
                $variant2 = $variants[1];
                PurchaseOrderItem::firstOrCreate([
                    'purchase_order_id' => $po2->id,
                    'variant_id' => $variant2->id,
                ], [
                    'quantity' => 50,
                    'received_quantity' => 0,
                    'unit_cost' => 70.00,
                    'subtotal' => 3500.00,
                    'tax' => 0,
                    'discount' => 0,
                    'notes' => null,
                ]);
            }

            // PO 3 - Draft
            $po3 = PurchaseOrder::firstOrCreate(
                ['po_number' => 'PO-2026-0003'],
                [
                    'supplier_id' => $createdSuppliers[2]->id,
                    'store_id' => $mainStore->id,
                    'status' => 'draft',
                    'total_amount' => 0,
                    'shipping_cost' => 0,
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                    'payment_status' => 'unpaid',
                    'order_date' => now(),
                    'expected_delivery_date' => now()->addDays(20),
                    'received_date' => null,
                    'notes' => 'Draft - awaiting approval',
                    'created_by' => $adminUser->id,
                ]
            );

            if ($variants->count() > 2) {
                $variant3 = $variants[2];
                PurchaseOrderItem::firstOrCreate([
                    'purchase_order_id' => $po3->id,
                    'variant_id' => $variant3->id,
                ], [
                    'quantity' => 200,
                    'received_quantity' => 0,
                    'unit_cost' => 25.00,
                    'subtotal' => 5000.00,
                    'tax' => 0,
                    'discount' => 0,
                    'notes' => 'Packaging materials',
                ]);
            }
        }

        $this->command->info('Inventory module seeded successfully!');
    }
}
