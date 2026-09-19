<?php

namespace Modules\Store\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Catalog\Models\Brand;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductImage;
use Modules\Catalog\Models\ProductVariant;
use Modules\Frontend\Models\AnnouncementBar;
use Modules\Frontend\Models\Banner;
use Modules\Frontend\Models\HomepageCta;
use Modules\Frontend\Models\NavbarItem;
use Modules\Frontend\Models\Setting;
use Modules\Frontend\Models\SubnavbarItem;
use Modules\Cart\Models\Campaign;
use Modules\Store\Models\Store;

class StoreDemoDataSeeder
{
    public function seed(int $storeId): void
    {
        DB::transaction(function () use ($storeId) {
            $this->seedAnnouncementBar($storeId);
            $this->seedSiteSettings($storeId);
            $brandIds = $this->seedBrands($storeId);
            $categoryIds = $this->seedCategories($storeId);
            $navbarIds = $this->seedNavbarItems($storeId, $categoryIds);
            $this->seedSubnavbarItems($storeId, $navbarIds);
            $this->seedProducts($storeId, $brandIds, $categoryIds);
            $this->seedBanners($storeId);
            $this->seedHomepageCtas($storeId);
            $this->seedCampaigns($storeId);
        });
    }

    private function seedAnnouncementBar(int $storeId): void
    {
        $store = Store::find($storeId);
        $currency = $store?->currency_code === 'BDT' ? '৳5,000' : '$50';
        AnnouncementBar::create([
            'store_id' => $storeId,
            'left_text' => "Free shipping on orders over {$currency}",
            'center_text' => 'Welcome to '.($store?->name ?? 'Our Store'),
            'right_text' => 'Secure checkout and reliable support',
            'background_color' => '#1f2937',
            'text_color' => '#ffffff',
            'sort_order' => 1,
            'status' => 'active',
        ]);
    }

    private function seedSiteSettings(int $storeId): void
    {
        $store = Store::find($storeId);
        $settings = [
            ['group' => 'general', 'key' => 'site_name', 'value' => $store?->name ?? 'Your Store', 'type' => 'text', 'label' => 'Site Name', 'sort_order' => 1],
            ['group' => 'general', 'key' => 'site_description', 'value' => 'A curated selection of quality products with dependable service.', 'type' => 'textarea', 'label' => 'Site Description', 'sort_order' => 2],
            ['group' => 'general', 'key' => 'site_keywords', 'value' => 'online shop, quality products, ecommerce', 'type' => 'text', 'label' => 'Site Keywords', 'sort_order' => 3],
            ['group' => 'appearance', 'key' => 'primary_color', 'value' => '#0D9488', 'type' => 'color', 'label' => 'Primary Color', 'sort_order' => 1],
            ['group' => 'appearance', 'key' => 'secondary_color', 'value' => '#0F766E', 'type' => 'color', 'label' => 'Secondary Color', 'sort_order' => 2],
            ['group' => 'contact', 'key' => 'contact_email', 'value' => $store?->email ?? '', 'type' => 'email', 'label' => 'Contact Email', 'sort_order' => 1],
            ['group' => 'contact', 'key' => 'contact_phone', 'value' => $store?->phone ?? '', 'type' => 'text', 'label' => 'Contact Phone', 'sort_order' => 2],
            ['group' => 'contact', 'key' => 'contact_address', 'value' => '', 'type' => 'textarea', 'label' => 'Contact Address', 'sort_order' => 3],
            ['group' => 'social', 'key' => 'facebook_url', 'value' => 'https://facebook.com', 'type' => 'url', 'label' => 'Facebook URL', 'sort_order' => 1],
            ['group' => 'social', 'key' => 'twitter_url', 'value' => 'https://twitter.com', 'type' => 'url', 'label' => 'Twitter URL', 'sort_order' => 2],
            ['group' => 'social', 'key' => 'instagram_url', 'value' => 'https://instagram.com', 'type' => 'url', 'label' => 'Instagram URL', 'sort_order' => 3],
        ];

        foreach ($settings as $settingData) {
            Setting::create(array_merge($settingData, ['store_id' => $storeId]));
        }
    }
    
