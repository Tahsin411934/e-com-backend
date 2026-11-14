<?php

namespace Modules\Frontend\Services;

use Modules\Frontend\Models\Setting;

class CentralSettingService
{
    // Explicitly public fields; newly added admin settings are private by default.
    private const PUBLIC_KEYS = [
        'site_name', 'site_logo', 'site_favicon', 'site_description', 'primary_color', 'primary_text_color',
        'facebook_url', 'twitter_url', 'instagram_url', 'youtube_url', 'whatsapp_number',
        'phone', 'email', 'address', 'meta_title', 'meta_description',
        'gtm_enabled', 'gtm_id', 'gtm_container_url', 'gtm_header_code', 'gtm_body_code',
    ];

    public function flat(): array
    {
        $flat = [];

        $settings = Setting::query()
            ->whereNull('store_id')
            ->whereIn('key', self::PUBLIC_KEYS)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['key', 'value', 'type']);

        foreach ($settings as $setting) {
            $value = $setting->value;

            if ($setting->type === 'image' && $value && ! preg_match('~^https?://~i', $value)) {
                $value = asset(ltrim($value, '/'));
            }

            $flat[$setting->key] = $value;
        }

        return $flat;
    }
}
