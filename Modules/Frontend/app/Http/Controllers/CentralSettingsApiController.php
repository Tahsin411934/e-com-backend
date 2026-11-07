<?php

namespace Modules\Frontend\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Frontend\Services\CentralSettingService;

class CentralSettingsApiController extends Controller
{
    public function __construct(
        private readonly CentralSettingService $settingService
    ) {}

    public function index(): JsonResponse
    {
        return ApiResponse::success(
            (object) $this->settingService->flat(),
            'Central settings retrieved successfully.'
        );
    }
}
