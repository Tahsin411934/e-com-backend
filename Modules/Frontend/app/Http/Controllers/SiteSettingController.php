<?php

namespace Modules\Frontend\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\Frontend\Services\SettingService;

class SiteSettingController extends Controller
{
    public function __construct(
        private readonly SettingService $settingService
    ) {}

    public function index()
    {
        $grouped = $this->settingService->getGrouped();
        $groups = ['general', 'social', 'contact', 'seo'];
        return view('frontend::site-settings', compact('grouped', 'groups'));
    }

    public function update(Request $request): RedirectResponse
    {
        // Fetch settings directly from DB
        $settings = \Modules\Frontend\Models\Setting::all()->keyBy('key');
        $data = $request->except('_token', '_method');
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
                $type = $settings[$key]->type;
                $typeRules = SettingService::TYPES;
                $rules = [$key => $typeRules[$type]['validation'] ?? 'nullable|string'];

                // For image type: handle replace or keep existing.
                // (Removal is handled above via remove_<key> checkbox.)
                if ($type === 'image') {
                    if (!$request->hasFile($key)) {
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

        if (!empty($updateData)) {
            $this->settingService->updateBulk($updateData);
        }

        return redirect()->route('frontend.site-settings.index')
            ->with('success', 'Site settings updated successfully!');
    }

    public function seed(): RedirectResponse
    {
        $this->settingService->seedDefaults();
        return redirect()->route('frontend.site-settings.index')
            ->with('success', 'Default settings have been created!');
    }
}