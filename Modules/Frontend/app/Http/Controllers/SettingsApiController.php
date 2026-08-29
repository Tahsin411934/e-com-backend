<?php

namespace Modules\Frontend\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Frontend\Services\SettingService;

class SettingsApiController extends Controller
{
    public function __construct(
        private readonly SettingService $settingService
    ) {}

    public function index(): JsonResponse
    {
        $settings = $this->settingService->getGrouped();
        $flat = [];
        foreach ($settings as $group => $items) {
            foreach ($items as $item) {
                $value = $item['value'];

                // Convert relative storage paths (e.g. /storage/settings/xxx.png)
                // to full absolute URLs so the frontend can display the image.
                if ($item['type'] === 'image' && $value && ! str_starts_with($value, 'http')) {
                    $value = asset(ltrim($value, '/'));
                }

                $flat[$item['key']] = $value;
            }
        }

        return ApiResponse::success($flat, 'Settings retrieved successfully.');
    }
}
