<?php

namespace Modules\Pos\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Identity\Models\User;
use Modules\Pos\Models\PosRegister;
use Modules\Pos\Models\PosSale;
use Modules\Pos\Models\PosShift;
use Modules\Store\Models\Store;

class PosDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $mainStore = Store::where('slug', 'main-store-dhaka')->first();

        if (! $mainStore) {
            $this->command->warn('No stores found. Skipping POS seeding.');

            return;
        }

        $adminUser = User::where('email', 'admin@example.com')->first();

        // ===== POS Registers =====
        $registers = [
            ['store_id' => $mainStore->id, 'name' => 'Main Counter - Gulshan', 'code' => 'REG-001', 'type' => 'counter', 'status' => 'active'],
            ['store_id' => $mainStore->id, 'name' => 'Mobile POS - Mirpur', 'code' => 'REG-002', 'type' => 'mobile', 'status' => 'active'],
            ['store_id' => $mainStore->id, 'name' => 'Kiosk - Uttara', 'code' => 'REG-003', 'type' => 'kiosk', 'status' => 'active'],
        ];

        $createdRegisters = [];
        foreach ($registers as $regData) {
            $reg = PosRegister::firstOrCreate(
                ['store_id' => $regData['store_id'], 'code' => $regData['code']],
                $regData
            );
            $createdRegisters[] = $reg;
        }

        // ===== POS Shifts =====
        if ($adminUser && ! empty($createdRegisters)) {
            $register = $createdRegisters[0];
            $shift = PosShift::firstOrCreate(
                ['register_id' => $register->id, 'user_id' => $adminUser->id, 'status' => 'open'],
                [
                    'opened_at' => now()->subHours(8),
                    'opening_balance' => 5000.00,
                    'notes' => 'Morning shift - seeded data',
                ]
            );

            // ===== POS Sales =====
            PosSale::firstOrCreate(
                ['receipt_number' => 'POS-SEED-001'],
                [
                    'register_id' => $register->id,
                    'shift_id' => $shift->id,
                    'user_id' => $adminUser->id,
                    'subtotal' => 1500.00,
                    'tax_amount' => 180.00,
                    'discount_amount' => 50.00,
                    'total' => 1630.00,
                    'cash_amount' => 1630.00,
                    'card_amount' => 0,
                    'other_amount' => 0,
                    'change_amount' => 0,
                    'payment_status' => 'paid',
                    'status' => 'completed',
                    'notes' => 'Seeded POS sale',
                ]
            );
        }

        $this->command->info('POS module seeded successfully!');
    }
}
