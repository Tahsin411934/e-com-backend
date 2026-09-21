<?php

namespace Modules\Store\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Store\Models\Plan;
use Modules\Store\Models\Feature;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $features = [
            ['name' => 'Storefront Access', 'slug' => 'storefront_access', 'type' => 'boolean'],
            ['name' => 'Pixel Setup', 'slug' => 'pixel_setup', 'type' => 'boolean'],
            ['name' => 'Google Analytics', 'slug' => 'google_analytics', 'type' => 'boolean'],
            ['name' => 'Meta Ads Campaign', 'slug' => 'meta_ads_campaign', 'type' => 'boolean'],
            ['name' => 'Google Ads Campaign', 'slug' => 'google_ads_campaign', 'type' => 'boolean'],
            ['name' => 'Conversion API', 'slug' => 'conversion_api', 'type' => 'boolean'],
            ['name' => 'Advanced Reports', 'slug' => 'advanced_reports', 'type' => 'boolean'],
            ['name' => 'Priority Support', 'slug' => 'priority_support', 'type' => 'boolean'],
        ];
        foreach ($features as $feature) Feature::updateOrCreate(['slug' => $feature['slug']], $feature + ['is_active' => true]);

        $featureIds = fn (array $slugs) => Feature::whereIn('slug', $slugs)->pluck('id');
        foreach ([
            ['name' => 'Free Trial', 'slug' => 'free-trial', 'description' => 'Try the complete store experience for free.', 'price' => 0, 'duration_days' => 7, 'product_limit' => 50, 'is_free' => true, 'features' => ['7-day access', 'Up to 50 products', 'Storefront subdomain']],
            ['name' => 'Starter', 'slug' => 'starter', 'description' => 'For growing small businesses.', 'price' => 499, 'duration_days' => 30, 'product_limit' => 500, 'is_free' => false, 'features' => ['500 products', 'Store management', 'Priority support']],
            ['name' => 'Professional', 'slug' => 'professional', 'description' => 'For established businesses with larger catalogs.', 'price' => 999, 'duration_days' => 30, 'product_limit' => null, 'is_free' => false, 'features' => ['Unlimited products', 'Advanced store tools', 'Priority support']],
        ] as $plan) {
            $featureSlugs = match ($plan['slug']) {
                'free-trial' => ['storefront_access'],
                'starter' => ['storefront_access', 'pixel_setup', 'google_analytics'],
                default => ['storefront_access', 'pixel_setup', 'google_analytics', 'meta_ads_campaign', 'google_ads_campaign', 'conversion_api', 'advanced_reports', 'priority_support'],
            };
            $record = Plan::updateOrCreate(['slug' => $plan['slug']], array_merge($plan, ['currency' => 'BDT', 'is_public' => true, 'is_active' => true]));
            $record->features()->syncWithPivotValues($featureIds($featureSlugs), ['enabled' => true]);
        }
    }
}
