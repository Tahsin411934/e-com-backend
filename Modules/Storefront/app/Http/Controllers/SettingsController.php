<?php

namespace Modules\Storefront\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Storefront\Services\StorefrontSettingService;

/**
 * Storefront settings API.
 *
 * Identical response shape to the legacy /api/v1/settings endpoint, but the
 * store's own setting rows win over the global platform values per key.
 */
class SettingsController extends Controller
{
    public function __construct(
        private readonly StorefrontSettingService $settingService
    ) {}

    public function index(): JsonResponse
    {
        return ApiResponse::success(
            $this->settingService->flat(),
            'Settings retrieved successfully.'
        );
    }
}
