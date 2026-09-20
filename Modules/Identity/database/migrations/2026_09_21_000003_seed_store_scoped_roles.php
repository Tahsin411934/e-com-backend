<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'Store Admin' => 'Full administration of one assigned store',
            'Store Staff' => 'General staff access for one assigned store',
            'POS Admin' => 'POS registers, shifts, sales and reports',
            'Cashier' => 'POS selling and shift operations',
            'Inventory Manager' => 'Inventory and purchase operations',
        ] as $name => $description) {
            DB::table('roles')->updateOrInsert(
                ['name' => $name],
                ['description' => $description, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    public function down(): void
    {
        DB::table('roles')->whereIn('name', [
            'Store Admin', 'Store Staff', 'POS Admin', 'Cashier', 'Inventory Manager',
        ])->delete();
    }
};
