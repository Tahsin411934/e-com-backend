<?php

namespace Modules\Frontend\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Frontend\Models\NavbarItem;
use Modules\Frontend\Models\SubnavbarItem;

class NavbarItemSeeder extends Seeder
{
    public function run(): void
    {
        // ===== 6 Main Navbar Items (Garments Focused) =====
        $navbarItems = [
            ['name' => 'Home', 'slug' => 'home', 'url' => '/', 'icon' => 'fa-solid fa-home', 'sort_order' => 1, 'status' => 'active'],
            ['name' => 'Boys', 'slug' => 'boys', 'url' => '/boys', 'icon' => 'fa-solid fa-person', 'sort_order' => 2, 'status' => 'active'],
            ['name' => 'Women', 'slug' => 'women', 'url' => '/women', 'icon' => 'fa-solid fa-person-dress', 'sort_order' => 3, 'status' => 'active'],
            ['name' => 'Accessories', 'slug' => 'accessories', 'url' => '/accessories', 'icon' => 'fa-solid fa-bag-shopping', 'sort_order' => 4, 'status' => 'active'],
            ['name' => 'Collections', 'slug' => 'collections', 'url' => '/collections', 'icon' => 'fa-solid fa-tags', 'sort_order' => 5, 'status' => 'active'],
            ['name' => 'Pages', 'slug' => 'pages', 'url' => null, 'icon' => 'fa-solid fa-file', 'sort_order' => 6, 'status' => 'active'],
        ];

        foreach ($navbarItems as $data) {
            NavbarItem::firstOrCreate(['slug' => $data['slug']], $data);
        }

        // ===== Subnavbar Items =====
        $boys = NavbarItem::where('slug', 'boys')->first();
        $women = NavbarItem::where('slug', 'women')->first();
        $accessories = NavbarItem::where('slug', 'accessories')->first();
        $collections = NavbarItem::where('slug', 'collections')->first();
        $pages = NavbarItem::where('slug', 'pages')->first();

        $subnavbarItems = [
            // Boys sub-items
            ['navbar_item_id' => $boys?->id, 'name' => 'T-Shirts', 'slug' => 'boys-tshirts', 'url' => '/subnavbar/boys-tshirts', 'icon' => 'fa-solid fa-shirt', 'sort_order' => 1, 'status' => 'active'],
            ['navbar_item_id' => $boys?->id, 'name' => 'Shirts', 'slug' => 'boys-shirts', 'url' => '/subnavbar/boys-shirts', 'icon' => 'fa-solid fa-shirt', 'sort_order' => 2, 'status' => 'active'],
            ['navbar_item_id' => $boys?->id, 'name' => 'Pants & Jeans', 'slug' => 'boys-pants', 'url' => '/subnavbar/boys-pants', 'icon' => 'fa-solid fa-shirt', 'sort_order' => 3, 'status' => 'active'],
            ['navbar_item_id' => $boys?->id, 'name' => 'Panjabi & Kurta', 'slug' => 'boys-panjabi', 'url' => '/subnavbar/boys-panjabi', 'icon' => 'fa-solid fa-shirt', 'sort_order' => 4, 'status' => 'active'],
            ['navbar_item_id' => $boys?->id, 'name' => 'Suits & Blazers', 'slug' => 'boys-suits', 'url' => '/subnavbar/boys-suits', 'icon' => 'fa-solid fa-shirt', 'sort_order' => 5, 'status' => 'active'],
            ['navbar_item_id' => $boys?->id, 'name' => 'Winter Wear', 'slug' => 'boys-winter', 'url' => '/subnavbar/boys-winter', 'icon' => 'fa-solid fa-snowflake', 'sort_order' => 6, 'status' => 'active'],
            ['navbar_item_id' => $boys?->id, 'name' => 'Underwear & Socks', 'slug' => 'boys-underwear', 'url' => '/subnavbar/boys-underwear', 'icon' => 'fa-solid fa-shirt', 'sort_order' => 7, 'status' => 'active'],
            ['navbar_item_id' => $boys?->id, 'name' => 'Traditional Wear', 'slug' => 'boys-traditional', 'url' => '/subnavbar/boys-traditional', 'icon' => 'fa-solid fa-shirt', 'sort_order' => 8, 'status' => 'active'],

            // Women sub-items
            ['navbar_item_id' => $women?->id, 'name' => 'Sarees', 'slug' => 'women-sarees', 'url' => '/subnavbar/women-sarees', 'icon' => 'fa-solid fa-person-dress', 'sort_order' => 1, 'status' => 'active'],
            ['navbar_item_id' => $women?->id, 'name' => 'Salwar Kameez', 'slug' => 'women-salwar', 'url' => '/subnavbar/women-salwar', 'icon' => 'fa-solid fa-person-dress', 'sort_order' => 2, 'status' => 'active'],
            ['navbar_item_id' => $women?->id, 'name' => 'Kurtis & Tunics', 'slug' => 'women-kurtis', 'url' => '/subnavbar/women-kurtis', 'icon' => 'fa-solid fa-person-dress', 'sort_order' => 3, 'status' => 'active'],
            ['navbar_item_id' => $women?->id, 'name' => 'Dresses', 'slug' => 'women-dresses', 'url' => '/subnavbar/women-dresses', 'icon' => 'fa-solid fa-person-dress', 'sort_order' => 4, 'status' => 'active'],
            ['navbar_item_id' => $women?->id, 'name' => 'Tops & Blouses', 'slug' => 'women-tops', 'url' => '/subnavbar/women-tops', 'icon' => 'fa-solid fa-person-dress', 'sort_order' => 5, 'status' => 'active'],
            ['navbar_item_id' => $women?->id, 'name' => 'Jeans & Pants', 'slug' => 'women-jeans', 'url' => '/subnavbar/women-jeans', 'icon' => 'fa-solid fa-person-dress', 'sort_order' => 6, 'status' => 'active'],
            ['navbar_item_id' => $women?->id, 'name' => 'Hijabs & Scarves', 'slug' => 'women-hijabs', 'url' => '/subnavbar/women-hijabs', 'icon' => 'fa-solid fa-hand', 'sort_order' => 7, 'status' => 'active'],
            ['navbar_item_id' => $women?->id, 'name' => 'Abayas & Burqas', 'slug' => 'women-abayas', 'url' => '/subnavbar/women-abayas', 'icon' => 'fa-solid fa-person-dress', 'sort_order' => 8, 'status' => 'active'],

            // Accessories sub-items
            ['navbar_item_id' => $accessories?->id, 'name' => 'Bags & Backpacks', 'slug' => 'accessories-bags', 'url' => '/subnavbar/accessories-bags', 'icon' => 'fa-solid fa-bag-shopping', 'sort_order' => 1, 'status' => 'active'],
            ['navbar_item_id' => $accessories?->id, 'name' => 'Watches', 'slug' => 'accessories-watches', 'url' => '/subnavbar/accessories-watches', 'icon' => 'fa-solid fa-clock', 'sort_order' => 2, 'status' => 'active'],
            ['navbar_item_id' => $accessories?->id, 'name' => 'Jewelry', 'slug' => 'accessories-jewelry', 'url' => '/subnavbar/accessories-jewelry', 'icon' => 'fa-solid fa-gem', 'sort_order' => 3, 'status' => 'active'],
            ['navbar_item_id' => $accessories?->id, 'name' => 'Belts & Wallets', 'slug' => 'accessories-belts', 'url' => '/subnavbar/accessories-belts', 'icon' => 'fa-solid fa-belt', 'sort_order' => 4, 'status' => 'active'],
            ['navbar_item_id' => $accessories?->id, 'name' => 'Sunglasses', 'slug' => 'accessories-sunglasses', 'url' => '/subnavbar/accessories-sunglasses', 'icon' => 'fa-solid fa-glasses', 'sort_order' => 5, 'status' => 'active'],
            ['navbar_item_id' => $accessories?->id, 'name' => 'Shoes', 'slug' => 'accessories-shoes', 'url' => '/subnavbar/accessories-shoes', 'icon' => 'fa-solid fa-shoe-prints', 'sort_order' => 6, 'status' => 'active'],
            ['navbar_item_id' => $accessories?->id, 'name' => 'Caps & Hats', 'slug' => 'accessories-caps', 'url' => '/subnavbar/accessories-caps', 'icon' => 'fa-solid fa-hat-cowboy', 'sort_order' => 7, 'status' => 'active'],
            ['navbar_item_id' => $accessories?->id, 'name' => 'Perfumes & Deos', 'slug' => 'accessories-perfumes', 'url' => '/subnavbar/accessories-perfumes', 'icon' => 'fa-solid fa-spray-can', 'sort_order' => 8, 'status' => 'active'],

            // Collections sub-items
            ['navbar_item_id' => $collections?->id, 'name' => 'New Arrivals', 'slug' => 'new-arrivals', 'url' => '/subnavbar/new-arrivals', 'icon' => 'fa-solid fa-clock', 'sort_order' => 1, 'status' => 'active'],
            ['navbar_item_id' => $collections?->id, 'name' => 'Best Sellers', 'slug' => 'best-sellers', 'url' => '/subnavbar/best-sellers', 'icon' => 'fa-solid fa-fire', 'sort_order' => 2, 'status' => 'active'],
            ['navbar_item_id' => $collections?->id, 'name' => 'Summer Collection', 'slug' => 'summer-collection', 'url' => '/subnavbar/summer-collection', 'icon' => 'fa-solid fa-sun', 'sort_order' => 3, 'status' => 'active'],
            ['navbar_item_id' => $collections?->id, 'name' => 'Eid Collection', 'slug' => 'eid-collection', 'url' => '/subnavbar/eid-collection', 'icon' => 'fa-solid fa-moon', 'sort_order' => 4, 'status' => 'active'],
            ['navbar_item_id' => $collections?->id, 'name' => 'Sale', 'slug' => 'sale', 'url' => '/subnavbar/sale', 'icon' => 'fa-solid fa-percent', 'sort_order' => 5, 'status' => 'active'],

            // Pages sub-items
            ['navbar_item_id' => $pages?->id, 'name' => 'About Us', 'slug' => 'about-us', 'url' => '/about', 'icon' => 'fa-solid fa-info-circle', 'sort_order' => 1, 'status' => 'active'],
            ['navbar_item_id' => $pages?->id, 'name' => 'Contact', 'slug' => 'contact', 'url' => '/contact', 'icon' => 'fa-solid fa-envelope', 'sort_order' => 2, 'status' => 'active'],
            ['navbar_item_id' => $pages?->id, 'name' => 'FAQ', 'slug' => 'faq', 'url' => '/faq', 'icon' => 'fa-solid fa-question-circle', 'sort_order' => 3, 'status' => 'active'],
            ['navbar_item_id' => $pages?->id, 'name' => 'Shipping Info', 'slug' => 'shipping-info', 'url' => '/shipping', 'icon' => 'fa-solid fa-truck', 'sort_order' => 4, 'status' => 'active'],
            ['navbar_item_id' => $pages?->id, 'name' => 'Return Policy', 'slug' => 'return-policy', 'url' => '/returns', 'icon' => 'fa-solid fa-rotate-left', 'sort_order' => 5, 'status' => 'active'],
        ];

        foreach ($subnavbarItems as $data) {
            SubnavbarItem::firstOrCreate(['slug' => $data['slug']], $data);
        }

        $this->command->info('Navbar items and subnavbar items seeded successfully!');
    }
}
