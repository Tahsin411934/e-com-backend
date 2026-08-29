<?php

namespace Modules\Cart\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Cart\Models\Cart;
use Modules\Cart\Models\CartItem;
use Modules\Cart\Models\Coupon;
use Modules\Cart\Models\Wishlist;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductVariant;
use Modules\Identity\Models\User;
use Modules\Store\Models\Store;

class CartDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed coupons
        $coupons = [
            [
                'code' => 'WELCOME10',
                'discount_type' => 'percentage',
                'discount_value' => 10.0000,
                'minimum_order_amount' => 50.0000,
                'usage_limit' => 100,
                'usage_limit_per_user' => 1,
                'used_count' => 0,
                'starts_at' => now()->subDays(7),
                'ends_at' => now()->addDays(30),
                'status' => 'active',
            ],
            [
                'code' => 'FLAT20',
                'discount_type' => 'fixed_amount',
                'discount_value' => 20.0000,
                'minimum_order_amount' => 100.0000,
                'usage_limit' => 50,
                'usage_limit_per_user' => 2,
                'used_count' => 5,
                'starts_at' => now()->subDays(3),
                'ends_at' => now()->addDays(60),
                'status' => 'active',
            ],
            [
                'code' => 'FREESHIP',
                'discount_type' => 'free_shipping',
                'discount_value' => 0.0000,
                'minimum_order_amount' => 75.0000,
                'usage_limit' => 200,
                'usage_limit_per_user' => 5,
                'used_count' => 12,
                'starts_at' => now()->subDays(1),
                'ends_at' => now()->addDays(90),
                'status' => 'active',
            ],
            [
                'code' => 'SUMMER25',
                'discount_type' => 'percentage',
                'discount_value' => 25.0000,
                'minimum_order_amount' => 150.0000,
                'usage_limit' => 30,
                'usage_limit_per_user' => 1,
                'used_count' => 0,
                'starts_at' => now()->addDays(1),
                'ends_at' => now()->addDays(30),
                'status' => 'active',
            ],
            [
                'code' => 'EXPIRED5',
                'discount_type' => 'percentage',
                'discount_value' => 5.0000,
                'minimum_order_amount' => 25.0000,
                'usage_limit' => 100,
                'usage_limit_per_user' => 3,
                'used_count' => 88,
                'starts_at' => now()->subDays(60),
                'ends_at' => now()->subDays(10),
                'status' => 'inactive',
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::firstOrCreate(
                ['code' => $coupon['code']],
                $coupon
            );
        }

        // Seed carts with items
        $users = User::where('status', 'active')->take(3)->get();
        $stores = Store::where('status', 'active')->take(2)->get();
        $variants = ProductVariant::where('status', 'active')->take(6)->get();

        if ($users->count() > 0 && $stores->count() > 0 && $variants->count() > 0) {
            // Cart 1 - active cart for first user
            $cart1 = Cart::create([
                'user_id' => $users->first()->id,
                'store_id' => $stores->first()->id,
                'status' => 'active',
                'expires_at' => now()->addDays(7),
            ]);

            CartItem::create([
                'cart_id' => $cart1->id,
                'variant_id' => $variants[0]->id,
                'quantity' => 2,
                'unit_price' => $variants[0]->sale_price,
            ]);

            CartItem::create([
                'cart_id' => $cart1->id,
                'variant_id' => $variants[1]->id,
                'quantity' => 1,
                'unit_price' => $variants[1]->sale_price,
            ]);

            // Cart 2 - active cart for second user
            if ($users->count() > 1) {
                $cart2 = Cart::create([
                    'user_id' => $users[1]->id,
                    'store_id' => $stores->first()->id,
                    'status' => 'active',
                    'expires_at' => now()->addDays(7),
                ]);

                CartItem::create([
                    'cart_id' => $cart2->id,
                    'variant_id' => $variants[2]->id,
                    'quantity' => 3,
                    'unit_price' => $variants[2]->sale_price,
                ]);
            }

            // Cart 3 - abandoned cart
            $cart3 = Cart::create([
                'user_id' => $users->last()->id,
                'store_id' => $stores->last()->id,
                'status' => 'abandoned',
                'expires_at' => now()->subDays(2),
            ]);

            CartItem::create([
                'cart_id' => $cart3->id,
                'variant_id' => $variants[3]->id,
                'quantity' => 1,
                'unit_price' => $variants[3]->sale_price,
            ]);

        }

        // Seed wishlists using distinct products
        $products = Product::where('status', 'active')->take(3)->get();

        if ($users->count() > 0 && $products->count() > 0) {
            Wishlist::firstOrCreate([
                'user_id' => $users->first()->id,
                'product_id' => $products[0]->id,
            ]);

            if ($products->count() > 1) {
                Wishlist::firstOrCreate([
                    'user_id' => $users->first()->id,
                    'product_id' => $products[1]->id,
                ]);
            }

            if ($users->count() > 1 && $products->count() > 2) {
                Wishlist::firstOrCreate([
                    'user_id' => $users[1]->id,
                    'product_id' => $products[2]->id,
                ]);
            }
        }
    }
}
