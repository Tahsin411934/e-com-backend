<?php

namespace Modules\Frontend\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Frontend\Models\Banner;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banners = [
            [
                'title' => 'Shop The Best Deals',
                'subtitle' => 'Up to 50% off across categories',
                'smtag' => 'SALE',
                'primary_btn' => 'Shop Now',
                'primary_btn_url' => '/shop',
                'secondary_btn' => 'View Offers',
                'secondary_btn_url' => '/deals',
                'banner_image' => 'https://via.placeholder.com/1600x600?text=Best+Deals',
                'sort_order' => 1,
                'status' => 'active',
            ],
            [
                'title' => 'Discover New Styles',
                'subtitle' => 'Fresh collections for every season',
                'smtag' => 'NEW',
                'primary_btn' => 'Browse Now',
                'primary_btn_url' => '/new-arrivals',
                'secondary_btn' => 'Shop Women',
                'secondary_btn_url' => '/products/clothing',
                'banner_image' => 'https://via.placeholder.com/1600x600?text=New+Styles',
                'sort_order' => 2,
                'status' => 'active',
            ],
            [
                'title' => 'Fast Delivery Now Available',
                'subtitle' => 'Free shipping on orders over $50',
                'smtag' => 'FREE',
                'primary_btn' => 'Start Shopping',
                'primary_btn_url' => '/products',
                'secondary_btn' => 'Learn More',
                'secondary_btn_url' => '/shipping',
                'banner_image' => 'https://via.placeholder.com/1600x600?text=Fast+Delivery',
                'sort_order' => 3,
                'status' => 'active',
            ],
        ];

        foreach ($banners as $banner) {
            Banner::create($banner);
        }
    }
}