    private function seedBrands(int $storeId): array
    {
        $storeSlug = Store::find($storeId)?->slug ?? 'store';
        $brands = [
            ['name' => 'TechBrand', 'slug' => 'techbrand', 'status' => 'active'],
            ['name' => 'FashionCo', 'slug' => 'fashionco', 'status' => 'active'],
            ['name' => 'HomeStyle', 'slug' => 'homestyle', 'status' => 'active'],
        ];

        $brandIds = [];
        foreach ($brands as $brandData) {
            $brand = Brand::create(array_merge($brandData, ['store_id' => $storeId]));
            $brandIds[str_replace('-' . $storeSlug, '', $brandData['slug'])] = $brand->id;
        }
        return $brandIds;
    }

    private function seedCategories(int $storeId): array
    {
        $storeSlug = Store::find($storeId)?->slug ?? 'store';
        $categories = [
            ['name' => 'Electronics', 'slug' => 'electronics', 'image' => 'storage/demo/categories/electronics.png', 'description' => 'Electronic devices and accessories', 'status' => 'active', 'sort_order' => 1],
            ['name' => 'Clothing', 'slug' => 'clothing', 'image' => 'storage/demo/categories/clothing.png', 'description' => 'Apparel and fashion items', 'status' => 'active', 'sort_order' => 2],
            ['name' => 'Home & Kitchen', 'slug' => 'home-kitchen', 'image' => 'storage/demo/categories/home-kitchen.png', 'description' => 'Home appliances and kitchenware', 'status' => 'active', 'sort_order' => 3],
            ['name' => 'Sports & Outdoors', 'slug' => 'sports-outdoors', 'image' => 'storage/demo/categories/sports-outdoors.png', 'description' => 'Sports equipment and outdoor gear', 'status' => 'active', 'sort_order' => 4],
            ['name' => 'Beauty & Health', 'slug' => 'beauty-health', 'image' => 'storage/demo/categories/beauty-health.png', 'description' => 'Beauty products and health supplements', 'status' => 'active', 'sort_order' => 5],
            ['name' => 'Books & Media', 'slug' => 'books-media', 'image' => 'storage/demo/categories/books-media.png', 'description' => 'Books, movies, and digital media', 'status' => 'active', 'sort_order' => 6],
        ];

        $categoryIds = [];
        foreach ($categories as $catData) {
            $cat = Category::create(array_merge($catData, ['store_id' => $storeId]));
            $categoryIds[str_replace('-' . $storeSlug, '', $catData['slug'])] = $cat->id;
        }
        return $categoryIds;
    }

