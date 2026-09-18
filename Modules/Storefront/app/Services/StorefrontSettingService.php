<?php

namespace Modules\Storefront\Services;

use Modules\Frontend\Models\Setting;
use Modules\Store\Support\CurrentStore;

class StorefrontSettingService
{
    /**
     * Flat key => value map for the current storefront.
     *
     * Global (store_id NULL) settings are the base; the store's own rows
     * override them per key. Response shape mirrors the legacy
     * /api/v1/settings endpoint so the storefront frontend can reuse it.
     *
     * @return array<string, mixed>
     */
    public function flat(): array
    {
        $settings = Setting::query()
            ->where(function ($query) {
                $query->whereNull('store_id')
                    ->orWhere('store_id', CurrentStore::id());
            })
            ->orderBy('sort_order')
            ->get();

        // Global rows first (base layer), store overrides last (win).
        $flat = [];

        foreach ($settings->sortBy(fn (Setting $setting) => $setting->store_id === null ? 0 : 1) as $setting) {
            $value = $setting->value;

            if ($setting->type === 'image' && $value && ! str_starts_with($value, 'http')) {
                $value = asset(ltrim($value, '/'));
            }

            $flat[$setting->key] = $value;
        }

        return $flat;
    }
}
