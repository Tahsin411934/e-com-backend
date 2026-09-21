<?php

namespace Modules\Frontend\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Storefront\Services\StorefrontSettingService;

class SettingsApiController extends Controller
{
    public function __construct(
        private readonly StorefrontSettingService $settingService
    ) {}

    public function index(): JsonResponse
    {
        $flat = $this->settingService->flat();

        return ApiResponse::success($flat, 'Settings retrieved successfully.');
    }
}
