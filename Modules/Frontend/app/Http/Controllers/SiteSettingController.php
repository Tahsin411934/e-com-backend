<?php

namespace Modules\Frontend\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\Frontend\Models\Setting;
use Modules\Frontend\Services\SettingService;
use Modules\Store\Models\Store;

class SiteSettingController extends Controller
{
    public function __construct(
        private readonly SettingService $settingService
    ) {}

    /**
     * Target store requested via ?store_id= (platform staff only; owners are
     * always forced to their own store inside the service).
     */
    private function requestedStoreId(): ?int
    {
        $id = (int) request('store_id');

        return $id > 0 ? $id : null;
    }

    public function index()
    {
        $requestedStoreId = $this->requestedStoreId();
        $grouped = $this->settingService->getGrouped($requestedStoreId);
        $groups = ['general', 'social', 'contact', 'seo', 'marketing'];

        $user = auth()->user();
        $isStoreOwner = $user->isStoreOwner();

        // Store Owner only manages their own store; platform staff can pick any store.
        $stores = $isStoreOwner
            ? collect($user->ownedStore ? [$user->ownedStore] : [])
            : Store::query()->orderBy('name')->get(['id', 'name']);

        $selectedStoreId = $isStoreOwner ? null : $requestedStoreId;

        return view('frontend::site-settings', compact('grouped', 'groups', 'isStoreOwner', 'stores', 'selectedStoreId'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'store_id' => 'nullable|integer|exists:stores,id',
        ]);

        if (! $this->settingService->canManage()) {
            return back()->with('error', 'No store is associated with your account, so settings cannot be saved.');
        }

        // Fetch settings directly from DB
        $settings = Setting::all()->keyBy('key');
        $data = $request->except('_token', '_method', 'store_id');
        $updateData = [];

        // Handle "remove_<key>" checkboxes first (e.g. remove_site_logo).
        // These are sent as separate form fields when the user clicks "Remove".
        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'remove_')) {
                $settingKey = substr($key, strlen('remove_'));
                if (isset($settings[$settingKey])) {
                    $updateData[$settingKey] = null;
                }
            }
        }

        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'remove_')) {
                continue;
            }

            if (isset($settings[$key])) {
                // Treat the legacy '#' placeholder as an empty / nullable value
                if ($value === '#') {
                    $value = '';
                }
                $type = $settings[$key]->type;
                $typeRules = SettingService::TYPES;
                $rules = [$key => $typeRules[$type]['validation'] ?? 'nullable|string'];

                // For image type: handle replace or keep existing.
                // (Removal is handled above via remove_<key> checkbox.)
                if ($type === 'image') {
                    if (! $request->hasFile($key)) {
                        continue; // No new file selected, keep the existing image
                    }

                    $validator = Validator::make($request->only($key), $rules);
                    if ($validator->fails()) {
                        return back()->withErrors($validator)->withInput();
                    }

                    $updateData[$key] = $this->settingService->uploadImage($request->file($key));

                    continue;
                }

                $validator = Validator::make([$key => $value], $rules);
                if ($validator->fails()) {
                    return back()->withErrors($validator)->withInput();
                }

                $updateData[$key] = $value;
            }
        }

        if (! empty($updateData)) {
            $this->settingService->updateBulk($updateData, $this->requestedStoreId());
        }

        // Redirect back to the originating settings page (marketing or general),
        // preserving the selected store context if any.
        $redirectParams = $this->requestedStoreId() ? ['store_id' => $this->requestedStoreId()] : [];
        $referer = $request->headers->get('referer', '');
        if (str_contains($referer, 'marketing/gtm')) {
            return redirect()->route('frontend.marketing.gtm.index', $redirectParams)
                ->with('success', 'GTM settings updated successfully!');
        }

        return redirect()->route('frontend.site-settings.index', $redirectParams)
            ->with('success', 'Site settings updated successfully!');
    }

    public function marketingGtm()
    {
        $grouped = $this->settingService->getGrouped($this->requestedStoreId());
        $items = $grouped['marketing'] ?? [];

        return view('frontend::marketing.gtm', compact('items'));
    }

    public function seed(Request $request): RedirectResponse
    {
        if (! $this->settingService->canManage()) {
            return back()->with('error', 'No store is associated with your account, so settings cannot be created.');
        }

        $this->settingService->seedDefaults($this->requestedStoreId());

        $redirectParams = $this->requestedStoreId() ? ['store_id' => $this->requestedStoreId()] : [];

        return redirect()->route('frontend.site-settings.index', $redirectParams)
            ->with('success', 'Default settings have been created!');
    }
}
