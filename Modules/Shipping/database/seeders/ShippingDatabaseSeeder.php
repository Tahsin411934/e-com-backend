<?php

namespace Modules\Shipping\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Identity\Models\User;
use Modules\Order\Models\Order;
use Modules\Shipping\Models\DeliveryDriver;
use Modules\Shipping\Models\DeliveryZone;
use Modules\Shipping\Models\Shipment;
use Modules\Shipping\Models\ShipmentEvent;
use Modules\Store\Models\Address;
use Modules\Store\Models\Country;
use Modules\Store\Models\Store;

class ShippingDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $store = Store::where('slug', 'main-store-dhaka')->first() ?? Store::first();

        if (! $store) {
            $this->command->warn('No stores found. Skipping shipping seeding.');

            return;
        }

        $country = Country::where('iso2', 'BD')->first() ?? Country::first();
        $admin = User::where('email', 'admin@example.com')->first() ?? User::first();

        $zones = [
            [
                'name' => 'Dhaka Metro Core',
                'code' => 'DHK-METRO',
                'city' => 'Dhaka',
                'state' => 'Dhaka Division',
                'postal_codes' => ['1205', '1212', '1213', '1216'],
                'base_fee' => 80,
                'per_km_fee' => 12,
                'free_shipping_min' => 2500,
                'max_delivery_distance_km' => 18,
                'estimated_min_days' => 0,
                'estimated_max_days' => 1,
                'status' => 'active',
            ],
            [
                'name' => 'Dhaka Suburban',
                'code' => 'DHK-SUB',
                'city' => 'Dhaka',
                'state' => 'Dhaka Division',
                'postal_codes' => ['1230', '1340', '1362'],
                'base_fee' => 120,
                'per_km_fee' => 15,
                'free_shipping_min' => 3500,
                'max_delivery_distance_km' => 35,
                'estimated_min_days' => 1,
                'estimated_max_days' => 2,
                'status' => 'active',
            ],
            [
                'name' => 'Nationwide Standard',
                'code' => 'BD-NATIONWIDE',
                'city' => null,
                'state' => null,
                'postal_codes' => null,
                'base_fee' => 180,
                'per_km_fee' => 0,
                'free_shipping_min' => 5000,
                'max_delivery_distance_km' => null,
                'estimated_min_days' => 2,
                'estimated_max_days' => 5,
                'status' => 'active',
            ],
        ];

        $createdZones = collect();
        foreach ($zones as $zoneData) {
            $createdZones->push(DeliveryZone::updateOrCreate(
                ['code' => $zoneData['code']],
                array_merge($zoneData, [
                    'store_id' => $store->id,
                    'country_id' => $country?->id,
                ])
            ));
        }

        $drivers = [
            [
                'employee_code' => 'DRV-DHK-001',
                'name' => 'Rahim Uddin',
                'phone' => '+8801711000001',
                'email' => 'rahim.driver@example.com',
                'vehicle_type' => 'motorbike',
                'vehicle_plate' => 'DHA-11-2201',
                'capacity_kg' => 25,
                'status' => 'available',
                'delivery_zone_id' => $createdZones[0]?->id,
            ],
            [
                'employee_code' => 'DRV-DHK-002',
                'name' => 'Karim Hasan',
                'phone' => '+8801711000002',
                'email' => 'karim.driver@example.com',
                'vehicle_type' => 'van',
                'vehicle_plate' => 'DHA-22-7710',
                'capacity_kg' => 300,
                'status' => 'available',
                'delivery_zone_id' => $createdZones[1]?->id,
            ],
        ];

        $createdDrivers = collect();
        foreach ($drivers as $driverData) {
            $createdDrivers->push(DeliveryDriver::updateOrCreate(
                ['employee_code' => $driverData['employee_code']],
                array_merge($driverData, [
                    'store_id' => $store->id,
                    'user_id' => null,
                    'last_seen_at' => now()->subMinutes(rand(5, 40)),
                ])
            ));
        }

        $orders = Order::orderByDesc('created_at')->limit(5)->get();
        if ($orders->isEmpty()) {
            $this->command->warn('No orders found. Seed orders before creating shipments.');

            return;
        }

        $address = Address::where('store_id', $store->id)->first() ?? Address::first();
        $statuses = ['pending', 'packed', 'ready_for_pickup', 'out_for_delivery', 'delivered'];

        foreach ($orders as $index => $order) {
            $zone = $createdZones[$index % $createdZones->count()];
            $driver = $createdDrivers[$index % $createdDrivers->count()];
            $status = $statuses[$index % count($statuses)];

            $shipment = Shipment::updateOrCreate(
                ['tracking_number' => 'SHP-SEED-'.str_pad((string) $order->id, 5, '0', STR_PAD_LEFT)],
                [
                    'order_id' => $order->id,
                    'store_id' => $order->store_id ?? $store->id,
                    'delivery_zone_id' => $zone->id,
                    'driver_id' => $driver->id,
                    'shipping_address_id' => $order->shipping_address_id ?? $address?->id,
                    'carrier_name' => 'In-house Delivery',
                    'service_level' => $index === 0 ? 'same_day' : 'standard',
                    'delivery_type' => 'home_delivery',
                    'status' => $status,
                    'shipping_cost' => $zone->base_fee,
                    'package_weight_kg' => 1.25 + $index,
                    'package_count' => 1,
                    'recipient_name' => $address?->contact_name ?? 'Sample Customer',
                    'recipient_phone' => $address?->contact_phone ?? '+8801700000000',
                    'delivery_instructions' => 'Call customer before arrival.',
                    'scheduled_delivery_date' => now()->addDays(min($index, 3))->toDateString(),
                    'eta_at' => now()->addDays(min($index, 3))->setTime(18, 0),
                    'shipped_at' => in_array($status, ['out_for_delivery', 'delivered'], true) ? now()->subHours(3) : null,
                    'delivered_at' => $status === 'delivered' ? now()->subHour() : null,
                ]
            );

            ShipmentEvent::updateOrCreate(
                [
                    'shipment_id' => $shipment->id,
                    'event_type' => 'status_update',
                    'title' => 'Seeded status: '.str_replace('_', ' ', $status),
                ],
                [
                    'driver_id' => $driver->id,
                    'created_by' => $admin?->id,
                    'status' => $status,
                    'description' => 'Initial shipment event created by seeder.',
                    'latitude' => 23.7806,
                    'longitude' => 90.4074,
                    'occurred_at' => now()->subMinutes(20 - $index),
                ]
            );
        }

        $this->command->info('Shipping module seeded successfully!');
    }
}