    private function seedProducts(int $storeId, array $brandIds, array $categoryIds): void
    {
        $imageByCategory = [
            'electronics' => 'storage/demo/products/electronics.png',
            'clothing' => 'storage/demo/products/clothing.png',
            'home-kitchen' => 'storage/demo/products/home-kitchen.png',
            'sports-outdoors' => 'storage/demo/products/sports-outdoors.png',
            'beauty-health' => 'storage/demo/products/beauty-health.png',
            'books-media' => 'storage/demo/products/books-media.png',
        ];

        $products = [
            // Electronics (4 products)
            [
                'brand_id' => $brandIds['techbrand'] ?? null,
                'name' => 'Wireless Headphones Pro',
                'slug' => 'wireless-headphones-pro',
                'short_description' => 'Premium noise-cancelling headphones',
                'description' => 'High-quality wireless headphones with active noise cancellation and 30-hour battery life.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['electronics'] ?? null,
                'categories' => ['electronics'],
                'variants' => [
                    ['name' => 'Black', 'sale_price' => 29900, 'sku_suffix' => 'BLK'],
                    ['name' => 'White', 'sale_price' => 29900, 'sku_suffix' => 'WHT'],
                ],
            ],
            [
                'brand_id' => $brandIds['techbrand'] ?? null,
                'name' => 'Smartphone X Pro Max',
                'slug' => 'smartphone-x-pro-max',
                'short_description' => 'Latest flagship smartphone',
                'description' => 'Cutting-edge smartphone with 200MP camera, 120Hz display, and 5G connectivity.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['electronics'] ?? null,
                'categories' => ['electronics'],
                'variants' => [
                    ['name' => '128GB', 'sale_price' => 119900, 'sku_suffix' => '128'],
                    ['name' => '256GB', 'sale_price' => 129900, 'sku_suffix' => '256'],
                    ['name' => '512GB', 'sale_price' => 149900, 'sku_suffix' => '512'],
                ],
            ],
            [
                'brand_id' => $brandIds['techbrand'] ?? null,
                'name' => 'UltraBook Pro 14',
                'slug' => 'ultrabook-pro-14',
                'short_description' => 'Lightweight professional laptop',
                'description' => '14-inch ultrabook with M2 chip, 16GB RAM, 512GB SSD - perfect for professionals.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['electronics'] ?? null,
                'categories' => ['electronics'],
                'variants' => [
                    ['name' => 'Silver / 512GB', 'sale_price' => 159900, 'sku_suffix' => 'SLV-512'],
                    ['name' => 'Space Gray / 1TB', 'sale_price' => 189900, 'sku_suffix' => 'GRY-1TB'],
                ],
            ],
            [
                'brand_id' => $brandIds['techbrand'] ?? null,
                'name' => 'Smart Watch Series 8',
                'slug' => 'smart-watch-series-8',
                'short_description' => 'Advanced health monitoring smartwatch',
                'description' => 'Track your fitness, heart rate, sleep, and more with this premium smartwatch.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['electronics'] ?? null,
                'categories' => ['electronics'],
                'variants' => [
                    ['name' => '41mm Aluminum', 'sale_price' => 39900, 'sku_suffix' => '41-ALU'],
                    ['name' => '45mm Stainless Steel', 'sale_price' => 69900, 'sku_suffix' => '45-STL'],
                ],
            ],

            // Clothing (5 products)
            [
                'brand_id' => $brandIds['fashionco'] ?? null,
                'name' => 'Classic Cotton T-Shirt',
                'slug' => 'classic-cotton-tshirt',
                'short_description' => 'Comfortable everyday t-shirt',
                'description' => '100% organic cotton t-shirt available in multiple colors and sizes.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['clothing'] ?? null,
                'categories' => ['clothing'],
                'variants' => [
                    ['name' => 'S / Black', 'sale_price' => 1299, 'sku_suffix' => 'S-BLK'],
                    ['name' => 'M / Black', 'sale_price' => 1299, 'sku_suffix' => 'M-BLK'],
                    ['name' => 'L / Black', 'sale_price' => 1399, 'sku_suffix' => 'L-BLK'],
                    ['name' => 'S / White', 'sale_price' => 1299, 'sku_suffix' => 'S-WHT'],
                    ['name' => 'M / White', 'sale_price' => 1299, 'sku_suffix' => 'M-WHT'],
                ],
            ],
            [
                'brand_id' => $brandIds['fashionco'] ?? null,
                'name' => 'Slim Fit Jeans',
                'slug' => 'slim-fit-jeans',
                'short_description' => 'Modern slim fit denim jeans',
                'description' => 'Premium stretch denim with a modern slim fit. Comfortable and stylish.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['clothing'] ?? null,
                'categories' => ['clothing'],
                'variants' => [
                    ['name' => '30 / Blue', 'sale_price' => 4999, 'sku_suffix' => '30-BLU'],
                    ['name' => '32 / Blue', 'sale_price' => 4999, 'sku_suffix' => '32-BLU'],
                    ['name' => '34 / Blue', 'sale_price' => 5299, 'sku_suffix' => '34-BLU'],
                    ['name' => '30 / Black', 'sale_price' => 4999, 'sku_suffix' => '30-BLK'],
                    ['name' => '32 / Black', 'sale_price' => 4999, 'sku_suffix' => '32-BLK'],
                ],
            ],
            [
                'brand_id' => $brandIds['fashionco'] ?? null,
                'name' => 'Hooded Sweatshirt',
                'slug' => 'hooded-sweatshirt',
                'short_description' => 'Cozy fleece hoodie',
                'description' => 'Soft brushed fleece hoodie with kangaroo pocket and adjustable drawstring hood.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['clothing'] ?? null,
                'categories' => ['clothing'],
                'variants' => [
                    ['name' => 'S / Gray', 'sale_price' => 3499, 'sku_suffix' => 'S-GRY'],
                    ['name' => 'M / Gray', 'sale_price' => 3499, 'sku_suffix' => 'M-GRY'],
                    ['name' => 'L / Gray', 'sale_price' => 3699, 'sku_suffix' => 'L-GRY'],
                    ['name' => 'XL / Gray', 'sale_price' => 3699, 'sku_suffix' => 'XL-GRY'],
                ],
            ],
            [
                'brand_id' => $brandIds['fashionco'] ?? null,
                'name' => 'Summer Dress',
                'slug' => 'summer-dress',
                'short_description' => 'Lightweight floral summer dress',
                'description' => 'Breathable cotton-linen blend dress with floral print. Perfect for warm days.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['clothing'] ?? null,
                'categories' => ['clothing'],
                'variants' => [
                    ['name' => 'S / Floral', 'sale_price' => 4299, 'sku_suffix' => 'S-FLR'],
                    ['name' => 'M / Floral', 'sale_price' => 4299, 'sku_suffix' => 'M-FLR'],
                    ['name' => 'L / Floral', 'sale_price' => 4499, 'sku_suffix' => 'L-FLR'],
                ],
            ],
            [
                'brand_id' => $brandIds['fashionco'] ?? null,
                'name' => 'Wool Blend Coat',
                'slug' => 'wool-blend-coat',
                'short_description' => 'Elegant winter coat',
                'description' => 'Sophisticated wool-blend coat with notched lapels and button closure.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['clothing'] ?? null,
                'categories' => ['clothing'],
                'variants' => [
                    ['name' => 'S / Camel', 'sale_price' => 12999, 'sku_suffix' => 'S-CML'],
                    ['name' => 'M / Camel', 'sale_price' => 12999, 'sku_suffix' => 'M-CML'],
                    ['name' => 'L / Camel', 'sale_price' => 13499, 'sku_suffix' => 'L-CML'],
                ],
            ],

            // Home & Kitchen (4 products)
            [
                'brand_id' => $brandIds['homestyle'] ?? null,
                'name' => 'Smart LED Desk Lamp',
                'slug' => 'smart-led-desk-lamp',
                'short_description' => 'Adjustable smart desk lamp',
                'description' => 'Touch-controlled LED desk lamp with adjustable brightness and color temperature.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['home-kitchen'] ?? null,
                'categories' => ['home-kitchen'],
                'variants' => [
                    ['name' => 'Default', 'sale_price' => 4999, 'sku_suffix' => 'DEF'],
                ],
            ],
            [
                'brand_id' => $brandIds['homestyle'] ?? null,
                'name' => 'Air Fryer XL',
                'slug' => 'air-fryer-xl',
                'short_description' => 'Large capacity air fryer',
                'description' => '5.5L air fryer with 8 preset cooking programs. Healthy cooking with less oil.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['home-kitchen'] ?? null,
                'categories' => ['home-kitchen'],
                'variants' => [
                    ['name' => 'Black', 'sale_price' => 12999, 'sku_suffix' => 'BLK'],
                    ['name' => 'White', 'sale_price' => 12999, 'sku_suffix' => 'WHT'],
                ],
            ],
            [
                'brand_id' => $brandIds['homestyle'] ?? null,
                'name' => 'Robot Vacuum Cleaner',
                'slug' => 'robot-vacuum-cleaner',
                'short_description' => 'Smart robot vacuum with mapping',
                'description' => 'LiDAR navigation, 2700Pa suction, app control, and auto-empty dock.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['home-kitchen'] ?? null,
                'categories' => ['home-kitchen'],
                'variants' => [
                    ['name' => 'Standard', 'sale_price' => 34999, 'sku_suffix' => 'STD'],
                    ['name' => 'Pro with Mop', 'sale_price' => 42999, 'sku_suffix' => 'PRO'],
                ],
            ],
            [
                'brand_id' => $brandIds['homestyle'] ?? null,
                'name' => 'Coffee Maker Deluxe',
                'slug' => 'coffee-maker-deluxe',
                'short_description' => 'Programmable coffee machine',
                'description' => '12-cup programmable coffee maker with strength control and thermal carafe.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['home-kitchen'] ?? null,
                'categories' => ['home-kitchen'],
                'variants' => [
                    ['name' => 'Stainless Steel', 'sale_price' => 8999, 'sku_suffix' => 'STL'],
                ],
            ],

            // Sports & Outdoors (4 products)
            [
                'brand_id' => $brandIds['techbrand'] ?? null,
                'name' => 'Yoga Mat Premium',
                'slug' => 'yoga-mat-premium',
                'short_description' => 'Non-slip exercise yoga mat',
                'description' => 'Extra thick 6mm eco-friendly TPE yoga mat with alignment lines and carrying strap.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['sports-outdoors'] ?? null,
                'categories' => ['sports-outdoors'],
                'variants' => [
                    ['name' => 'Purple', 'sale_price' => 2499, 'sku_suffix' => 'PUR'],
                    ['name' => 'Teal', 'sale_price' => 2499, 'sku_suffix' => 'TEA'],
                    ['name' => 'Black', 'sale_price' => 2499, 'sku_suffix' => 'BLK'],
                ],
            ],
            [
                'brand_id' => $brandIds['techbrand'] ?? null,
                'name' => 'Resistance Bands Set',
                'slug' => 'resistance-bands-set',
                'short_description' => '5-piece resistance band set',
                'description' => 'Stackable resistance bands (10-150 lbs) with handles, ankle straps, and door anchor.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['sports-outdoors'] ?? null,
                'categories' => ['sports-outdoors'],
                'variants' => [
                    ['name' => 'Standard Set', 'sale_price' => 1899, 'sku_suffix' => 'STD'],
                    ['name' => 'Heavy Duty Set', 'sale_price' => 2799, 'sku_suffix' => 'HD'],
                ],
            ],
            [
                'brand_id' => $brandIds['techbrand'] ?? null,
                'name' => 'Camping Tent 4-Person',
                'slug' => 'camping-tent-4-person',
                'short_description' => 'Waterproof family camping tent',
                'description' => 'Easy-setup 4-person tent with rainfly, mesh windows, and gear loft. 3-season rated.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['sports-outdoors'] ?? null,
                'categories' => ['sports-outdoors'],
                'variants' => [
                    ['name' => 'Green', 'sale_price' => 8999, 'sku_suffix' => 'GRN'],
                    ['name' => 'Blue', 'sale_price' => 8999, 'sku_suffix' => 'BLU'],
                ],
            ],
            [
                'brand_id' => $brandIds['techbrand'] ?? null,
                'name' => 'Dumbbell Set 20kg',
                'slug' => 'dumbbell-set-20kg',
                'short_description' => 'Adjustable dumbbell pair',
                'description' => 'Space-saving adjustable dumbbells (2-20kg each) with quick-lock mechanism.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['sports-outdoors'] ?? null,
                'categories' => ['sports-outdoors'],
                'variants' => [
                    ['name' => 'Pair', 'sale_price' => 15999, 'sku_suffix' => 'PAIR'],
                ],
            ],

            // Beauty & Health (4 products)
            [
                'brand_id' => $brandIds['homestyle'] ?? null,
                'name' => 'Vitamin C Serum',
                'slug' => 'vitamin-c-serum',
                'short_description' => 'Brightening facial serum',
                'description' => '20% Vitamin C serum with Hyaluronic Acid and Vitamin E for radiant skin.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['beauty-health'] ?? null,
                'categories' => ['beauty-health'],
                'variants' => [
                    ['name' => '30ml', 'sale_price' => 1899, 'sku_suffix' => '30ML'],
                ],
            ],
            [
                'brand_id' => $brandIds['homestyle'] ?? null,
                'name' => 'Electric Toothbrush',
                'slug' => 'electric-toothbrush',
                'short_description' => 'Sonic rechargeable toothbrush',
                'description' => '40,000 VPM sonic technology, 5 brushing modes, 60-day battery life.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['beauty-health'] ?? null,
                'categories' => ['beauty-health'],
                'variants' => [
                    ['name' => 'White', 'sale_price' => 5999, 'sku_suffix' => 'WHT'],
                    ['name' => 'Black', 'sale_price' => 5999, 'sku_suffix' => 'BLK'],
                    ['name' => 'Pink', 'sale_price' => 5999, 'sku_suffix' => 'PNK'],
                ],
            ],
            [
                'brand_id' => $brandIds['homestyle'] ?? null,
                'name' => 'Hair Dryer Ionic',
                'slug' => 'hair-dryer-ionic',
                'short_description' => 'Professional ionic hair dryer',
                'description' => '1875W ionic technology with 3 heat/2 speed settings and cool shot button.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['beauty-health'] ?? null,
                'categories' => ['beauty-health'],
                'variants' => [
                    ['name' => 'Rose Gold', 'sale_price' => 4499, 'sku_suffix' => 'ROG'],
                    ['name' => 'Titanium', 'sale_price' => 4499, 'sku_suffix' => 'TIT'],
                ],
            ],
            [
                'brand_id' => $brandIds['homestyle'] ?? null,
                'name' => 'Multivitamin Gummies',
                'slug' => 'multivitamin-gummies',
                'short_description' => 'Daily multivitamin gummies',
                'description' => 'Delicious fruit-flavored gummies with 13 essential vitamins and minerals.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['beauty-health'] ?? null,
                'categories' => ['beauty-health'],
                'variants' => [
                    ['name' => '60 Count', 'sale_price' => 1299, 'sku_suffix' => '60CT'],
                    ['name' => '120 Count', 'sale_price' => 2199, 'sku_suffix' => '120CT'],
                ],
            ],

            // Books & Media (4 products)
            [
                'brand_id' => $brandIds['fashionco'] ?? null,
                'name' => 'The Art of Programming',
                'slug' => 'the-art-of-programming',
                'short_description' => 'Comprehensive programming guide',
                'description' => 'Master software development with this 1000-page guide covering algorithms, patterns, and best practices.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['books-media'] ?? null,
                'categories' => ['books-media'],
                'variants' => [
                    ['name' => 'Hardcover', 'sale_price' => 4999, 'sku_suffix' => 'HC'],
                    ['name' => 'Paperback', 'sale_price' => 3499, 'sku_suffix' => 'PB'],
                    ['name' => 'E-Book', 'sale_price' => 2499, 'sku_suffix' => 'EB'],
                ],
            ],
            [
                'brand_id' => $brandIds['fashionco'] ?? null,
                'name' => 'Cookbook: World Cuisines',
                'slug' => 'cookbook-world-cuisines',
                'short_description' => 'International recipe collection',
                'description' => '200+ recipes from 50 countries with step-by-step photos and cultural notes.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['books-media'] ?? null,
                'categories' => ['books-media'],
                'variants' => [
                    ['name' => 'Hardcover', 'sale_price' => 3999, 'sku_suffix' => 'HC'],
                ],
            ],
            [
                'brand_id' => $brandIds['fashionco'] ?? null,
                'name' => 'Sci-Fi Movie Collection',
                'slug' => 'sci-fi-movie-collection',
                'short_description' => '10 classic sci-fi films 4K',
                'description' => 'Remastered 4K UHD collection of the greatest sci-fi movies with bonus features.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['books-media'] ?? null,
                'categories' => ['books-media'],
                'variants' => [
                    ['name' => '4K UHD Box Set', 'sale_price' => 9999, 'sku_suffix' => '4K'],
                    ['name' => 'Blu-ray Box Set', 'sale_price' => 6999, 'sku_suffix' => 'BR'],
                ],
            ],
            [
                'brand_id' => $brandIds['fashionco'] ?? null,
                'name' => 'Learn Guitar Complete',
                'slug' => 'learn-guitar-complete',
                'short_description' => 'Beginner to advanced guitar course',
                'description' => 'Comprehensive video course with 100+ lessons, tabs, and practice tracks.',
                'product_type' => 'digital',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['books-media'] ?? null,
                'categories' => ['books-media'],
                'variants' => [
                    ['name' => 'Lifetime Access', 'sale_price' => 2999, 'sku_suffix' => 'LIFE'],
                ],
            ],
        ];

        foreach ($products as $productData) {
            $categorySlugs = $productData['categories'] ?? [];
            $variants = $productData['variants'] ?? [];
            unset($productData['categories'], $productData['variants']);

            $product = Product::create(array_merge($productData, [
                'store_id' => $storeId,
                'published_at' => now(),
                'is_homepage' => true,
            ]));

            if (!empty($categorySlugs)) {
                $catIds = array_values(array_intersect_key($categoryIds, array_flip($categorySlugs)));
                $product->categories()->syncWithoutDetaching($catIds);
            }

            $product->images()->create([
                'image_url' => $imageByCategory[$categorySlugs[0] ?? '']
                    ?? 'storage/demo/products/default.svg',
                'alt_text' => $product->name,
                'sort_order' => 0,
            ]);

            foreach ($variants as $i => $variantData) {
                $variantData['sku_suffix'] ??= '';
                $skuSuffix = $variantData['sku_suffix'];
                unset($variantData['sku_suffix']);

                $product->variants()->create(array_merge($variantData, [
                    'sku' => strtoupper(Str::slug($product->slug)) . '-' . $storeId . '-' . $skuSuffix,
                    'barcode' => (string) Str::uuid(),
                    'cost_price' => (int) round(($variantData['sale_price'] ?? 0) * 0.7),
                    'status' => 'active',
                    'track_inventory' => true,
                ]));
            }
        }
    }

