<?php

namespace Modules\Store\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Store\Models\Store;
use Modules\Store\Models\StoreStaff;
use Modules\Store\Models\Country;
use Modules\Store\Models\Address;
use Modules\Identity\Models\User;

class StoreDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ===== Stores =====
        $stores = [
            [
                'name' => 'Main Store (Dhaka)',
                'slug' => 'main-store-dhaka',
                'email' => 'store.dhaka@example.com',
                'phone' => '01711111111',
                'status' => 'active',
                'currency_code' => 'BDT',
                'timezone' => 'Asia/Dhaka',
            ],
            [
                'name' => 'Chittagong Branch',
                'slug' => 'chittagong-branch',
                'email' => 'store.ctg@example.com',
                'phone' => '01711111112',
                'status' => 'active',
                'currency_code' => 'BDT',
                'timezone' => 'Asia/Dhaka',
            ],
            [
                'name' => 'Online Store',
                'slug' => 'online-store',
                'email' => 'online@example.com',
                'phone' => null,
                'status' => 'active',
                'currency_code' => 'USD',
                'timezone' => 'UTC',
            ],
        ];

        foreach ($stores as $storeData) {
            Store::firstOrCreate(['slug' => $storeData['slug']], $storeData);
        }

        // ===== Store Staff =====
        $adminUser = User::where('email', 'admin@example.com')->first();
        $staffUser = User::where('email', 'staff@example.com')->first();
        $managerUser = User::where('email', 'manager@example.com')->first();
        $mainStore = Store::where('slug', 'main-store-dhaka')->first();

        if ($mainStore && $adminUser) {
            StoreStaff::firstOrCreate([
                'store_id' => $mainStore->id,
                'user_id' => $adminUser->id,
            ], [
                'staff_code' => 'ADMIN-001',
                'status' => 'active',
                'hired_at' => now()->subMonths(6),
            ]);
        }

        if ($mainStore && $managerUser) {
            StoreStaff::firstOrCreate([
                'store_id' => $mainStore->id,
                'user_id' => $managerUser->id,
            ], [
                'staff_code' => 'MGR-001',
                'status' => 'active',
                'hired_at' => now()->subMonths(3),
            ]);
        }

        if ($mainStore && $staffUser) {
            StoreStaff::firstOrCreate([
                'store_id' => $mainStore->id,
                'user_id' => $staffUser->id,
            ], [
                'staff_code' => 'STF-001',
                'status' => 'active',
                'hired_at' => now()->subMonths(1),
            ]);
        }

        // ===== Countries (if not already seeded) =====
        $countries = [
            ['iso2' => 'US', 'name' => 'United States'],
            ['iso2' => 'CA', 'name' => 'Canada'],
            ['iso2' => 'GB', 'name' => 'United Kingdom'],
            ['iso2' => 'BD', 'name' => 'Bangladesh'],
            ['iso2' => 'IN', 'name' => 'India'],
            ['iso2' => 'PK', 'name' => 'Pakistan'],
            ['iso2' => 'AU', 'name' => 'Australia'],
            ['iso2' => 'DE', 'name' => 'Germany'],
            ['iso2' => 'FR', 'name' => 'France'],
            ['iso2' => 'JP', 'name' => 'Japan'],
        ];

        foreach ($countries as $country) {
            Country::firstOrCreate(['iso2' => $country['iso2']], $country);
        }

        // ===== Addresses =====
        $customers = User::whereIn('email', ['customer@example.com', 'admin@example.com'])->get();
        $bdCountry = Country::where('iso2', 'BD')->first();
        $store = Store::where('slug', 'main-store-dhaka')->first();

        $addresses = [
            [
                'user_id' => $customers->where('email', 'customer@example.com')->first()?->id,
                'store_id' => null,
                'label' => 'Home',
                'contact_name' => 'Bob Customer',
                'contact_phone' => '01700000004',
                'address_line1' => '123 Gulshan Avenue',
                'address_line2' => 'Level 5, House 12',
                'city' => 'Dhaka',
                'state' => 'Dhaka',
                'postal_code' => '1212',
                'country_id' => $bdCountry?->id ?? 1,
                'is_default' => 1,
            ],
            [
                'user_id' => null,
                'store_id' => $store?->id,
                'label' => 'Head Office',
                'contact_name' => 'Store Manager',
                'contact_phone' => '01711111111',
                'address_line1' => '456 Motijheel C/A',
                'address_line2' => null,
                'city' => 'Dhaka',
                'state' => 'Dhaka',
                'postal_code' => '1000',
                'country_id' => $bdCountry?->id ?? 1,
                'is_default' => 1,
            ],
        ];

        foreach ($addresses as $addrData) {
            Address::create($addrData);
        }

        $this->command->info('Store module seeded successfully!');
    }
}