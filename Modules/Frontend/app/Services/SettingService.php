<?php

namespace Modules\Frontend\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Frontend\Models\Setting;
use Modules\Identity\Models\User;
use Yajra\DataTables\DataTables;

class SettingService
{
    const TYPES = [
        'text' => ['label' => 'Text', 'validation' => 'nullable|string|max:500'],
        'textarea' => ['label' => 'Textarea', 'validation' => 'nullable|string|max:5000'],
        'image' => ['label' => 'Image', 'validation' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'],
        'color' => ['label' => 'Color', 'validation' => 'nullable|string|max:7'],
        'tel' => ['label' => 'Phone', 'validation' => 'nullable|string|max:30'],
        'email' => ['label' => 'Email', 'validation' => 'nullable|email|max:255'],
        'url' => ['label' => 'URL', 'validation' => 'nullable|url|max:500'],
    ];

    public static function getDefaults(): array
    {
        return [
            ['group' => 'general', 'key' => 'site_name',       'label' => 'Site Name',        'type' => 'text',     'value' => 'Shopio',              'sort_order' => 1],
            ['group' => 'general', 'key' => 'site_logo',       'label' => 'Site Logo',        'type' => 'image',    'value' => null,                  'sort_order' => 2],
            ['group' => 'general', 'key' => 'site_favicon',    'label' => 'Site Favicon / Icon', 'type' => 'image',    'value' => null,                  'sort_order' => 3],
            ['group' => 'general', 'key' => 'site_description', 'label' => 'Site Description',  'type' => 'textarea', 'value' => 'Your premium online shopping destination.', 'sort_order' => 4],
            ['group' => 'general', 'key' => 'primary_color', 'label' => 'Primary Color',     'type' => 'color',   'value' => '#22C55E', 'sort_order' => 5],
            ['group' => 'social',  'key' => 'facebook_url',    'label' => 'Facebook URL',       'type' => 'url',  'value' => '', 'sort_order' => 1],
            ['group' => 'social',  'key' => 'twitter_url',     'label' => 'Twitter URL',        'type' => 'url',  'value' => '', 'sort_order' => 2],
            ['group' => 'social',  'key' => 'instagram_url',   'label' => 'Instagram URL',      'type' => 'url',  'value' => '', 'sort_order' => 3],
            ['group' => 'social',  'key' => 'youtube_url',     'label' => 'Youtube URL',        'type' => 'url',  'value' => '', 'sort_order' => 4],
            ['group' => 'social',  'key' => 'whatsapp_number', 'label' => 'WhatsApp Number',    'type' => 'tel',  'value' => '+8801234567890', 'sort_order' => 5],
            ['group' => 'contact', 'key' => 'phone',   'label' => 'Phone Number', 'type' => 'tel',     'value' => '+880 123-456-7890', 'sort_order' => 1],
            ['group' => 'contact', 'key' => 'email',   'label' => 'Email Address', 'type' => 'email',    'value' => 'support@shopio.com', 'sort_order' => 2],
            ['group' => 'contact', 'key' => 'address', 'label' => 'Address',      'type' => 'textarea', 'value' => '123 Commerce Ave, Dhaka, Bangladesh', 'sort_order' => 3],
            ['group' => 'seo',     'key' => 'meta_title',       'label' => 'Default Meta Title',       'type' => 'text',     'value' => 'Shopio - Premium E-Commerce', 'sort_order' => 1],
            ['group' => 'seo',     'key' => 'meta_description', 'label' => 'Default Meta Description', 'type' => 'textarea', 'value' => 'Shopio is your premium online shopping destination.', 'sort_order' => 2],

            // Marketing - Google Tag Manager (GTM)
            ['group' => 'marketing', 'key' => 'gtm_enabled',       'label' => 'Enable GTM',                'type' => 'text',     'value' => '0', 'sort_order' => 1],
            ['group' => 'marketing', 'key' => 'gtm_id',            'label' => 'GTM Container ID',         'type' => 'text',     'value' => '', 'sort_order' => 2],
            ['group' => 'marketing', 'key' => 'gtm_container_url', 'label' => 'GTM Container URL (optional)', 'type' => 'url', 'value' => '', 'sort_order' => 3],
            ['group' => 'marketing', 'key' => 'gtm_header_code',   'label' => 'GTM Head Code',            'type' => 'textarea', 'value' => '', 'sort_order' => 4],
            ['group' => 'marketing', 'key' => 'gtm_body_code',     'label' => 'GTM Body (noscript) Code', 'type' => 'textarea', 'value' => '', 'sort_order' => 5],
        ];
    }

    /**
     * Resolve the settings scope for the acting user:
     *  - Store Owner (with a store)  → their owned store id
     *  - Store Owner (without store) → 0 (owns nothing; read-only global view)
     *  - Everyone else (platform)    → null (global settings)
     */
    private function resolveStoreScope(?User $user): ?int
    {
        if (! $user || ! $user->isStoreOwner()) {
            return null;
        }

        $store = $user->ownedStore()->first(['id']);

        return $store ? (int) $store->id : 0;
    }

    /**
     * Final write/read target scope: Store Owners are always forced to their
     * own store; platform staff may explicitly target a store (or global/null).
     */
    private function targetScope(?int $requestedStoreId): ?int
    {
        $ownedStoreId = $this->resolveStoreScope(auth()->user());

        if ($ownedStoreId !== null) {
            return $ownedStoreId > 0 ? $ownedStoreId : null;
        }

        return $requestedStoreId > 0 ? (int) $requestedStoreId : null;
    }

    /**
     * Whether the acting user may write settings (owners need a store).
     */
    public function canManage(?int $requestedStoreId = null): bool
    {
        if ($this->resolveStoreScope(auth()->user()) === 0) {
            return false;
        }

        return true;
    }

    /**
     * All settings visible to the acting user, keyed by setting key.
     * Global rows (store_id NULL) are the base; the target store's rows
     * override them per key.
     */
    public function getAll(?int $requestedStoreId = null): array
    {
        // Store owners must only see settings belonging to their own store.
        // Do not fall back to platform/global (store_id = NULL) settings.
        $ownerScope = $this->resolveStoreScope(auth()->user());
        if ($ownerScope !== null) {
            if ($ownerScope === 0) {
                return [];
            }

            // Use global rows only as field definitions. Never copy their
            // values into the owner's form; only the owner's own value may
            // be displayed (especially for site_logo).
            $definitions = Setting::whereNull('store_id')
                ->orderBy('group')->orderBy('sort_order')
                ->get()
                ->keyBy('key');
            $owned = Setting::where('store_id', $ownerScope)
                ->get()
                ->keyBy('key');

            return $definitions->map(function (Setting $definition) use ($owned) {
                $setting = $owned->get($definition->key);

                return [
                    'id' => $setting?->id ?? $definition->id,
                    'group' => $definition->group,
                    'key' => $definition->key,
                    'value' => $setting?->value,
                    'type' => $definition->type,
                    'label' => $definition->label,
                    'sort_order' => $definition->sort_order,
                ];
            })->values()->all();
        }

        // Platform admin editing a selected store must read only that store's
        // rows. A NULL store_id is not treated as a fallback/global setting.
        if ($requestedStoreId !== null && $requestedStoreId > 0) {
            return Setting::where('store_id', $requestedStoreId)
                ->orderBy('group')->orderBy('sort_order')
                ->get()
                ->map(fn (Setting $setting) => [
                    'id' => $setting->id,
                    'group' => $setting->group,
                    'key' => $setting->key,
                    'value' => $setting->value,
                    'type' => $setting->type,
                    'label' => $setting->label,
                    'sort_order' => $setting->sort_order,
                ])
                ->all();
        }

        $base = Setting::whereNull('store_id')
            ->orderBy('group')->orderBy('sort_order')
            ->get()
            ->keyBy('key');

        $scope = $this->targetScope($requestedStoreId);

        if ($scope !== null) {
            Setting::where('store_id', $scope)
                ->orderBy('group')->orderBy('sort_order')
                ->get()
                ->each(fn (Setting $override) => $base->put($override->key, $override));
        }

        return $base
            ->map(fn (Setting $s) => [
                'id' => $s->id,
                'group' => $s->group,
                'key' => $s->key,
                'value' => $s->value,
                'type' => $s->type,
                'label' => $s->label,
                'sort_order' => $s->sort_order,
            ])
            ->all();
    }

    public function getGrouped(?int $requestedStoreId = null): array
    {
        $all = $this->getAll($requestedStoreId);
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

    /**
     * Bulk-update settings in the acting user's scope. Store Owners always
     * write to their own store's rows (created on demand from the global
     * definition); platform staff write to the requested store or global.
     */
    public function updateBulk(array $settings, ?int $requestedStoreId = null): void
    {
        $scope = $this->targetScope($requestedStoreId);

        foreach ($settings as $key => $value) {
            $row = Setting::where('key', $key)
                ->where('store_id', $scope)
                ->first();

            if ($row) {
                $row->update(['value' => $value]);

                continue;
            }

            // No scoped row yet — clone the global definition for this scope.
            $definition = Setting::where('key', $key)->whereNull('store_id')->first();

            if (! $definition) {
                continue;
            }

            Setting::create([
                'store_id' => $scope,
                'group' => $definition->group,
                'key' => $definition->key,
                'value' => $value,
                'type' => $definition->type,
                'label' => $definition->label,
                'sort_order' => $definition->sort_order,
            ]);
        }
    }

    public function uploadImage(UploadedFile $file): string
    {
        $path = $file->store('settings', 'public');

        return Storage::url($path);
    }

    public function seedDefaults(?int $requestedStoreId = null): void
    {
        $scope = $this->targetScope($requestedStoreId);

        foreach (self::getDefaults() as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key'], 'store_id' => $scope],
                $setting + ['store_id' => $scope]
            );
        }
    }

    /**
     * Platform-staff DataTable: every settings row across the platform —
     * global definitions (store_id NULL) and per-store overrides — with a
     * store column and optional store filter.
     */
    public function getSettingsDataTable(Request $request)
    {
        $query = Setting::query()->with('store');

        // Store Owner → only their own store's rows (the table view is
        // admin-only in practice, but stays safe if reached). Platform staff
        // → all rows, optionally filtered by store (or "global").
        $ownedStoreId = $this->resolveStoreScope($request->user());

        if ($ownedStoreId !== null) {
            $query->where('store_id', $ownedStoreId);
        } elseif ($request->filled('store_id')) {
            $filter = $request->input('store_id');

            if ($filter === 'global') {
                $query->whereNull('store_id');
            } elseif (is_numeric($filter)) {
                $query->where('store_id', (int) $filter);
            }
        }

        $query->orderBy('group')->orderBy('sort_order');

        return DataTables::of($query)
            ->addColumn('store_name', function (Setting $setting) {
                return $setting->store?->name ?? 'Global (Platform)';
            })
            ->editColumn('group', function (Setting $setting) {
                return ucfirst($setting->group);
            })
            ->editColumn('value', function (Setting $setting) {
                return Str::limit((string) ($setting->value ?? '-'), 60);
            })
            ->editColumn('created_at', function (Setting $setting) {
                return $setting->created_at?->format('d M Y H:i');
            })
            ->addColumn('action', function (Setting $setting) {
                $editUrl = $setting->store_id
                    ? route('frontend.site-settings.edit', ['store_id' => $setting->store_id])
                    : route('frontend.site-settings.edit');

                $buttons = '<a href="'.$editUrl.'" class="btn-primary px-2 py-1 rounded text-sm mr-2" title="Edit in form">'
                    .'<i class="fa fa-pencil"></i></a>';

                // Only store-scoped rows can be deleted (removes the override
                // so the key falls back to the global value).
                if ($setting->store_id !== null) {
                    $buttons .= '<button onclick="siteSettingDelete('.$setting->id.')" class="bg-red-500 text-white px-2 py-1 rounded text-sm hover:bg-red-600" title="Delete override (revert to global)">'
                        .'<i class="fa fa-trash"></i></button>';
                }

                return '<div class="flex space-x-2 justify-center">'.$buttons.'</div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Delete a settings row. Global definitions are protected — only
     * per-store overrides may be deleted (reverting that key to the global
     * value).
     */
    public function deleteSetting(int $id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $setting = Setting::findOrFail($id);

                if ($setting->store_id === null) {
                    return ApiResponse::error('Global settings cannot be deleted. Edit them instead.', 403);
                }

                $ownedStoreId = $this->resolveStoreScope(auth()->user());

                if ($ownedStoreId !== null && (int) $setting->store_id !== $ownedStoreId) {
                    return ApiResponse::error('You are not allowed to delete this setting.', 403);
                }

                $setting->delete();

                return ApiResponse::success(null, 'Setting override deleted — this key now falls back to the global value.');
            });
        } catch (\Exception $e) {
            return ApiResponse::notFound('Setting not found.');
        }
    }
}
