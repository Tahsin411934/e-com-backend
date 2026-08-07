<?php

namespace Modules\Reviews\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Reviews\Models\ProductReview;
use Modules\Catalog\Models\Product;
use Modules\Identity\Models\User;

class ReviewsDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $product = Product::first();
        $user = User::where('email', 'admin@example.com')->first();

        if (!$product || !$user) {
            $this->command->warn('No products or users found. Skipping Reviews seeding.');
            return;
        }

        // Sample Review
        ProductReview::firstOrCreate(
            ['product_id' => $product->id, 'user_id' => $user->id],
            [
                'rating' => 5,
                'title' => 'Excellent product!',
                'body' => 'This is a fantastic product. Would highly recommend to everyone.',
                'status' => 'approved',
                'is_verified_purchase' => true,
            ]
        );

        $this->command->info('Reviews module seeded successfully!');
    }
}