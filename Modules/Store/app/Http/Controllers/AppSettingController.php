<?php

namespace Modules\Store\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Store\Http\Requests\AppSettingRequest;
use Modules\Store\Services\AppSettingService;

class AppSettingController extends Controller
{
    public function __construct(private AppSettingService $appSettingService) {}

    public function index()
    {
        return view('store::app-settings.index');
    }

    public function dataTable(Request $request)
    {
        return $this->appSettingService->getAppSettingDataTable($request);
    }

    public function store(AppSettingRequest $request)
    {
        return $this->appSettingService->saveAppSetting($request->validated());
    }

    public function show($id)
    {
        return $this->appSettingService->getAppSettingById((int) $id);
    }

    public function update(AppSettingRequest $request, $id)
    {
        return $this->appSettingService->saveAppSetting($request->validated() + ['setting_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->appSettingService->deleteAppSetting((int) $id);
    }
}
