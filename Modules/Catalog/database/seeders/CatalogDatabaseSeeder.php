<?php

namespace Modules\Catalog\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Catalog\Models\Brand;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductImage;
use Modules\Catalog\Models\ProductVariant;

class CatalogDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(GarmentsProductSeeder::class);
    }
}

// Below this line is the old code, keep it but commented or remove
// The entire previous content is replaced by calling GarmentsProductSeeder

/*
    // Old code below:
    public function run(): void
    {
        // ===== Brands =====
        $brands = [
            ['name' => 'Nike', 'slug' => 'nike', 'status' => 'active'],
            ['name' => 'Adidas', 'slug' => 'adidas', 'status' => 'active'],
            ['name' => 'Apple', 'slug' => 'apple', 'status' => 'active'],
            ['name' => 'Samsung', 'slug' => 'samsung', 'status' => 'active'],
            ['name' => 'Sony', 'slug' => 'sony', 'status' => 'active'],
            ['name' => 'Local Brand', 'slug' => 'local-brand', 'status' => 'active'],
        ];

        foreach ($brands as $brandData) {
            Brand::firstOrCreate(['slug' => $brandData['slug']], $brandData);
        }

        // ===== Categories =====
        $categories = [
            ['name' => 'Electronics', 'slug' => 'electronics', 'description' => 'Electronic devices and accessories', 'status' => 'active', 'sort_order' => 1],
            ['name' => 'Clothing', 'slug' => 'clothing', 'description' => 'Apparel and fashion items', 'status' => 'active', 'sort_order' => 2],
            ['name' => 'Sports', 'slug' => 'sports', 'description' => 'Sports equipment and gear', 'status' => 'active', 'sort_order' => 3],
            ['name' => 'Home & Kitchen', 'slug' => 'home-kitchen', 'description' => 'Home appliances and kitchenware', 'status' => 'active', 'sort_order' => 4],
            ['name' => 'Books', 'slug' => 'books', 'description' => 'Books and educational materials', 'status' => 'active', 'sort_order' => 5],
            // Sub-categories
            ['name' => 'Smartphones', 'slug' => 'smartphones', 'description' => 'Mobile phones and accessories', 'status' => 'active', 'sort_order' => 1, 'parent_id' => null],
            ['name' => 'Laptops', 'slug' => 'laptops', 'description' => 'Laptop computers', 'status' => 'active', 'sort_order' => 2, 'parent_id' => null],
            ['name' => 'Men', 'slug' => 'men', 'description' => 'Men\'s clothing', 'status' => 'active', 'sort_order' => 1, 'parent_id' => null],
            ['name' => 'Women', 'slug' => 'women', 'description' => 'Women\'s clothing', 'status' => 'active', 'sort_order' => 2, 'parent_id' => null],
        ];

        $categoryIds = [];
        foreach ($categories as $catData) {
            $category = Category::firstOrCreate(['slug' => $catData['slug']], $catData);
            $categoryIds[$catData['slug']] = $category->id;
        }

        // Set parent IDs for sub-categories
        $electronicsId = $categoryIds['electronics'] ?? null;
        $clothingId = $categoryIds['clothing'] ?? null;
        if ($electronicsId) {
            Category::where('slug', 'smartphones')->update(['parent_id' => $electronicsId]);
            Category::where('slug', 'laptops')->update(['parent_id' => $electronicsId]);
        }
        if ($clothingId) {
            Category::where('slug', 'men')->update(['parent_id' => $clothingId]);
            Category::where('slug', 'women')->update(['parent_id' => $clothingId]);
        }

        // ===== Products =====
        $nike = Brand::where('slug', 'nike')->first();
        $adidas = Brand::where('slug', 'adidas')->first();
        $apple = Brand::where('slug', 'apple')->first();
        $samsung = Brand::where('slug', 'samsung')->first();
        $sony = Brand::where('slug', 'sony')->first();
        $local = Brand::where('slug', 'local-brand')->first();

        $products = [
            // Electronics / Smartphones
            [
                'brand_id' => $apple?->id,
                'name' => 'iPhone 15 Pro Max',
                'slug' => 'iphone-15-pro-max',
                'short_description' => 'Apple iPhone 15 Pro Max 256GB',
                'description' => 'The most powerful iPhone ever. A17 Pro chip, 48MP camera system, titanium design.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['electronics'] ?? null,
                'categories' => ['electronics', 'smartphones'],
            ],
            [
                'brand_id' => $samsung?->id,
                'name' => 'Samsung Galaxy S24 Ultra',
                'slug' => 'samsung-galaxy-s24-ultra',
                'short_description' => 'Samsung Galaxy S24 Ultra 512GB',
                'description' => 'Galaxy AI is here. Built with titanium, Galaxy S24 Ultra features a flat display.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['electronics'] ?? null,
                'categories' => ['electronics', 'smartphones'],
            ],
            // Electronics / Laptops
            [
                'brand_id' => $apple?->id,
                'name' => 'MacBook Pro M3',
                'slug' => 'macbook-pro-m3',
                'short_description' => 'Apple MacBook Pro with M3 chip',
                'description' => 'Supercharged by M3 chip. Stunning Liquid Retina XDR display.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['electronics'] ?? null,
                'categories' => ['electronics', 'laptops'],
            ],
            // Sports / Clothing
            [
                'brand_id' => $nike?->id,
                'name' => 'Nike Air Max 270',
                'slug' => 'nike-air-max-270',
                'short_description' => 'Nike Air Max 270 Running Shoes',
                'description' => 'The Nike Air Max 270 delivers visible cushioning under the heel.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['sports'] ?? null,
                'categories' => ['sports', 'clothing', 'men'],
            ],
            [
                'brand_id' => $nike?->id,
                'name' => 'Nike Dri-FIT Sportswear',
                'slug' => 'nike-dri-fit-sportswear',
                'short_description' => 'Performance Dri-FIT training top',
                'description' => 'Stay dry and comfortable during workouts with Nike Dri-FIT technology.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['sports'] ?? null,
                'categories' => ['sports', 'clothing'],
            ],
            [
                'brand_id' => $nike?->id,
                'name' => 'Nike Football Pro',
                'slug' => 'nike-football-pro',
                'short_description' => 'Professional football/soccer ball',
                'description' => 'Official match ball with superior grip and durability for all weather conditions.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['sports'] ?? null,
                'categories' => ['sports'],
            ],
            // Clothing
            [
                'brand_id' => $local?->id,
                'name' => 'Handmade Cotton Kurta',
                'slug' => 'handmade-cotton-kurta',
                'short_description' => 'Traditional handwoven cotton kurta',
                'description' => 'Premium quality handwoven cotton kurta, perfect for casual and festive wear.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['clothing'] ?? null,
                'categories' => ['clothing', 'men'],
            ],
            [
                'brand_id' => $local?->id,
                'name' => 'Banglar Muslin Saree',
                'slug' => 'banglar-muslin-saree',
                'short_description' => 'Traditional Bengali Muslin Saree',
                'description' => 'Exquisite handwoven muslin saree from Bangladesh.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['clothing'] ?? null,
                'categories' => ['clothing', 'women'],
            ],
            // Home & Kitchen
            [
                'brand_id' => $samsung?->id,
                'name' => 'Samsung 65" 4K Smart TV',
                'slug' => 'samsung-65-4k-smart-tv',
                'short_description' => '65-inch 4K UHD Smart TV',
                'description' => 'Experience stunning 4K resolution with vibrant colors and smart features.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['home-kitchen'] ?? null,
                'categories' => ['electronics', 'home-kitchen'],
            ],
            [
                'brand_id' => $sony?->id,
                'name' => 'Sony Home Theater System',
                'slug' => 'sony-home-theater-system',
                'short_description' => '5.1ch Home Theater System',
                'description' => 'Immersive 5.1 channel surround sound system for your home entertainment.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['home-kitchen'] ?? null,
                'categories' => ['electronics', 'home-kitchen'],
            ],
            [
                'brand_id' => $samsung?->id,
                'name' => 'Samsung Bespoke Refrigerator',
                'slug' => 'samsung-bespoke-refrigerator',
                'short_description' => '4-Door Flex Refrigerator with AI',
                'description' => 'Customizable 4-door refrigerator with AI-powered cooling and smart features.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['home-kitchen'] ?? null,
                'categories' => ['home-kitchen'],
            ],
            // Books
            [
                'brand_id' => $local?->id,
                'name' => 'The Great Gatsby',
                'slug' => 'the-great-gatsby',
                'short_description' => 'Classic novel by F. Scott Fitzgerald',
                'description' => 'A timeless story of wealth, love, and the American Dream in the Jazz Age.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['books'] ?? null,
                'categories' => ['books'],
            ],
            [
                'brand_id' => $local?->id,
                'name' => 'To Kill a Mockingbird',
                'slug' => 'to-kill-a-mockingbird',
                'short_description' => 'Pulitzer Prize-winning novel',
                'description' => 'Harper Lee\'s masterpiece about racial injustice and moral growth in the American South.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['books'] ?? null,
                'categories' => ['books'],
            ],
            [
                'brand_id' => $local?->id,
                'name' => '1984',
                'slug' => '1984',
                'short_description' => 'Dystopian novel by George Orwell',
                'description' => 'A chilling vision of a totalitarian future where Big Brother watches everything.',
                'product_type' => 'physical',
                'status' => 'active',
                'visibility' => 'public',
                'category_id' => $categoryIds['books'] ?? null,
                'categories' => ['books'],
            ],
        ];

        foreach ($products as $productData) {
            $categoriesSlugs = $productData['categories'] ?? [];
            unset($productData['categories']);

            $product = Product::firstOrCreate(
                ['slug' => $productData['slug']],
                array_merge($productData, ['published_at' => now()])
            );

            // Attach categories
            if (!empty($categoriesSlugs)) {
                $catIds = Category::whereIn('slug', $categoriesSlugs)->pluck('id')->toArray();
                $product->categories()->syncWithoutDetaching($catIds);
            }

            // Create variants for each product
            $variantNames = ['Default'];
            if (in_array($productData['slug'], ['iphone-15-pro-max', 'samsung-galaxy-s24-ultra'])) {
                $variantNames = ['256GB', '512GB', '1TB'];
            } elseif (in_array($productData['slug'], ['macbook-pro-m3'])) {
                $variantNames = ['14" M3', '16" M3 Pro', '16" M3 Max'];
            } elseif (in_array($productData['slug'], ['nike-air-max-270'])) {
                $variantNames = ['US 8', 'US 9', 'US 10', 'US 11'];
            } elseif (in_array($productData['slug'], ['handmade-cotton-kurta'])) {
                $variantNames = ['S', 'M', 'L', 'XL'];
            } elseif (in_array($productData['slug'], ['banglar-muslin-saree'])) {
                $variantNames = ['6 Yard', '5.5 Yard'];
            } elseif (in_array($productData['slug'], ['samsung-65-4k-smart-tv'])) {
                $variantNames = ['55"', '65"', '75"'];
            }

            foreach ($variantNames as $i => $variantName) {
                $basePrice = match ($productData['slug']) {
                    'iphone-15-pro-max' => 149999,
                    'samsung-galaxy-s24-ultra' => 139999,
                    'macbook-pro-m3' => 199999,
                    'nike-air-max-270' => 15999,
                    'handmade-cotton-kurta' => 2499,
                    'banglar-muslin-saree' => 8999,
                    'samsung-65-4k-smart-tv' => 89999,
                    'sony-home-theater-system' => 54999,
                    'samsung-bespoke-refrigerator' => 129999,
                    'the-great-gatsby' => 799,
                    'to-kill-a-mockingbird' => 699,
                    '1984' => 749,
                    default => 10000,
                };

                $priceMultiplier = match ($variantName) {
                    '512GB' => 1.15,
                    '1TB' => 1.3,
                    '16" M3 Pro' => 1.35,
                    '16" M3 Max' => 1.6,
                    '55"' => 0.8,
                    '75"' => 1.25,
                    default => 1,
                };

                ProductVariant::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'sku' => strtoupper(str_replace([' ', '"', "'"], '-', $productData['slug'])) . '-' . ($i + 1),
                    ],
                    [
                        'name' => $variantName,
                        'barcode' => (string) Str::uuid(),
                        'sale_price' => (int) round($basePrice * $priceMultiplier),
                        'cost_price' => (int) round($basePrice * $priceMultiplier * 0.7),
                        'status' => 'active',
                        'track_inventory' => true,
                    ]
                );
            }
        }

        // ===== Product Images =====
        $products = Product::all();
        foreach ($products as $product) {
            ProductImage::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'image_url' => "https://via.placeholder.com/600x600?text=" . urlencode($product->name),
                ],
                [
                    'alt_text' => $product->name,
                    'sort_order' => 0,
                ]
            );
        }

        // ===== Additional sample: Classic Tee with color × size variants =====
        // Add a friendly T-shirt product with multiple colors and sizes for frontend testing.
        $this->command->info('Adding sample Classic Tee product with color and size variants...');

        $local = Brand::where('slug', 'local-brand')->first();
        $clothingId = $categoryIds['clothing'] ?? null;

        if ($local && $clothingId) {
            $tee = Product::firstOrCreate(
                ['slug' => 'classic-tee'],
                [
                    'brand_id' => $local->id,
                    'name' => 'Classic Cotton Tee',
                    'short_description' => 'Comfortable cotton T‑shirt',
                    'description' => 'A versatile classic tee available in multiple colors and sizes.',
                    'product_type' => 'physical',
                    'status' => 'active',
                    'visibility' => 'public',
                    'published_at' => now(),
                    'category_id' => $clothingId,
                ]
            );

            $colors = [
                ['name' => 'White', 'hex' => 'ffffff'],
                ['name' => 'Black', 'hex' => '111827'],
                ['name' => 'Red',   'hex' => 'ef4444'],
            ];

            $sizes = ['S', 'M', 'L', 'XL'];

            foreach ($colors as $cIdx => $color) {
                foreach ($sizes as $sIdx => $size) {
                    $sku = 'CTEE-' . strtoupper(substr($color['name'],0,1)) . '-' . $size;
                    ProductVariant::firstOrCreate(
                        ['product_id' => $tee->id, 'sku' => $sku],
                        [
                            'name' => $color['name'] . ' / ' . $size,
                            'barcode' => (string) Str::uuid(),
                            'sale_price' => 1299 + ($sIdx * 100),
                            'cost_price' => 700,
                            'status' => 'active',
                            'track_inventory' => false,
                            'attributes' => ['color' => $color['name'], 'color_hex' => $color['hex'], 'size' => $size],
                        ]
                    );
                }

                ProductImage::firstOrCreate(
                    [
                        'product_id' => $tee->id,
                        'image_url' => "https://via.placeholder.com/800x800/{$color['hex']}/ffffff?text=" . urlencode($tee->name . ' - ' . $color['name']),
                    ],
                    [
                        'alt_text' => $tee->name . ' ' . $color['name'],
                        'sort_order' => $cIdx,
                    ]
                );
            }

            $tee->categories()->syncWithoutDetaching([$clothingId]);
            $this->command->info('Classic Tee seeded.');
        }

        $this->command->info('Catalog module seeded successfully!');
    }
}
*/