    private function seedBanners(int $storeId): void
    {
        $banners = [
            [
                'store_id' => $storeId,
                'banner_image' => 'https://admin.onehaatbd.com/storage/banners/7CVCKbhuLnKEN6LGJ62ZDQSqIJcCZntYvoCpdYIi.png',
                'title' => 'Discover your next favourite',
                'subtitle' => 'Explore our latest products, selected for everyday living.',
                'smtag' => 'Featured collection',
                'primary_btn' => 'Shop now',
                'primary_btn_url' => '/products',
                'primary_btn_color' => '#1a462f',
                'primary_btn_text_color' => '#ffffff',
                'secondary_btn' => 'Browse categories',
                'secondary_btn_url' => '/categories',
                'secondary_btn_color' => '#ffffff',
                'secondary_btn_text_color' => '#1f2937',
                'sort_order' => 0,
                'status' => 'active',
            ],
            [
                'store_id' => $storeId,
                'banner_image' => 'https://admin.onehaatbd.com/storage/banners/cUgMETtch0m9yYFiWJgiEne1ZljIgkyUKJAcINdt.jpg',
                'title' => 'Elevate Your Audio Experience',
                'subtitle' => 'Explore premium TWS Earbuds, Over-Ear Headphones, and Wireless Neckbands built for high-performance sound.',
                'smtag' => 'Special Offer',
                'primary_btn' => 'Shop Now',
                'primary_btn_url' => '/shop',
                'primary_btn_color' => '#2b6c45',
                'primary_btn_text_color' => '#ffffff',
                'secondary_btn' => 'Get Yours Today',
                'secondary_btn_url' => '/get-yours-today',
                'secondary_btn_color' => '#ffffff',
                'secondary_btn_text_color' => '#1f2937',
                'sort_order' => 1,
                'status' => 'active',
            ],
            [
                'store_id' => $storeId,
                'banner_image' => 'https://admin.onehaatbd.com/storage/banners/vbsOQqAZUQamS3lWshHMy9eJv1lvGBJNjTncXAuE.jpg',
                'title' => 'Ultimate Tech Essentials',
                'subtitle' => 'Upgrade your daily setup with modern smartwatches, wireless earbuds, high-speed power banks, and desk accessories.',
                'smtag' => 'Get Yours Today',
                'primary_btn' => 'Get Offers',
                'primary_btn_url' => '/all-gadgets',
                'primary_btn_color' => '#2b6c45',
                'primary_btn_text_color' => '#ffffff',
                'secondary_btn' => 'View Best Sellers',
                'secondary_btn_url' => '/best-sellers',
                'secondary_btn_color' => '#ffffff',
                'secondary_btn_text_color' => '#2b6c45',
                'sort_order' => 2,
                'status' => 'active',
            ],
        ];

        foreach ($banners as $bannerData) {
            Banner::create($bannerData);
        }
    }

