<?php

namespace Modules\Storefront\Services;

use Modules\Frontend\Models\Setting;
use Modules\Store\Support\CurrentStore;

class StorefrontSettingService
{
    /**
     * Flat key => value map for the current storefront.
     *
     * Only the resolved store's settings are exposed. Response shape mirrors the legacy
     * /api/v1/settings endpoint so the storefront frontend can reuse it.
     *
     * @return array<string, mixed>
     */
    public function flat(): array
    {
        $storeId = CurrentStore::id();
        abort_if($storeId === null, 404, 'Store not found.');
        $storeSettings = Setting::query()
            ->where('store_id', $storeId)
            ->orderBy('sort_order')
            ->get()
            ->keyBy('key');

        $flat = [];

        foreach ($storeSettings as $setting) {
            $value = $setting->value;

            if ($setting->type === 'image' && $value && ! str_starts_with($value, 'http')) {
                $value = asset(ltrim($value, '/'));
            }

            $flat[$setting->key] = $value;
        }

        $store = CurrentStore::store();
        if (blank($flat['site_name'] ?? null)) {
            $flat['site_name'] = $store?->name;
        }

        if (blank($flat['phone'] ?? null)) $flat['phone'] = $store?->phone;
        if (blank($flat['email'] ?? null)) $flat['email'] = $store?->email;

        return array_replace($flat, ['store_id' => (string) $storeId]);
    }
}
