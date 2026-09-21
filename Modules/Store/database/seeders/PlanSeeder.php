<?php

namespace Modules\Store\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Store\Models\Plan;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Free Trial', 'slug' => 'free-trial', 'description' => 'Try the complete store experience for free.', 'price' => 0, 'duration_days' => 7, 'product_limit' => 50, 'is_free' => true, 'features' => ['7-day access', 'Up to 50 products', 'Storefront subdomain']],
            ['name' => 'Starter', 'slug' => 'starter', 'description' => 'For growing small businesses.', 'price' => 499, 'duration_days' => 30, 'product_limit' => 500, 'is_free' => false, 'features' => ['500 products', 'Store management', 'Priority support']],
            ['name' => 'Professional', 'slug' => 'professional', 'description' => 'For established businesses with larger catalogs.', 'price' => 999, 'duration_days' => 30, 'product_limit' => null, 'is_free' => false, 'features' => ['Unlimited products', 'Advanced store tools', 'Priority support']],
        ] as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], array_merge($plan, ['currency' => 'BDT', 'is_public' => true, 'is_active' => true]));
        }
    }
}
