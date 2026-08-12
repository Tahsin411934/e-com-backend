<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->command->info('====================================');
        $this->command->info('   DATABASE SEEDING STARTED');
        $this->command->info('====================================');

        // Order matters for foreign key dependencies
        $this->call(\Modules\Identity\Database\Seeders\IdentityDatabaseSeeder::class);
        $this->call(\Modules\Account\Database\Seeders\AccountDatabaseSeeder::class);
        $this->call(\Modules\Store\Database\Seeders\StoreDatabaseSeeder::class);
        $this->call(\Modules\Catalog\Database\Seeders\CatalogDatabaseSeeder::class);
        $this->call(\Modules\Frontend\Database\Seeders\FrontendDatabaseSeeder::class);
        $this->call(\Modules\Inventory\Database\Seeders\InventoryDatabaseSeeder::class);
        $this->call(\Modules\Cart\Database\Seeders\CartDatabaseSeeder::class);
        $this->call(\Modules\Order\Database\Seeders\OrderDatabaseSeeder::class);
        $this->call(\Modules\Pos\Database\Seeders\PosDatabaseSeeder::class);
        $this->call(\Modules\Reviews\Database\Seeders\ReviewsDatabaseSeeder::class);

        $this->command->info('====================================');
        $this->command->info('   ALL DATABASE SEEDING COMPLETED');
        $this->command->info('====================================');
    }
}