    private function seedHomepageCtas(int $storeId): void
    {
        $primaryColor = '#0D9488';
        $ctas = [
            [
                'store_id' => $storeId,
                'cta_style' => 'style1',
                'title' => 'Free Shipping',
                'subtitle' => 'On orders over $50',
                'description' => 'Enjoy free delivery on all orders above $50',
                'button_text' => 'Learn More',
                'button_link' => '/shipping-info',
                'background_color' => '#f0fdf4',
                'text_color' => '#166534',
                'button_color' => $primaryColor,
                'button_text_color' => '#ffffff',
                'sort_order' => 1,
                'status' => 'active',
            ],
            [
                'store_id' => $storeId,
                'cta_style' => 'style2',
                'title' => 'New Collection',
                'subtitle' => 'Explore the latest arrivals',
                'description' => 'Check out our latest fashion collection',
                'button_text' => 'Shop Collection',
                'button_link' => '/categories/clothing',
                'background_color' => '#fef3c7',
                'text_color' => '#92400e',
                'button_color' => $primaryColor,
                'button_text_color' => '#ffffff',
                'sort_order' => 2,
                'status' => 'active',
            ],
        ];

        foreach ($ctas as $ctaData) {
            HomepageCta::create($ctaData);
        }
    }

    private function seedNavbarItems(int $storeId, array $categoryIds): array
    {
        $storeSlug = Store::find($storeId)?->slug ?? 'store';
        $navItems = [
            ['store_id' => $storeId, 'name' => 'Home', 'slug' => 'home-' . $storeSlug, 'url' => '/', 'sort_order' => 1, 'status' => 'active'],
            ['store_id' => $storeId, 'name' => 'Shop', 'slug' => 'shop-' . $storeSlug, 'url' => '/products', 'sort_order' => 2, 'status' => 'active'],
            ['store_id' => $storeId, 'name' => 'Categories', 'slug' => 'categories-' . $storeSlug, 'url' => '/categories', 'sort_order' => 3, 'status' => 'active'],
            ['store_id' => $storeId, 'name' => 'About Us', 'slug' => 'about-us-' . $storeSlug, 'url' => '/about', 'sort_order' => 4, 'status' => 'active'],
            ['store_id' => $storeId, 'name' => 'Contact', 'slug' => 'contact-' . $storeSlug, 'url' => '/contact', 'sort_order' => 5, 'status' => 'active'],
        ];

        $navbarIds = [];
        foreach ($navItems as $itemData) {
            $navItem = NavbarItem::create($itemData);
            $navbarIds[$navItem->name] = $navItem->id;
        }
        return $navbarIds;
    }

