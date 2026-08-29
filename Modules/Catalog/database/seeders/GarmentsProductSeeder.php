<?php

namespace Modules\Catalog\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Catalog\Models\Brand;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductImage;
use Modules\Catalog\Models\ProductVariant;
use Modules\Frontend\Models\SubnavbarItem;

class GarmentsProductSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding garments data...');

        // ===== Brands =====
        $brands = [
            ['name' => 'EcoThreads', 'slug' => 'eco-threads', 'status' => 'active'],
            ['name' => 'UrbanFit', 'slug' => 'urban-fit', 'status' => 'active'],
            ['name' => 'Bengal Cotton', 'slug' => 'bengal-cotton', 'status' => 'active'],
            ['name' => 'Deshi Threads', 'slug' => 'deshi-threads', 'status' => 'active'],
            ['name' => 'Kazi Casuals', 'slug' => 'kazi-casuals', 'status' => 'active'],
            ['name' => 'Nokshi Fashion', 'slug' => 'nokshi-fashion', 'status' => 'active'],
            ['name' => 'Pride Garments', 'slug' => 'pride-garments', 'status' => 'active'],
            ['name' => 'Dhaka Drape', 'slug' => 'dhaka-drape', 'status' => 'active'],
            ['name' => 'BD Style House', 'slug' => 'bd-style-house', 'status' => 'active'],
            ['name' => 'Rongdhonu Wear', 'slug' => 'rongdhonu-wear', 'status' => 'active'],
        ];

        foreach ($brands as $data) {
            Brand::firstOrCreate(['slug' => $data['slug']], $data);
        }

        // ===== 30 Categories =====
        $categories = [
            // Men categories
            ['name' => 'Men\'s T-Shirts', 'slug' => 'men-tshirts', 'description' => 'Casual and formal t-shirts for men', 'status' => 'active', 'sort_order' => 1],
            ['name' => 'Men\'s Shirts', 'slug' => 'men-shirts', 'description' => 'Formal and casual shirts for men', 'status' => 'active', 'sort_order' => 2],
            ['name' => 'Men\'s Panjabi', 'slug' => 'men-panjabi', 'description' => 'Traditional panjabi for men', 'status' => 'active', 'sort_order' => 3],
            ['name' => 'Men\'s Pants', 'slug' => 'men-pants', 'description' => 'Jeans, trousers, and casual pants', 'status' => 'active', 'sort_order' => 4],
            ['name' => 'Men\'s Suits', 'slug' => 'men-suits', 'description' => 'Suits and blazers for men', 'status' => 'active', 'sort_order' => 5],
            ['name' => 'Men\'s Winter Wear', 'slug' => 'men-winter', 'description' => 'Jackets, sweaters, hoodies for men', 'status' => 'active', 'sort_order' => 6],
            ['name' => 'Men\'s Undergarments', 'slug' => 'men-underwear', 'description' => 'Underwear, socks, vests', 'status' => 'active', 'sort_order' => 7],
            ['name' => 'Men\'s Footwear', 'slug' => 'men-footwear', 'description' => 'Shoes, sandals, boots for men', 'status' => 'active', 'sort_order' => 8],
            ['name' => 'Men\'s Traditional', 'slug' => 'men-traditional', 'description' => 'Traditional attire for men', 'status' => 'active', 'sort_order' => 9],

            // Women categories
            ['name' => 'Women\'s Sarees', 'slug' => 'women-sarees', 'description' => 'Silk, cotton, and designer sarees', 'status' => 'active', 'sort_order' => 10],
            ['name' => 'Women\'s Salwar Kameez', 'slug' => 'women-salwar', 'description' => 'Salwar kameez and Anarkali suits', 'status' => 'active', 'sort_order' => 11],
            ['name' => 'Women\'s Kurtis', 'slug' => 'women-kurtis', 'description' => 'Kurtis and tunics for women', 'status' => 'active', 'sort_order' => 12],
            ['name' => 'Women\'s Dresses', 'slug' => 'women-dresses', 'description' => 'Party and casual dresses', 'status' => 'active', 'sort_order' => 13],
            ['name' => 'Women\'s Tops', 'slug' => 'women-tops', 'description' => 'Blouses, tops, and shirts', 'status' => 'active', 'sort_order' => 14],
            ['name' => 'Women\'s Jeans & Pants', 'slug' => 'women-jeans', 'description' => 'Jeans, palazzos, and trousers', 'status' => 'active', 'sort_order' => 15],
            ['name' => 'Women\'s Hijabs', 'slug' => 'women-hijabs', 'description' => 'Hijabs, scarves, and dupattas', 'status' => 'active', 'sort_order' => 16],
            ['name' => 'Women\'s Abayas', 'slug' => 'women-abayas', 'description' => 'Abayas and burqas', 'status' => 'active', 'sort_order' => 17],
            ['name' => 'Women\'s Footwear', 'slug' => 'women-footwear', 'description' => 'Shoes, sandals, heels for women', 'status' => 'active', 'sort_order' => 18],
            ['name' => 'Women\'s Winter', 'slug' => 'women-winter', 'description' => 'Cardigans, shawls, winter wear', 'status' => 'active', 'sort_order' => 19],

            // Accessories & Others
            ['name' => 'Bags', 'slug' => 'bags', 'description' => 'Handbags, backpacks, wallets', 'status' => 'active', 'sort_order' => 20],
            ['name' => 'Watches', 'slug' => 'watches', 'description' => 'Analog and digital watches', 'status' => 'active', 'sort_order' => 21],
            ['name' => 'Jewelry', 'slug' => 'jewelry', 'description' => 'Necklaces, earrings, rings, bangles', 'status' => 'active', 'sort_order' => 22],
            ['name' => 'Belts', 'slug' => 'belts', 'description' => 'Leather and fabric belts', 'status' => 'active', 'sort_order' => 23],
            ['name' => 'Sunglasses', 'slug' => 'sunglasses', 'description' => 'Sunglasses and eyewear', 'status' => 'active', 'sort_order' => 24],
            ['name' => 'Caps & Hats', 'slug' => 'caps-hats', 'description' => 'Caps, hats, and headwear', 'status' => 'active', 'sort_order' => 25],
            ['name' => 'Perfumes', 'slug' => 'perfumes', 'description' => 'Perfumes, deodorants, attars', 'status' => 'active', 'sort_order' => 26],
            ['name' => 'Kid\'s Wear', 'slug' => 'kids-wear', 'description' => 'Clothing for children', 'status' => 'active', 'sort_order' => 27],
            ['name' => 'Couple Sets', 'slug' => 'couple-sets', 'description' => 'Matching couple outfits', 'status' => 'active', 'sort_order' => 28],
            ['name' => 'Eid Collection', 'slug' => 'eid-collection', 'description' => 'Special Eid outfits', 'status' => 'active', 'sort_order' => 29],
            ['name' => 'Sale Items', 'slug' => 'sale-items', 'description' => 'Discounted items', 'status' => 'active', 'sort_order' => 30],
        ];

        $categoryIds = [];
        foreach ($categories as $catData) {
            $category = Category::firstOrCreate(['slug' => $catData['slug']], $catData);
            $categoryIds[$catData['slug']] = $category->id;
        }

        // Get subnavbar IDs for linking products
        $subnavbarMap = [];
        $subnavbars = SubnavbarItem::where('status', 'active')->get();
        foreach ($subnavbars as $sn) {
            $subnavbarMap[$sn->slug] = $sn->id;
        }

        $brandMap = [];
        $allBrands = Brand::all();
        foreach ($allBrands as $b) {
            $brandMap[$b->slug] = $b->id;
        }

        // ===== 100+ Products with images =====
        // Using picsum.photos for realistic placeholder images
        $imgBase = 'https://picsum.photos/seed';
        $imgSizes = '/600x800';

        $products = [
            // ===== MEN'S T-SHIRTS (8 products) =====
            [
                'name' => 'Classic White Cotton T-Shirt',
                'slug' => 'classic-white-cotton-tshirt',
                'brand' => 'bengal-cotton',
                'cat' => 'men-tshirts',
                'subnavbar' => 'boys-tshirts',
                'desc' => 'Premium 100% cotton white t-shirt. Soft, breathable, and perfect for everyday wear.',
                'short' => 'Pure cotton white tee for men',
                'price' => 899,
                'variants' => ['S', 'M', 'L', 'XL', 'XXL'],
                'img' => $imgBase.'/white-tshirt'.$imgSizes,
            ],
            [
                'name' => 'Black Edition Premium Tee',
                'slug' => 'black-edition-premium-tee',
                'brand' => 'urban-fit',
                'cat' => 'men-tshirts',
                'subnavbar' => 'boys-tshirts',
                'desc' => 'Premium black t-shirt with a sleek finish. Comfortable fit for all occasions.',
                'short' => 'Sleek black t-shirt premium fit',
                'price' => 999,
                'variants' => ['M', 'L', 'XL', 'XXL'],
                'img' => $imgBase.'/black-tshirt'.$imgSizes,
            ],
            [
                'name' => 'Striped Casual Blue Tee',
                'slug' => 'striped-casual-blue-tee',
                'brand' => 'eco-threads',
                'cat' => 'men-tshirts',
                'subnavbar' => 'boys-tshirts',
                'desc' => 'Navy blue striped t-shirt made from organic cotton. Stylish and eco-friendly.',
                'short' => 'Organic cotton striped tee',
                'price' => 1099,
                'variants' => ['S', 'M', 'L', 'XL'],
                'img' => $imgBase.'/striped-tshirt'.$imgSizes,
            ],
            [
                'name' => 'Graphic Printed T-Shirt',
                'slug' => 'graphic-printed-tshirt',
                'brand' => 'kazi-casuals',
                'cat' => 'men-tshirts',
                'subnavbar' => 'boys-tshirts',
                'desc' => 'Cool graphic printed t-shirt with modern design. Soft fabric for all-day comfort.',
                'short' => 'Trendy graphic print tee',
                'price' => 1199,
                'variants' => ['S', 'M', 'L', 'XL', 'XXL'],
                'img' => $imgBase.'/graphic-tshirt'.$imgSizes,
            ],
            [
                'name' => 'Red Sports T-Shirt',
                'slug' => 'red-sports-tshirt',
                'brand' => 'pride-garments',
                'cat' => 'men-tshirts',
                'subnavbar' => 'boys-tshirts',
                'desc' => 'Moisture-wicking red sports t-shirt. Perfect for gym and outdoor activities.',
                'short' => 'Performance sports tee red',
                'price' => 799,
                'variants' => ['M', 'L', 'XL'],
                'img' => $imgBase.'/sports-tshirt'.$imgSizes,
            ],
            [
                'name' => 'Olive Green Oversized Tee',
                'slug' => 'olive-green-oversized-tee',
                'brand' => 'deshi-threads',
                'cat' => 'men-tshirts',
                'subnavbar' => 'boys-tshirts',
                'desc' => 'Trendy oversized fit t-shirt in olive green. Relaxed streetwear style.',
                'short' => 'Oversized streetwear tee',
                'price' => 1299,
                'variants' => ['M', 'L', 'XL'],
                'img' => $imgBase.'/olive-tshirt'.$imgSizes,
            ],
            [
                'name' => 'Pack of 3 Basic Tees',
                'slug' => 'pack-3-basic-tees',
                'brand' => 'bengal-cotton',
                'cat' => 'men-tshirts',
                'subnavbar' => 'boys-tshirts',
                'desc' => 'Value pack with 3 basic cotton t-shirts (White, Black, Grey). Wardrobe essentials.',
                'short' => '3-pack cotton basic tees',
                'price' => 1999,
                'variants' => ['M', 'L', 'XL'],
                'img' => $imgBase.'/pack-tshirts'.$imgSizes,
            ],
            [
                'name' => 'Navy Blue Polo T-Shirt',
                'slug' => 'navy-blue-polo-tshirt',
                'brand' => 'urban-fit',
                'cat' => 'men-tshirts',
                'subnavbar' => 'boys-tshirts',
                'desc' => 'Classic navy blue polo t-shirt with collar. Smart casual essential.',
                'short' => 'Classic polo t-shirt navy',
                'price' => 1499,
                'variants' => ['S', 'M', 'L', 'XL', 'XXL'],
                'img' => $imgBase.'/polo-tshirt'.$imgSizes,
            ],

            // ===== MEN'S SHIRTS (7 products) =====
            [
                'name' => 'White Formal Shirt',
                'slug' => 'white-formal-shirt',
                'brand' => 'pride-garments',
                'cat' => 'men-shirts',
                'subnavbar' => 'boys-shirts',
                'desc' => 'Crisp white formal shirt for office and formal events. Premium cotton fabric.',
                'short' => 'Classic white formal shirt',
                'price' => 1599,
                'variants' => ['S', 'M', 'L', 'XL', 'XXL'],
                'img' => $imgBase.'/white-shirt'.$imgSizes,
            ],
            [
                'name' => 'Blue Checkered Casual Shirt',
                'slug' => 'blue-checkered-casual-shirt',
                'brand' => 'kazi-casuals',
                'cat' => 'men-shirts',
                'subnavbar' => 'boys-shirts',
                'desc' => 'Stylish blue checkered shirt perfect for casual outings and college.',
                'short' => 'Blue checkered casual shirt',
                'price' => 1399,
                'variants' => ['S', 'M', 'L', 'XL'],
                'img' => $imgBase.'/check-shirt'.$imgSizes,
            ],
            [
                'name' => 'Black Slim Fit Shirt',
                'slug' => 'black-slim-fit-shirt',
                'brand' => 'urban-fit',
                'cat' => 'men-shirts',
                'subnavbar' => 'boys-shirts',
                'desc' => 'Slim fit black shirt. Modern cut with a sophisticated look.',
                'short' => 'Slim fit black shirt',
                'price' => 1699,
                'variants' => ['M', 'L', 'XL'],
                'img' => $imgBase.'/black-shirt'.$imgSizes,
            ],
            [
                'name' => 'Linen Summer Shirt',
                'slug' => 'linen-summer-shirt',
                'brand' => 'eco-threads',
                'cat' => 'men-shirts',
                'subnavbar' => 'boys-shirts',
                'desc' => 'Breathable linen shirt perfect for summer. Lightweight and comfortable.',
                'short' => 'Breathable linen summer shirt',
                'price' => 1899,
                'variants' => ['S', 'M', 'L', 'XL'],
                'img' => $imgBase.'/linen-shirt'.$imgSizes,
            ],
            [
                'name' => 'Pink Casual Shirt',
                'slug' => 'pink-casual-shirt',
                'brand' => 'deshi-threads',
                'cat' => 'men-shirts',
                'subnavbar' => 'boys-shirts',
                'desc' => 'Light pink casual shirt. Trendy color for modern men.',
                'short' => 'Light pink shirt for men',
                'price' => 1299,
                'variants' => ['M', 'L', 'XL'],
                'img' => $imgBase.'/pink-shirt'.$imgSizes,
            ],
            [
                'name' => 'Denim Western Shirt',
                'slug' => 'denim-western-shirt',
                'brand' => 'bd-style-house',
                'cat' => 'men-shirts',
                'subnavbar' => 'boys-shirts',
                'desc' => 'Classic denim shirt with western styling. Versatile for any wardrobe.',
                'short' => 'Classic denim shirt',
                'price' => 1799,
                'variants' => ['M', 'L', 'XL', 'XXL'],
                'img' => $imgBase.'/denim-shirt'.$imgSizes,
            ],
            [
                'name' => 'Printed Hawaiian Shirt',
                'slug' => 'printed-hawaiian-shirt',
                'brand' => 'rongdhonu-wear',
                'cat' => 'men-shirts',
                'subnavbar' => 'boys-shirts',
                'desc' => 'Vibrant printed Hawaiian shirt for vacation and beach vibes.',
                'short' => 'Colorful Hawaiian print shirt',
                'price' => 999,
                'variants' => ['S', 'M', 'L', 'XL'],
                'img' => $imgBase.'/hawaiian-shirt'.$imgSizes,
            ],

            // ===== PANJABI & KURTA (6 products) =====
            [
                'name' => 'White Cotton Panjabi',
                'slug' => 'white-cotton-panjabi',
                'brand' => 'bengal-cotton',
                'cat' => 'men-panjabi',
                'subnavbar' => 'boys-panjabi',
                'desc' => 'Pure white cotton panjabi. Essential for Jumuah and casual wear.',
                'short' => 'Pure white cotton panjabi',
                'price' => 1499,
                'variants' => ['M', 'L', 'XL', 'XXL', '3XL'],
                'img' => $imgBase.'/white-panjabi'.$imgSizes,
            ],
            [
                'name' => 'Royal Blue Panjabi with Button',
                'slug' => 'royal-blue-panjabi-button',
                'brand' => 'deshi-threads',
                'cat' => 'men-panjabi',
                'subnavbar' => 'boys-panjabi',
                'desc' => 'Royal blue panjabi with stylish button details. Perfect for Eid and events.',
                'short' => 'Royal blue designer panjabi',
                'price' => 2499,
                'variants' => ['M', 'L', 'XL', 'XXL'],
                'img' => $imgBase.'/blue-panjabi'.$imgSizes,
            ],
            [
                'name' => 'Maroon Silk Panjabi',
                'slug' => 'maroon-silk-panjabi',
                'brand' => 'nokshi-fashion',
                'cat' => 'men-panjabi',
                'subnavbar' => 'boys-panjabi',
                'desc' => 'Rich maroon silk panjabi with golden embroidery. Premium wedding wear.',
                'short' => 'Maroon silk embroidered panjabi',
                'price' => 3999,
                'variants' => ['L', 'XL', 'XXL'],
                'img' => $imgBase.'/maroon-panjabi'.$imgSizes,
            ],
            [
                'name' => 'Black Pathor Panjabi',
                'slug' => 'black-pathor-panjabi',
                'brand' => 'nokshi-fashion',
                'cat' => 'men-panjabi',
                'subnavbar' => 'boys-panjabi',
                'desc' => 'Black panjabi with pathor (stone) work on collar. Exclusive party wear.',
                'short' => 'Black stone work panjabi',
                'price' => 4599,
                'variants' => ['L', 'XL', 'XXL'],
                'img' => $imgBase.'/black-panjabi'.$imgSizes,
            ],
            [
                'name' => 'Off-White Kurta with Pocket',
                'slug' => 'offwhite-kurta-pocket',
                'brand' => 'kazi-casuals',
                'cat' => 'men-panjabi',
                'subnavbar' => 'boys-panjabi',
                'desc' => 'Comfortable off-white cotton kurta with chest pocket. Daily wear essential.',
                'short' => 'Off-white cotton kurta',
                'price' => 1199,
                'variants' => ['M', 'L', 'XL', 'XXL'],
                'img' => $imgBase.'/kurta-panjabi'.$imgSizes,
            ],
            [
                'name' => 'Green Embroidered Panjabi Set',
                'slug' => 'green-embroidered-panjabi-set',
                'brand' => 'nokshi-fashion',
                'cat' => 'men-panjabi',
                'subnavbar' => 'boys-panjabi',
                'desc' => 'Green panjabi set with intricate embroidery. Includes panjabi and pajama.',
                'short' => 'Green embroidered panjabi set',
                'price' => 3499,
                'variants' => ['L', 'XL', 'XXL'],
                'img' => $imgBase.'/green-panjabi'.$imgSizes,
            ],

            // ===== MEN'S PANTS (6 products) =====
            [
                'name' => 'Slim Fit Blue Jeans',
                'slug' => 'slim-fit-blue-jeans',
                'brand' => 'urban-fit',
                'cat' => 'men-pants',
                'subnavbar' => 'boys-pants',
                'desc' => 'Classic slim fit blue jeans. Durable denim with comfortable stretch.',
                'short' => 'Slim fit blue denim jeans',
                'price' => 1999,
                'variants' => ['28', '30', '32', '34', '36'],
                'img' => $imgBase.'/blue-jeans'.$imgSizes,
            ],
            [
                'name' => 'Black Chino Trousers',
                'slug' => 'black-chino-trousers',
                'brand' => 'eco-threads',
                'cat' => 'men-pants',
                'subnavbar' => 'boys-pants',
                'desc' => 'Smart black chino trousers. Perfect for office and semi-formal occasions.',
                'short' => 'Black chino trousers',
                'price' => 1799,
                'variants' => ['30', '32', '34', '36'],
                'img' => $imgBase.'/chino-pants'.$imgSizes,
            ],
            [
                'name' => 'Grey Jogger Pants',
                'slug' => 'grey-jogger-pants',
                'brand' => 'pride-garments',
                'cat' => 'men-pants',
                'subnavbar' => 'boys-pants',
                'desc' => 'Comfortable grey jogger pants with elastic waist and cuffs. Loungewear essential.',
                'short' => 'Casual grey joggers',
                'price' => 1299,
                'variants' => ['M', 'L', 'XL'],
                'img' => $imgBase.'/jogger-pants'.$imgSizes,
            ],
            [
                'name' => 'Brown Cargo Pants',
                'slug' => 'brown-cargo-pants',
                'brand' => 'kazi-casuals',
                'cat' => 'men-pants',
                'subnavbar' => 'boys-pants',
                'desc' => 'Utility brown cargo pants with multiple pockets. Rugged and stylish.',
                'short' => 'Multi-pocket cargo pants',
                'price' => 1699,
                'variants' => ['30', '32', '34', '36'],
                'img' => $imgBase.'/cargo-pants'.$imgSizes,
            ],
            [
                'name' => 'White Linen Trousers',
                'slug' => 'white-linen-trousers',
                'brand' => 'eco-threads',
                'cat' => 'men-pants',
                'subnavbar' => 'boys-pants',
                'desc' => 'Lightweight white linen trousers. Perfect for summer and formal events.',
                'short' => 'Summer linen trousers',
                'price' => 1899,
                'variants' => ['30', '32', '34'],
                'img' => $imgBase.'/linen-trousers'.$imgSizes,
            ],
            [
                'name' => 'Black Skinny Jeans',
                'slug' => 'black-skinny-jeans',
                'brand' => 'urban-fit',
                'cat' => 'men-pants',
                'subnavbar' => 'boys-pants',
                'desc' => 'Sleek black skinny jeans. Modern fit for a stylish look.',
                'short' => 'Black skinny denim jeans',
                'price' => 1899,
                'variants' => ['28', '30', '32', '34'],
                'img' => $imgBase.'/black-jeans'.$imgSizes,
            ],

            // ===== MEN'S SUITS (4 products) =====
            [
                'name' => 'Navy Blue Slim Fit Suit',
                'slug' => 'navy-blue-slim-fit-suit',
                'brand' => 'pride-garments',
                'cat' => 'men-suits',
                'subnavbar' => 'boys-suits',
                'desc' => 'Premium navy blue slim fit suit. Two-piece set with blazer and trousers.',
                'short' => 'Navy blue 2-piece suit',
                'price' => 8999,
                'variants' => ['38', '40', '42', '44'],
                'img' => $imgBase.'/navy-suit'.$imgSizes,
            ],
            [
                'name' => 'Charcoal Grey Blazer',
                'slug' => 'charcoal-grey-blazer',
                'brand' => 'bd-style-house',
                'cat' => 'men-suits',
                'subnavbar' => 'boys-suits',
                'desc' => 'Versatile charcoal grey blazer. Pairs well with jeans or trousers.',
                'short' => 'Charcoal grey blazer',
                'price' => 5499,
                'variants' => ['38', '40', '42', '44'],
                'img' => $imgBase.'/grey-blazer'.$imgSizes,
            ],
            [
                'name' => 'Black Formal Suit',
                'slug' => 'black-formal-suit',
                'brand' => 'pride-garments',
                'cat' => 'men-suits',
                'subnavbar' => 'boys-suits',
                'desc' => 'Classic black formal suit for weddings and official events.',
                'short' => 'Classic black formal suit',
                'price' => 9999,
                'variants' => ['38', '40', '42', '44'],
                'img' => $imgBase.'/black-suit'.$imgSizes,
            ],
            [
                'name' => 'Beige Linen Suit',
                'slug' => 'beige-linen-suit',
                'brand' => 'eco-threads',
                'cat' => 'men-suits',
                'subnavbar' => 'boys-suits',
                'desc' => 'Summer-ready beige linen suit. Lightweight and breathable.',
                'short' => 'Summer linen suit beige',
                'price' => 7999,
                'variants' => ['38', '40', '42'],
                'img' => $imgBase.'/beige-suit'.$imgSizes,
            ],

            // ===== MEN'S WINTER WEAR (5 products) =====
            [
                'name' => 'Black Bomber Jacket',
                'slug' => 'black-bomber-jacket',
                'brand' => 'urban-fit',
                'cat' => 'men-winter',
                'subnavbar' => 'boys-winter',
                'desc' => 'Stylish black bomber jacket. Ribbed cuffs and hem, zip closure.',
                'short' => 'Black bomber jacket men',
                'price' => 2999,
                'variants' => ['M', 'L', 'XL', 'XXL'],
                'img' => $imgBase.'/bomber-jacket'.$imgSizes,
            ],
            [
                'name' => 'Grey Hooded Sweatshirt',
                'slug' => 'grey-hooded-sweatshirt',
                'brand' => 'kazi-casuals',
                'cat' => 'men-winter',
                'subnavbar' => 'boys-winter',
                'desc' => 'Comfortable grey hoodie with kangaroo pocket. Casual winter essential.',
                'short' => 'Grey pullover hoodie',
                'price' => 1999,
                'variants' => ['S', 'M', 'L', 'XL', 'XXL'],
                'img' => $imgBase.'/hoodie'.$imgSizes,
            ],
            [
                'name' => 'Maroon Sweater',
                'slug' => 'maroon-sweater',
                'brand' => 'bengal-cotton',
                'cat' => 'men-winter',
                'subnavbar' => 'boys-winter',
                'desc' => 'Warm maroon sweater with ribbed pattern. Perfect for winter layering.',
                'short' => 'Maroon winter sweater',
                'price' => 1599,
                'variants' => ['M', 'L', 'XL', 'XXL'],
                'img' => $imgBase.'/sweater'.$imgSizes,
            ],
            [
                'name' => 'Brown Leather Jacket',
                'slug' => 'brown-leather-jacket',
                'brand' => 'bd-style-house',
                'cat' => 'men-winter',
                'subnavbar' => 'boys-winter',
                'desc' => 'Classic brown faux leather jacket. Timeless style with a rugged look.',
                'short' => 'Brown faux leather jacket',
                'price' => 4999,
                'variants' => ['M', 'L', 'XL', 'XXL'],
                'img' => $imgBase.'/leather-jacket'.$imgSizes,
            ],
            [
                'name' => 'Navy Blue Puffer Jacket',
                'slug' => 'navy-puffer-jacket',
                'brand' => 'rongdhonu-wear',
                'cat' => 'men-winter',
                'subnavbar' => 'boys-winter',
                'desc' => 'Warm navy blue puffer jacket with quilted design. Extreme cold protection.',
                'short' => 'Navy quilted puffer jacket',
                'price' => 3999,
                'variants' => ['M', 'L', 'XL'],
                'img' => $imgBase.'/puffer-jacket'.$imgSizes,
            ],

            // ===== MEN'S UNDERWEAR (3 products) =====
            [
                'name' => 'Pack of 5 Cotton Boxers',
                'slug' => 'pack-5-cotton-boxers',
                'brand' => 'bengal-cotton',
                'cat' => 'men-underwear',
                'subnavbar' => 'boys-underwear',
                'desc' => 'Pack of 5 premium cotton boxer briefs. Assorted colors, elastic waistband.',
                'short' => '5-pack cotton boxer briefs',
                'price' => 999,
                'variants' => ['M', 'L', 'XL', 'XXL'],
                'img' => $imgBase.'/boxers'.$imgSizes,
            ],
            [
                'name' => 'Pack of 6 Ankle Socks',
                'slug' => 'pack-6-ankle-socks',
                'brand' => 'pride-garments',
                'cat' => 'men-underwear',
                'subnavbar' => 'boys-underwear',
                'desc' => 'Comfortable ankle socks multipack. Breathable cotton blend for everyday use.',
                'short' => '6-pair ankle socks pack',
                'price' => 499,
                'variants' => ['One Size'],
                'img' => $imgBase.'/socks'.$imgSizes,
            ],
            [
                'name' => 'Cotton Vest (Banian) 3 Pack',
                'slug' => 'cotton-vest-banian-3-pack',
                'brand' => 'bengal-cotton',
                'cat' => 'men-underwear',
                'subnavbar' => 'boys-underwear',
                'desc' => 'Pure cotton vests (banian) pack of 3. Soft and comfortable undergarment.',
                'short' => '3-pack cotton banian',
                'price' => 599,
                'variants' => ['M', 'L', 'XL', 'XXL'],
                'img' => $imgBase.'/vest'.$imgSizes,
            ],

            // ===== TRADITIONAL WEAR (3 products) =====
            [
                'name' => 'White Pajama with Lungi',
                'slug' => 'white-pajama-lungi',
                'brand' => 'deshi-threads',
                'cat' => 'men-traditional',
                'subnavbar' => 'boys-traditional',
                'desc' => 'Traditional white cotton pajama and lungi set. Comfortable home wear.',
                'short' => 'Cotton pajama lungi set',
                'price' => 899,
                'variants' => ['M', 'L', 'XL', 'XXL'],
                'img' => $imgBase.'/pajama'.$imgSizes,
            ],
            [
                'name' => 'Red Gamcha Towel Set',
                'slug' => 'red-gamcha-towel-set',
                'brand' => 'deshi-threads',
                'cat' => 'men-traditional',
                'subnavbar' => 'boys-traditional',
                'desc' => 'Traditional red gamcha towels set of 3. Bengali cultural essential.',
                'short' => 'Traditional gamcha 3-pack',
                'price' => 399,
                'variants' => ['One Size'],
                'img' => $imgBase.'/gamcha'.$imgSizes,
            ],
            [
                'name' => 'White Tupi (Prayer Cap)',
                'slug' => 'white-tupi-prayer-cap',
                'brand' => 'deshi-threads',
                'cat' => 'men-traditional',
                'subnavbar' => 'boys-traditional',
                'desc' => 'White embroidered prayer cap (tupi/topi). Cotton fabric for daily use.',
                'short' => 'Embroidered prayer cap',
                'price' => 199,
                'variants' => ['One Size'],
                'img' => $imgBase.'/tupi'.$imgSizes,
            ],

            // ===== WOMEN'S SAREES (7 products) =====
            [
                'name' => 'Red Bridal Silk Saree',
                'slug' => 'red-bridal-silk-saree',
                'brand' => 'nokshi-fashion',
                'cat' => 'women-sarees',
                'subnavbar' => 'women-sarees',
                'desc' => 'Stunning red bridal silk saree with golden zari work. Perfect for weddings.',
                'short' => 'Red silk bridal saree',
                'price' => 8999,
                'variants' => ['5.5 Yards', '6 Yards'],
                'img' => $imgBase.'/red-saree'.$imgSizes,
            ],
            [
                'name' => 'Blue Tangail Cotton Saree',
                'slug' => 'blue-tangail-cotton-saree',
                'brand' => 'dhaka-drape',
                'cat' => 'women-sarees',
                'subnavbar' => 'women-sarees',
                'desc' => 'Authentic Tangail cotton saree in blue with traditional border designs.',
                'short' => 'Tangail cotton saree blue',
                'price' => 2999,
                'variants' => ['5.5 Yards', '6 Yards'],
                'img' => $imgBase.'/blue-saree'.$imgSizes,
            ],
            [
                'name' => 'Green Georgette Saree',
                'slug' => 'green-georgette-saree',
                'brand' => 'dhaka-drape',
                'cat' => 'women-sarees',
                'subnavbar' => 'women-sarees',
                'desc' => 'Elegant green georgette saree with floral embroidery. Party wear.',
                'short' => 'Green georgette saree',
                'price' => 3999,
                'variants' => ['5.5 Yards'],
                'img' => $imgBase.'/green-saree'.$imgSizes,
            ],
            [
                'name' => 'White Khadi Saree with Red Border',
                'slug' => 'white-khadi-saree-red-border',
                'brand' => 'bengal-cotton',
                'cat' => 'women-sarees',
                'subnavbar' => 'women-sarees',
                'desc' => 'Classic white khadi saree with red border. Bengali cultural icon.',
                'short' => 'White khadi red border saree',
                'price' => 2499,
                'variants' => ['5.5 Yards'],
                'img' => $imgBase.'/khadi-saree'.$imgSizes,
            ],
            [
                'name' => 'Pink Organza Saree',
                'slug' => 'pink-organza-saree',
                'brand' => 'nokshi-fashion',
                'cat' => 'women-sarees',
                'subnavbar' => 'women-sarees',
                'desc' => 'Beautiful pink organza saree with delicate embroidery. Lightweight and elegant.',
                'short' => 'Pink organza embroidered saree',
                'price' => 5499,
                'variants' => ['5.5 Yards'],
                'img' => $imgBase.'/pink-saree'.$imgSizes,
            ],
            [
                'name' => 'Purple Silk Saree',
                'slug' => 'purple-silk-saree',
                'brand' => 'nokshi-fashion',
                'cat' => 'women-sarees',
                'subnavbar' => 'women-sarees',
                'desc' => 'Rich purple silk saree with resham work. Perfect for receptions.',
                'short' => 'Purple silk reception saree',
                'price' => 6999,
                'variants' => ['5.5 Yards', '6 Yards'],
                'img' => $imgBase.'/purple-saree'.$imgSizes,
            ],
            [
                'name' => 'Yellow Cotton Saree',
                'slug' => 'yellow-cotton-saree',
                'brand' => 'bengal-cotton',
                'cat' => 'women-sarees',
                'subnavbar' => 'women-sarees',
                'desc' => 'Bright yellow cotton saree. Comfortable for daily wear and casual events.',
                'short' => 'Yellow cotton casual saree',
                'price' => 1899,
                'variants' => ['5.5 Yards'],
                'img' => $imgBase.'/yellow-saree'.$imgSizes,
            ],

            // ===== WOMEN'S SALWAR KAMEEZ (6 products) =====
            [
                'name' => 'Blue Anarkali Salwar',
                'slug' => 'blue-anarkali-salwar',
                'brand' => 'nokshi-fashion',
                'cat' => 'women-salwar',
                'subnavbar' => 'women-salwar',
                'desc' => 'Royal blue Anarkali salwar kameez with embroidered neckline. Party wear.',
                'short' => 'Blue Anarkali suit',
                'price' => 3999,
                'variants' => ['S', 'M', 'L', 'XL', 'XXL'],
                'img' => $imgBase.'/anarkali'.$imgSizes,
            ],
            [
                'name' => 'Green Printed Salwar Kameez',
                'slug' => 'green-printed-salwar',
                'brand' => 'deshi-threads',
                'cat' => 'women-salwar',
                'subnavbar' => 'women-salwar',
                'desc' => 'Green printed cotton salwar kameez. Comfortable and stylish for daily wear.',
                'short' => 'Green cotton printed salwar',
                'price' => 2499,
                'variants' => ['S', 'M', 'L', 'XL'],
                'img' => $imgBase.'/green-salwar'.$imgSizes,
            ],
            [
                'name' => 'Black Embroidered Salwar',
                'slug' => 'black-embroidered-salwar',
                'brand' => 'nokshi-fashion',
                'cat' => 'women-salwar',
                'subnavbar' => 'women-salwar',
                'desc' => 'Black salwar kameez with intricate embroidery. Elegant evening wear.',
                'short' => 'Black embroidered suit',
                'price' => 4499,
                'variants' => ['S', 'M', 'L', 'XL'],
                'img' => $imgBase.'/black-salwar'.$imgSizes,
            ],
            [
                'name' => 'Orange Cotton Salwar',
                'slug' => 'orange-cotton-salwar',
                'brand' => 'deshi-threads',
                'cat' => 'women-salwar',
                'subnavbar' => 'women-salwar',
                'desc' => 'Bright orange cotton salwar kameez. Perfect for festive occasions.',
                'short' => 'Orange cotton suit',
                'price' => 1999,
                'variants' => ['M', 'L', 'XL'],
                'img' => $imgBase.'/orange-salwar'.$imgSizes,
            ],
            [
                'name' => 'White Pakistani Suit',
                'slug' => 'white-pakistani-suit',
                'brand' => 'dhaka-drape',
                'cat' => 'women-salwar',
                'subnavbar' => 'women-salwar',
                'desc' => 'White Pakistani style salwar kameez with lace details. Elegant and modest.',
                'short' => 'White Pakistani suit',
                'price' => 3499,
                'variants' => ['S', 'M', 'L', 'XL'],
                'img' => $imgBase.'/white-salwar'.$imgSizes,
            ],
            [
                'name' => 'Mint Green Anarkali',
                'slug' => 'mint-green-anarkali',
                'brand' => 'rongdhonu-wear',
                'cat' => 'women-salwar',
                'subnavbar' => 'women-salwar',
                'desc' => 'Mint green Anarkali suit with delicate floral pattern. Summer special.',
                'short' => 'Mint green floral Anarkali',
                'price' => 2999,
                'variants' => ['S', 'M', 'L', 'XL'],
                'img' => $imgBase.'/mint-anarkali'.$imgSizes,
            ],

            // ===== KURTIS (5 products) =====
            [
                'name' => 'White Cotton Kurti with Embroidery',
                'slug' => 'white-cotton-kurti-embroidered',
                'brand' => 'kazi-casuals',
                'cat' => 'women-kurtis',
                'subnavbar' => 'women-kurtis',
                'desc' => 'White cotton kurti with neck embroidery. Versatile for daily wear.',
                'short' => 'White embroidered kurti',
                'price' => 1499,
                'variants' => ['S', 'M', 'L', 'XL'],
                'img' => $imgBase.'/white-kurti'.$imgSizes,
            ],
            [
                'name' => 'Maroon Rayon Kurti',
                'slug' => 'maroon-rayon-kurti',
                'brand' => 'deshi-threads',
                'cat' => 'women-kurtis',
                'subnavbar' => 'women-kurtis',
                'desc' => 'Maroon rayon kurti with printed design. Flowing and comfortable fit.',
                'short' => 'Maroon printed kurti',
                'price' => 1299,
                'variants' => ['S', 'M', 'L', 'XL'],
                'img' => $imgBase.'/maroon-kurti'.$imgSizes,
            ],
            [
                'name' => 'Blue Denim Kurti',
                'slug' => 'blue-denim-kurti',
                'brand' => 'bd-style-house',
                'cat' => 'women-kurtis',
                'subnavbar' => 'women-kurtis',
                'desc' => 'Trendy blue denim kurti with button placket. Modern fusion wear.',
                'short' => 'Denim style kurti',
                'price' => 1799,
                'variants' => ['M', 'L', 'XL'],
                'img' => $imgBase.'/denim-kurti'.$imgSizes,
            ],
            [
                'name' => 'Black Long Kurti with Side Slits',
                'slug' => 'black-long-kurti-side-slits',
                'brand' => 'urban-fit',
                'cat' => 'women-kurtis',
                'subnavbar' => 'women-kurtis',
                'desc' => 'Black long kurti with side slits. Pairs well with leggings or jeans.',
                'short' => 'Black long kurti',
                'price' => 1599,
                'variants' => ['M', 'L', 'XL'],
                'img' => $imgBase.'/black-kurti'.$imgSizes,
            ],
            [
                'name' => 'Pink Cotton A-Line Kurti',
                'slug' => 'pink-cotton-aline-kurti',
                'brand' => 'kazi-casuals',
                'cat' => 'women-kurtis',
                'subnavbar' => 'women-kurtis',
                'desc' => 'Pink A-line cotton kurti with colorful print. Casual and cheerful.',
                'short' => 'Pink printed A-line kurti',
                'price' => 1399,
                'variants' => ['S', 'M', 'L', 'XL'],
                'img' => $imgBase.'/pink-kurti'.$imgSizes,
            ],

            // ===== WOMEN'S DRESSES (5 products) =====
            [
                'name' => 'Red Bodycon Dress',
                'slug' => 'red-bodycon-dress',
                'brand' => 'urban-fit',
                'cat' => 'women-dresses',
                'subnavbar' => 'women-dresses',
                'desc' => 'Stunning red bodycon dress. Perfect for parties and special occasions.',
                'short' => 'Red bodycon party dress',
                'price' => 2999,
                'variants' => ['S', 'M', 'L', 'XL'],
                'img' => $imgBase.'/red-dress'.$imgSizes,
            ],
            [
                'name' => 'Black Lace Midi Dress',
                'slug' => 'black-lace-midi-dress',
                'brand' => 'rongdhonu-wear',
                'cat' => 'women-dresses',
                'subnavbar' => 'women-dresses',
                'desc' => 'Elegant black lace midi dress. Sophisticated design for evening events.',
                'short' => 'Black lace midi dress',
                'price' => 3499,
                'variants' => ['S', 'M', 'L'],
                'img' => $imgBase.'/black-dress'.$imgSizes,
            ],
            [
                'name' => 'Floral Maxi Dress',
                'slug' => 'floral-maxi-dress',
                'brand' => 'rongdhonu-wear',
                'cat' => 'women-dresses',
                'subnavbar' => 'women-dresses',
                'desc' => 'Beautiful floral print maxi dress. Lightweight and flowy for summer.',
                'short' => 'Floral print maxi dress',
                'price' => 2499,
                'variants' => ['S', 'M', 'L', 'XL'],
                'img' => $imgBase.'/floral-dress'.$imgSizes,
            ],
            [
                'name' => 'Blue Shirt Dress',
                'slug' => 'blue-shirt-dress',
                'brand' => 'kazi-casuals',
                'cat' => 'women-dresses',
                'subnavbar' => 'women-dresses',
                'desc' => 'Casual blue shirt dress with belt. Perfect for office and casual outings.',
                'short' => 'Blue shirt dress casual',
                'price' => 2199,
                'variants' => ['S', 'M', 'L', 'XL'],
                'img' => $imgBase.'/shirt-dress'.$imgSizes,
            ],
            [
                'name' => 'White Summer Dress',
                'slug' => 'white-summer-dress',
                'brand' => 'eco-threads',
                'cat' => 'women-dresses',
                'subnavbar' => 'women-dresses',
                'desc' => 'White cotton summer dress with lace trim. Breezy and feminine.',
                'short' => 'White lace summer dress',
                'price' => 1999,
                'variants' => ['S', 'M', 'L'],
                'img' => $imgBase.'/white-dress'.$imgSizes,
            ],

            // ===== WOMEN'S TOPS (5 products) =====
            [
                'name' => 'White Cotton Blouse',
                'slug' => 'white-cotton-blouse',
                'brand' => 'bengal-cotton',
                'cat' => 'women-tops',
                'subnavbar' => 'women-tops',
                'desc' => 'Classic white cotton blouse versatile for office or casual wear.',
                'short' => 'White cotton blouse',
                'price' => 1299,
                'variants' => ['S', 'M', 'L', 'XL'],
                'img' => $imgBase.'/white-blouse'.$imgSizes,
            ],
            [
                'name' => 'Printed Crop Top',
                'slug' => 'printed-crop-top',
                'brand' => 'urban-fit',
                'cat' => 'women-tops',
                'subnavbar' => 'women-tops',
                'desc' => 'Trendy printed crop top with ruffled sleeves. Summer fashion essential.',
                'short' => 'Floral print crop top',
                'price' => 999,
                'variants' => ['S', 'M', 'L'],
                'img' => $imgBase.'/crop-top'.$imgSizes,
            ],
            [
                'name' => 'Black Peplum Top',
                'slug' => 'black-peplum-top',
                'brand' => 'rongdhonu-wear',
                'cat' => 'women-tops',
                'subnavbar' => 'women-tops',
                'desc' => 'Elegant black peplum top with flared hem. Perfect for parties.',
                'short' => 'Black peplum party top',
                'price' => 1599,
                'variants' => ['S', 'M', 'L', 'XL'],
                'img' => $imgBase.'/peplum-top'.$imgSizes,
            ],
            [
                'name' => 'Striped Off-Shoulder Top',
                'slug' => 'striped-off-shoulder-top',
                'brand' => 'eco-threads',
                'cat' => 'women-tops',
                'subnavbar' => 'women-tops',
                'desc' => 'Striped off-shoulder top with elastic neckline. Boho chic style.',
                'short' => 'Striped off-shoulder top',
                'price' => 1199,
                'variants' => ['S', 'M', 'L'],
                'img' => $imgBase.'/off-shoulder'.$imgSizes,
            ],
            [
                'name' => 'Silk Camisole Top',
                'slug' => 'silk-camisole-top',
                'brand' => 'nokshi-fashion',
                'cat' => 'women-tops',
                'subnavbar' => 'women-tops',
                'desc' => 'Luxurious silk camisole top with lace trim. Layering essential.',
                'short' => 'Silk lace camisole',
                'price' => 1799,
                'variants' => ['S', 'M', 'L'],
                'img' => $imgBase.'/camisole'.$imgSizes,
            ],

            // ===== WOMEN'S JEANS & PANTS (5 products) =====
            [
                'name' => 'Blue Skinny Jeans Women',
                'slug' => 'blue-skinny-jeans-women',
                'brand' => 'urban-fit',
                'cat' => 'women-jeans',
                'subnavbar' => 'women-jeans',
                'desc' => 'Comfortable blue skinny jeans for women. High waist with stretch denim.',
                'short' => 'High waist skinny jeans',
                'price' => 1899,
                'variants' => ['26', '28', '30', '32'],
                'img' => $imgBase.'/women-jeans'.$imgSizes,
            ],
            [
                'name' => 'Black Palazzo Pants',
                'slug' => 'black-palazzo-pants',
                'brand' => 'deshi-threads',
                'cat' => 'women-jeans',
                'subnavbar' => 'women-jeans',
                'desc' => 'Flowing black palazzo pants with elastic waist. Comfortable ethnic wear.',
                'short' => 'Black palazzo pants',
                'price' => 1399,
                'variants' => ['S', 'M', 'L', 'XL'],
                'img' => $imgBase.'/palazzo'.$imgSizes,
            ],
            [
                'name' => 'White Trousers Women',
                'slug' => 'white-trousers-women',
                'brand' => 'eco-threads',
                'cat' => 'women-jeans',
                'subnavbar' => 'women-jeans',
                'desc' => 'Crisp white trousers for women. Office-approved and stylish.',
                'short' => 'White formal trousers',
                'price' => 1699,
                'variants' => ['26', '28', '30', '32'],
                'img' => $imgBase.'/women-trousers'.$imgSizes,
            ],
            [
                'name' => 'Denim Shorts Women',
                'slug' => 'denim-shorts-women',
                'brand' => 'kazi-casuals',
                'cat' => 'women-jeans',
                'subnavbar' => 'women-jeans',
                'desc' => 'Casual denim shorts for women. Perfect summer essential.',
                'short' => 'Blue denim shorts',
                'price' => 999,
                'variants' => ['S', 'M', 'L'],
                'img' => $imgBase.'/denim-shorts'.$imgSizes,
            ],
            [
                'name' => 'Printed Leggings',
                'slug' => 'printed-leggings',
                'brand' => 'rongdhonu-wear',
                'cat' => 'women-jeans',
                'subnavbar' => 'women-jeans',
                'desc' => 'Colorful printed leggings with high stretch. Comfortable daily wear.',
                'short' => 'Printed stretch leggings',
                'price' => 799,
                'variants' => ['S', 'M', 'L', 'XL'],
                'img' => $imgBase.'/leggings'.$imgSizes,
            ],

            // ===== HIJABS (4 products) =====
            [
                'name' => 'Chiffon Hijab Set - Pink',
                'slug' => 'chiffon-hijab-set-pink',
                'brand' => 'dhaka-drape',
                'cat' => 'women-hijabs',
                'subnavbar' => 'women-hijabs',
                'desc' => 'Premium chiffon hijab set in pink. Includes inner cap and pins.',
                'short' => 'Pink chiffon hijab set',
                'price' => 899,
                'variants' => ['One Size'],
                'img' => $imgBase.'/pink-hijab'.$imgSizes,
            ],
            [
                'name' => 'Cotton Hijab Multipack',
                'slug' => 'cotton-hijab-multipack',
                'brand' => 'bengal-cotton',
                'cat' => 'women-hijabs',
                'subnavbar' => 'women-hijabs',
                'desc' => 'Pack of 5 cotton hijabs in assorted colors. Lightweight and breathable.',
                'short' => '5-pack cotton hijabs',
                'price' => 1499,
                'variants' => ['One Size'],
                'img' => $imgBase.'/hijab-pack'.$imgSizes,
            ],
            [
                'name' => 'Embroidered Net Hijab',
                'slug' => 'embroidered-net-hijab',
                'brand' => 'nokshi-fashion',
                'cat' => 'women-hijabs',
                'subnavbar' => 'women-hijabs',
                'desc' => 'Elegant net hijab with embroidered edges. Perfect for special occasions.',
                'short' => 'Net embroidered hijab',
                'price' => 699,
                'variants' => ['One Size'],
                'img' => $imgBase.'/net-hijab'.$imgSizes,
            ],
            [
                'name' => 'Silk Satin Hijab - Navy',
                'slug' => 'silk-satin-hijab-navy',
                'brand' => 'dhaka-drape',
                'cat' => 'women-hijabs',
                'subnavbar' => 'women-hijabs',
                'desc' => 'Luxurious silk satin hijab in navy blue. Smooth and elegant drape.',
                'short' => 'Navy silk satin hijab',
                'price' => 1099,
                'variants' => ['One Size'],
                'img' => $imgBase.'/satin-hijab'.$imgSizes,
            ],

            // ===== ABAYAS (3 products) =====
            [
                'name' => 'Black Abaya with Embroidery',
                'slug' => 'black-abaya-embroidered',
                'brand' => 'dhaka-drape',
                'cat' => 'women-abayas',
                'subnavbar' => 'women-abayas',
                'desc' => 'Elegant black abaya with subtle embroidery on sleeves and border.',
                'short' => 'Black embroidered abaya',
                'price' => 2999,
                'variants' => ['S', 'M', 'L', 'XL'],
                'img' => $imgBase.'/black-abaya'.$imgSizes,
            ],
            [
                'name' => 'Burgundy Open Abaya',
                'slug' => 'burgundy-open-abaya',
                'brand' => 'dhaka-drape',
                'cat' => 'women-abayas',
                'subnavbar' => 'women-abayas',
                'desc' => 'Burgundy open-front abaya with belt. Modern and modest.',
                'short' => 'Burgundy open abaya',
                'price' => 3499,
                'variants' => ['M', 'L', 'XL'],
                'img' => $imgBase.'/burgundy-abaya'.$imgSizes,
            ],
            [
                'name' => 'Navy Blue Burqa',
                'slug' => 'navy-blue-burqa',
                'brand' => 'dhaka-drape',
                'cat' => 'women-abayas',
                'subnavbar' => 'women-abayas',
                'desc' => 'Full coverage navy blue burqa. Lightweight fabric for comfort.',
                'short' => 'Navy full coverage burqa',
                'price' => 2499,
                'variants' => ['M', 'L', 'XL', 'XXL'],
                'img' => $imgBase.'/burqa'.$imgSizes,
            ],

            // ===== ACCESSORIES (12 products) =====
            // Bags
            [
                'name' => 'Brown Leather Backpack',
                'slug' => 'brown-leather-backpack',
                'brand' => 'bd-style-house',
                'cat' => 'bags',
                'subnavbar' => 'accessories-bags',
                'desc' => 'Premium brown genuine leather backpack with multiple compartments.',
                'short' => 'Brown leather backpack',
                'price' => 3499,
                'variants' => ['One Size'],
                'img' => $imgBase.'/backpack'.$imgSizes,
            ],
            [
                'name' => 'Black Handbag Tote',
                'slug' => 'black-handbag-tote',
                'brand' => 'bd-style-house',
                'cat' => 'bags',
                'subnavbar' => 'accessories-bags',
                'desc' => 'Elegant black tote handbag with gold hardware. Spacious interior.',
                'short' => 'Black tote handbag',
                'price' => 2499,
                'variants' => ['One Size'],
                'img' => $imgBase.'/handbag'.$imgSizes,
            ],
            // Watches
            [
                'name' => 'Silver Analog Watch',
                'slug' => 'silver-analog-watch',
                'brand' => 'pride-garments',
                'cat' => 'watches',
                'subnavbar' => 'accessories-watches',
                'desc' => 'Stylish silver analog watch with leather strap. Water resistant.',
                'short' => 'Silver analog leather watch',
                'price' => 1999,
                'variants' => ['One Size'],
                'img' => $imgBase.'/watch'.$imgSizes,
            ],
            [
                'name' => 'Gold Digital Smart Watch',
                'slug' => 'gold-digital-smart-watch',
                'brand' => 'bd-style-house',
                'cat' => 'watches',
                'subnavbar' => 'accessories-watches',
                'desc' => 'Gold tone smart watch with fitness tracking. Bluetooth connectivity.',
                'short' => 'Gold smart fitness watch',
                'price' => 3999,
                'variants' => ['One Size'],
                'img' => $imgBase.'/smartwatch'.$imgSizes,
            ],
            // Jewelry
            [
                'name' => 'Gold Plated Necklace Set',
                'slug' => 'gold-plated-necklace-set',
                'brand' => 'nokshi-fashion',
                'cat' => 'jewelry',
                'subnavbar' => 'accessories-jewelry',
                'desc' => 'Beautiful gold plated necklace set with earrings. Wedding jewelry.',
                'short' => 'Gold necklace earring set',
                'price' => 2999,
                'variants' => ['One Size'],
                'img' => $imgBase.'/necklace'.$imgSizes,
            ],
            [
                'name' => 'Traditional Bangles Set',
                'slug' => 'traditional-bangles-set',
                'brand' => 'nokshi-fashion',
                'cat' => 'jewelry',
                'subnavbar' => 'accessories-jewelry',
                'desc' => 'Set of 6 traditional glass bangles in assorted colors. Bengali tradition.',
                'short' => '6-piece bangles set',
                'price' => 499,
                'variants' => ['One Size'],
                'img' => $imgBase.'/bangles'.$imgSizes,
            ],
            // Belts
            [
                'name' => 'Black Leather Belt',
                'slug' => 'black-leather-belt',
                'brand' => 'pride-garments',
                'cat' => 'belts',
                'subnavbar' => 'accessories-belts',
                'desc' => 'Classic black leather belt with silver buckle. Universal fit.',
                'short' => 'Black leather belt',
                'price' => 899,
                'variants' => ['28', '30', '32', '34', '36'],
                'img' => $imgBase.'/black-belt'.$imgSizes,
            ],
            [
                'name' => 'Brown Reversible Belt',
                'slug' => 'brown-reversible-belt',
                'brand' => 'bd-style-house',
                'cat' => 'belts',
                'subnavbar' => 'accessories-belts',
                'desc' => '2-in-1 reversible belt (brown/black). Great value formal accessory.',
                'short' => 'Reversible brown/black belt',
                'price' => 1299,
                'variants' => ['30', '32', '34', '36'],
                'img' => $imgBase.'/brown-belt'.$imgSizes,
            ],
            // Sunglasses
            [
                'name' => 'Aviator Sunglasses Gold',
                'slug' => 'aviator-sunglasses-gold',
                'brand' => 'pride-garments',
                'cat' => 'sunglasses',
                'subnavbar' => 'accessories-sunglasses',
                'desc' => 'Classic aviator sunglasses with gold frame and UV protection.',
                'short' => 'Gold aviator shades',
                'price' => 1499,
                'variants' => ['One Size'],
                'img' => $imgBase.'/sunglasses'.$imgSizes,
            ],
            [
                'name' => 'Wayfarer Sunglasses Black',
                'slug' => 'wayfarer-sunglasses-black',
                'brand' => 'urban-fit',
                'cat' => 'sunglasses',
                'subnavbar' => 'accessories-sunglasses',
                'desc' => 'Trendy wayfarer style sunglasses. Black frame with polarized lenses.',
                'short' => 'Black wayfarer sunglasses',
                'price' => 1199,
                'variants' => ['One Size'],
                'img' => $imgBase.'/wayfarer'.$imgSizes,
            ],
            // Perfumes
            [
                'name' => 'Attar Perfume Gift Set',
                'slug' => 'attar-perfume-gift-set',
                'brand' => 'bd-style-house',
                'cat' => 'perfumes',
                'subnavbar' => 'accessories-perfumes',
                'desc' => 'Premium attar perfume collection of 3 fragrances. Traditional scents.',
                'short' => '3-pack attar perfume set',
                'price' => 999,
                'variants' => ['One Size'],
                'img' => $imgBase.'/attar'.$imgSizes,
            ],
            [
                'name' => 'Deodorant Spray 200ml',
                'slug' => 'deodorant-spray-200ml',
                'brand' => 'pride-garments',
                'cat' => 'perfumes',
                'subnavbar' => 'accessories-perfumes',
                'desc' => 'Long-lasting deodorant body spray. Fresh fragrance for everyday use.',
                'short' => 'Body spray deodorant',
                'price' => 599,
                'variants' => ['One Size'],
                'img' => $imgBase.'/deodorant'.$imgSizes,
            ],
        ];

        // Seed all products
        foreach ($products as $pData) {
            $product = Product::firstOrCreate(
                ['slug' => $pData['slug']],
                [
                    'brand_id' => $brandMap[$pData['brand']] ?? null,
                    'name' => $pData['name'],
                    'short_description' => $pData['short'],
                    'description' => $pData['desc'],
                    'product_type' => 'physical',
                    'status' => 'active',
                    'visibility' => 'public',
                    'published_at' => now(),
                    'subnavbar_item_id' => $subnavbarMap[$pData['subnavbar']] ?? null,
                ]
            );

            // Attach category
            if (isset($categoryIds[$pData['cat']])) {
                $product->categories()->syncWithoutDetaching([$categoryIds[$pData['cat']]]);
            }

            // Create variants (with size/color variations)
            $colorForProduct = null;
            // Determine color based on product name
            $productNameLower = strtolower($pData['name']);
            if (str_contains($productNameLower, 'white')) {
                $colorForProduct = ['color' => 'White', 'color_hex' => 'FFFFFF'];
            } elseif (str_contains($productNameLower, 'black')) {
                $colorForProduct = ['color' => 'Black', 'color_hex' => '111827'];
            } elseif (str_contains($productNameLower, 'blue') || str_contains($productNameLower, 'navy')) {
                $colorForProduct = ['color' => 'Blue', 'color_hex' => '1E40AF'];
            } elseif (str_contains($productNameLower, 'red') || str_contains($productNameLower, 'maroon')) {
                $colorForProduct = ['color' => 'Red', 'color_hex' => 'DC2626'];
            } elseif (str_contains($productNameLower, 'green') || str_contains($productNameLower, 'olive')) {
                $colorForProduct = ['color' => 'Green', 'color_hex' => '16A34A'];
            } elseif (str_contains($productNameLower, 'grey') || str_contains($productNameLower, 'gray')) {
                $colorForProduct = ['color' => 'Grey', 'color_hex' => '6B7280'];
            } elseif (str_contains($productNameLower, 'brown')) {
                $colorForProduct = ['color' => 'Brown', 'color_hex' => '92400E'];
            } elseif (str_contains($productNameLower, 'pink')) {
                $colorForProduct = ['color' => 'Pink', 'color_hex' => 'EC4899'];
            } elseif (str_contains($productNameLower, 'purple')) {
                $colorForProduct = ['color' => 'Purple', 'color_hex' => '7C3AED'];
            } elseif (str_contains($productNameLower, 'yellow')) {
                $colorForProduct = ['color' => 'Yellow', 'color_hex' => 'EAB308'];
            } elseif (str_contains($productNameLower, 'orange')) {
                $colorForProduct = ['color' => 'Orange', 'color_hex' => 'EA580C'];
            } elseif (str_contains($productNameLower, 'beige') || str_contains($productNameLower, 'off-white')) {
                $colorForProduct = ['color' => 'Beige', 'color_hex' => 'F5F5DC'];
            } else {
                $colorForProduct = null;
            }

            foreach ($pData['variants'] as $i => $variantName) {
                $sku = strtoupper(Str::slug($pData['slug'])).'-'.($i + 1);
                $price = $pData['price'];

                // Add price variation for sizes
                $sizeMultiplier = 1;
                if (in_array($variantName, ['XXL', '3XL', '36', '44'])) {
                    $sizeMultiplier = 1.1;
                }

                $attributes = [];
                if ($colorForProduct) {
                    $attributes['color'] = $colorForProduct['color'];
                    $attributes['color_hex'] = $colorForProduct['color_hex'];
                }
                // For products that have size variants like S/M/L/XL, add size attribute
                if (in_array($variantName, ['S', 'M', 'L', 'XL', 'XXL', '3XL', '28', '30', '32', '34', '36', '38', '40', '42', '44', '5.5 Yards', '6 Yards', 'One Size'])) {
                    $attributes['size'] = $variantName;
                }

                ProductVariant::firstOrCreate(
                    ['product_id' => $product->id, 'sku' => $sku],
                    [
                        'name' => $variantName,
                        'barcode' => (string) Str::uuid(),
                        'sale_price' => (int) round($price * $sizeMultiplier),
                        'cost_price' => (int) round($price * $sizeMultiplier * 0.6),
                        'track_inventory' => true,
                        'status' => 'active',
                        'attributes' => ! empty($attributes) ? $attributes : null,
                    ]
                );
            }

            // Create product image
            ProductImage::firstOrCreate(
                ['product_id' => $product->id, 'image_url' => $pData['img']],
                [
                    'alt_text' => $pData['name'],
                    'sort_order' => 0,
                    'is_main' => true,
                ]
            );
        }

        // ===== Extra products for Sale and New Arrivals =====
        $saleSubnavbarId = $subnavbarMap['sale'] ?? null;
        $newArrivalsSubnavbarId = $subnavbarMap['new-arrivals'] ?? null;

        // Assign some existing products to Sale
        if ($saleSubnavbarId) {
            $saleSlugs = ['classic-white-cotton-tshirt', 'black-edition-premium-tee', 'striped-casual-blue-tee',
                'white-formal-shirt', 'blue-checkered-casual-shirt', 'white-cotton-kurti-embroidered',
                'black-palazzo-pants', 'cotton-hijab-multipack', 'deodorant-spray-200ml'];
            Product::whereIn('slug', $saleSlugs)->update(['subnavbar_item_id' => $saleSubnavbarId]);
        }

        // Assign some products to New Arrivals
        if ($newArrivalsSubnavbarId) {
            $newSlugs = ['navy-blue-polo-tshirt', 'black-slim-fit-shirt', 'green-embroidered-panjabi-set',
                'black-abaya-embroidered', 'gold-digital-smart-watch', 'floral-maxi-dress'];
            Product::whereIn('slug', $newSlugs)->update(['subnavbar_item_id' => $newArrivalsSubnavbarId]);
        }

        $this->command->info('Garments product seeder completed successfully!');
        $this->command->info('Total products seeded: '.count($products));
    }
}
