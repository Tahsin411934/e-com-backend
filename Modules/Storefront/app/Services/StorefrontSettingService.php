<?php

namespace Modules\Storefront\Services;

use Modules\Frontend\Models\Setting;
use Modules\Store\Support\CurrentStore;
use Modules\Store\Models\Store;

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
        $storeId = CurrentStore::id();
        $storeSettings = Setting::query()
            ->where('store_id', $storeId)
            ->orderBy('sort_order')
            ->get()
            ->keyBy('key');

        $settings = Setting::query()
            ->where(function ($query) use ($storeId) {
                $query->whereNull('store_id')->orWhere('store_id', $storeId);
            })
            ->orderBy('sort_order')
            ->get()
            ->sortBy(fn (Setting $setting) => $setting->store_id === null ? 0 : 1)
            ->keyBy('key');

        $flat = [];

        foreach ($settings as $setting) {
            $value = $setting->value;

            if ($setting->type === 'image' && $value && ! str_starts_with($value, 'http')) {
                $value = asset(ltrim($value, '/'));
            }

            $flat[$setting->key] = $value;
        }

        // A platform logo must never appear as a tenant's storefront logo.
        // Store owners either see their own uploaded asset or their store name.
        if ($storeId !== null) {
            if (! $storeSettings->has('site_logo')) {
                unset($flat['site_logo']);
                $flat['site_name'] = Store::find($storeId)?->name;
            }
            if (! $storeSettings->has('site_favicon')) unset($flat['site_favicon']);
        }

        // A store always has a name fallback, but never inherit global
        // settings. The uploaded store logo remains optional.
        if (blank($flat['site_name'] ?? null)) {
            $flat['site_name'] = Store::find($storeId)?->name;
        }

        return $flat;
    }
}
