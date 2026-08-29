<?php

namespace Modules\Store\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Store\Models\AppSetting;
use Yajra\DataTables\DataTables;

class AppSettingService
{
    public function getAppSettingDataTable(Request $request)
    {
        $query = AppSetting::query()->orderByDesc('created_at');

        return DataTables::of($query)
            ->editColumn('scope_type', function (AppSetting $setting) {
                return ucfirst($setting->scope_type);
            })
            ->editColumn('setting_value', function (AppSetting $setting) {
                $val = $setting->setting_value;
                if (is_array($val)) {
                    return substr(json_encode($val), 0, 80).(strlen(json_encode($val)) > 80 ? '...' : '');
                }

                return substr((string) $val, 0, 80);
            })
            ->editColumn('is_public', function (AppSetting $setting) {
                return $setting->is_public ? '<span class="text-green-600 font-medium">Yes</span>' : 'No';
            })
            ->editColumn('created_at', function (AppSetting $setting) {
                return $setting->created_at ? $setting->created_at->format('d M Y H:i') : '';
            })
            ->addColumn('action', function (AppSetting $setting) {
                return view('components.action-buttons', [
                    'id' => $setting->id,
                    'edit' => 'appSettingEdit',
                    'delete' => 'appSettingDelete',
                ])->render();
            })
            ->rawColumns(['is_public', 'action'])
            ->make(true);
    }

    public function saveAppSetting(array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($data) {
                $settingId = $data['setting_id'] ?? null;

                if (isset($data['setting_value']) && is_string($data['setting_value'])) {
                    $decoded = json_decode($data['setting_value'], true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $data['setting_value'] = $decoded;
                    }
                }

                unset($data['setting_id']);

                if ($settingId) {
                    $setting = AppSetting::findOrFail($settingId);
                    $setting->update($data);
                    $message = 'Setting updated successfully.';
                } else {
                    $setting = AppSetting::create($data);
                    $message = 'Setting created successfully.';
                }

                return ApiResponse::success($setting->fresh(), $message);
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving setting: '.$e->getMessage(), 500);
        }
    }

    public function getAppSettingById(int $id): JsonResponse
    {
        try {
            $setting = AppSetting::findOrFail($id);

            return ApiResponse::success($setting);
        } catch (\Exception $e) {
            return ApiResponse::notFound('Setting not found.');
        }
    }

    public function deleteAppSetting(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $setting = AppSetting::findOrFail($id);
                $setting->delete();

                return ApiResponse::success(null, 'Setting deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting setting: '.$e->getMessage(), 500);
        }
    }
}