    private function seedSubnavbarItems(int $storeId, array $navbarIds): void
    {
        $storeSlug = Store::find($storeId)?->slug ?? 'store';
        $subItems = [
            'Shop' => [
                ['name' => 'New Arrivals', 'slug' => 'new-arrivals-' . $storeSlug, 'url' => '/products?filter=new', 'sort_order' => 1],
                ['name' => 'Best Sellers', 'slug' => 'best-sellers-' . $storeSlug, 'url' => '/products?filter=bestselling', 'sort_order' => 2],
                ['name' => 'On Sale', 'slug' => 'on-sale-' . $storeSlug, 'url' => '/products?filter=sale', 'sort_order' => 3],
            ],
            'Categories' => [
                ['name' => 'Electronics', 'slug' => 'electronics-' . $storeSlug, 'url' => '/categories/electronics', 'sort_order' => 1],
                ['name' => 'Clothing', 'slug' => 'clothing-' . $storeSlug, 'url' => '/categories/clothing', 'sort_order' => 2],
                ['name' => 'Home & Kitchen', 'slug' => 'home-kitchen-' . $storeSlug, 'url' => '/categories/home-kitchen', 'sort_order' => 3],
            ],
            'About Us' => [
                ['name' => 'Our Story', 'slug' => 'our-story-' . $storeSlug, 'url' => '/about/story', 'sort_order' => 1],
                ['name' => 'Team', 'slug' => 'team-' . $storeSlug, 'url' => '/about/team', 'sort_order' => 2],
                ['name' => 'Careers', 'slug' => 'careers-' . $storeSlug, 'url' => '/about/careers', 'sort_order' => 3],
            ],
        ];

        foreach ($subItems as $parentLabel => $items) {
            $parentId = $navbarIds[$parentLabel] ?? null;
            if (! $parentId) {
                continue;
            }
            foreach ($items as $itemData) {
                SubnavbarItem::create(array_merge($itemData, [
                    'store_id' => $storeId,
                    'navbar_item_id' => $parentId,
                    'status' => 'active',
                ]));
            }
        }
    }

    private function seedCampaigns(int $storeId): void
    {
        $campaigns = [
            [
                'store_id' => $storeId,
                'name' => 'Welcome Discount',
                'slug' => 'welcome-discount',
                'description' => 'Get 10% off on your first order',
                'banner_image' => 'https://via.placeholder.com/800x400/22c55e/ffffff?text=Welcome+10%25+Off',
                'button_text' => 'Claim Offer',
                'button_url' => '/products?discount=welcome',
                'priority' => 1,
                'is_featured' => true,
                'is_active' => true,
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => now()->addDays(30),
            ],
            [
                'store_id' => $storeId,
                'name' => 'Weekend Special',
                'slug' => 'weekend-special',
                'description' => 'Special weekend deals on electronics',
                'banner_image' => 'https://via.placeholder.com/800x400/3b82f6/ffffff?text=Weekend+Deals',
                'button_text' => 'Shop Now',
                'button_url' => '/categories/electronics',
                'priority' => 2,
                'is_featured' => false,
                'is_active' => true,
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => now()->addDays(7),
            ],
        ];

        foreach ($campaigns as $campaignData) {
            Campaign::create($campaignData);
        }
    }
}
