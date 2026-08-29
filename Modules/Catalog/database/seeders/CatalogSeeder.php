<?php

namespace Modules\Catalog\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogSeeder extends Seeder
{
    public function run()
    {
        DB::table('brands')->insertOrIgnore([
            ['name' => 'Acme', 'slug' => 'acme', 'logo_url' => null, 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Contoso', 'slug' => 'contoso', 'logo_url' => null, 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('categories')->insertOrIgnore([
            ['parent_id' => null, 'name' => 'Electronics', 'slug' => 'electronics', 'description' => 'Latest gadgets and electronic devices', 'image_url' => 'https://via.placeholder.com/400x300/1e3a8a/ffffff?text=Electronics', 'sort_order' => 0, 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['parent_id' => null, 'name' => 'Clothing', 'slug' => 'clothing', 'description' => 'Trendy fashion for everyone', 'image_url' => 'https://via.placeholder.com/400x300/7c3aed/ffffff?text=Clothing', 'sort_order' => 1, 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['parent_id' => null, 'name' => 'Sports', 'slug' => 'sports', 'description' => 'Sports equipment and accessories', 'image_url' => 'https://via.placeholder.com/400x300/059669/ffffff?text=Sports', 'sort_order' => 2, 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['parent_id' => null, 'name' => 'Home & Kitchen', 'slug' => 'home-kitchen', 'description' => 'Everything for your home', 'image_url' => 'https://via.placeholder.com/400x300/d97706/ffffff?text=Home+%26+Kitchen', 'sort_order' => 3, 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['parent_id' => null, 'name' => 'Books', 'slug' => 'books', 'description' => 'Books and educational materials', 'image_url' => 'https://via.placeholder.com/400x300/dc2626/ffffff?text=Books', 'sort_order' => 4, 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
