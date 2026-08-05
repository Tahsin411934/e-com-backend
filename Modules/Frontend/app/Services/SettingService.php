<?php

namespace Modules\Frontend\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Frontend\Models\Setting;

class SettingService
{
    const TYPES = [
        'text'     => ['label' => 'Text', 'validation' => 'nullable|string|max:500'],
        'textarea' => ['label' => 'Textarea', 'validation' => 'nullable|string|max:5000'],
        'image'    => ['label' => 'Image', 'validation' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'],
        'color'    => ['label' => 'Color', 'validation' => 'nullable|string|max:7'],
        'tel'      => ['label' => 'Phone', 'validation' => 'nullable|string|max:30'],
        'email'    => ['label' => 'Email', 'validation' => 'nullable|email|max:255'],
        'url'      => ['label' => 'URL', 'validation' => 'nullable|url|max:500'],
    ];

    public static function getDefaults(): array
    {
        return [
            ['group' => 'general', 'key' => 'site_name',       'label' => 'Site Name',        'type' => 'text',     'value' => 'Shopio',              'sort_order' => 1],
            ['group' => 'general', 'key' => 'site_logo',       'label' => 'Site Logo',        'type' => 'image',    'value' => null,                  'sort_order' => 2],
            ['group' => 'general', 'key' => 'site_description','label' => 'Site Description',  'type' => 'textarea', 'value' => 'Your premium online shopping destination.', 'sort_order' => 3],
            ['group' => 'general', 'key' => 'primary_color', 'label' => 'Primary Color',     'type' => 'color',   'value' => '#22C55E', 'sort_order' => 4],
            ['group' => 'social',  'key' => 'facebook_url',    'label' => 'Facebook URL',       'type' => 'url',  'value' => '#', 'sort_order' => 1],
            ['group' => 'social',  'key' => 'twitter_url',     'label' => 'Twitter URL',        'type' => 'url',  'value' => '#', 'sort_order' => 2],
            ['group' => 'social',  'key' => 'instagram_url',   'label' => 'Instagram URL',      'type' => 'url',  'value' => '#', 'sort_order' => 3],
            ['group' => 'social',  'key' => 'youtube_url',     'label' => 'Youtube URL',        'type' => 'url',  'value' => '#', 'sort_order' => 4],
            ['group' => 'social',  'key' => 'whatsapp_number', 'label' => 'WhatsApp Number',    'type' => 'tel',  'value' => '+8801234567890', 'sort_order' => 5],
            ['group' => 'contact', 'key' => 'phone',   'label' => 'Phone Number', 'type' => 'tel',     'value' => '+880 123-456-7890', 'sort_order' => 1],
            ['group' => 'contact', 'key' => 'email',   'label' => 'Email Address','type' => 'email',    'value' => 'support@shopio.com', 'sort_order' => 2],
            ['group' => 'contact', 'key' => 'address', 'label' => 'Address',      'type' => 'textarea', 'value' => '123 Commerce Ave, Dhaka, Bangladesh', 'sort_order' => 3],
            ['group' => 'seo',     'key' => 'meta_title',       'label' => 'Default Meta Title',       'type' => 'text',     'value' => 'Shopio - Premium E-Commerce', 'sort_order' => 1],
            ['group' => 'seo',     'key' => 'meta_description', 'label' => 'Default Meta Description', 'type' => 'textarea', 'value' => 'Shopio is your premium online shopping destination.', 'sort_order' => 2],
        ];
    }

    public function getAll(): array
    {
        return Setting::orderBy('group')->orderBy('sort_order')->get()
            ->keyBy('key')
            ->map(fn (Setting $s) => [
                'id'      => $s->id,
                'group'   => $s->group,
                'key'     => $s->key,
                'value'   => $s->value,
                'type'    => $s->type,
                'label'   => $s->label,
                'sort_order' => $s->sort_order,
            ])
            ->all();
    }

    public function getGrouped(): array
    {
        $all = $this->getAll();
        $grouped = [];
        foreach ($all as $setting) {
            $grouped[$setting['group']][] = $setting;
        }
        return $grouped;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $all = $this->getAll();
        return $all[$key]['value'] ?? $default;
    }

    public function updateBulk(array $settings): void
    {
        foreach ($settings as $key => $value) {
            Setting::where('key', $key)->update(['value' => $value]);
        }
    }

    public function uploadImage(UploadedFile $file): string
    {
        $path = $file->store('settings', 'public');
        return Storage::url($path);
    }

    public function seedDefaults(): void
    {
        foreach (self::getDefaults() as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}