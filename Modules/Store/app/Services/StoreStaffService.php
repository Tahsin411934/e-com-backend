<?php

namespace Modules\Store\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Store\Models\StoreStaff;
use Yajra\DataTables\DataTables;

class StoreStaffService
{
    public function getStoreStaffDataTable(Request $request)
    {
        try {
            $query = StoreStaff::query()->with(['store', 'user'])->orderByDesc('created_at');

            return DataTables::of($query)
                ->addColumn('store_name', function (StoreStaff $staff) {
                    return $staff->store?->name ?? '-';
                })
                ->addColumn('user_name', function (StoreStaff $staff) {
                    $firstName = $staff->user?->first_name ?? '';
                    $lastName = $staff->user?->last_name ?? '';
                    $name = trim($firstName.' '.$lastName);

                    return $name !== '' ? $name : '-';
                })
                ->editColumn('status', function (StoreStaff $staff) {
                    return ucfirst((string) $staff->status);
                })
                ->editColumn('hired_at', function (StoreStaff $staff) {
                    return $staff->hired_at ? $staff->hired_at->format('d M Y') : '-';
                })
                ->editColumn('created_at', function (StoreStaff $staff) {
                    return $staff->created_at ? $staff->created_at->format('d M Y H:i') : '-';
                })
                ->addColumn('action', function (StoreStaff $staff) {
                    return view('components.action-buttons', [
                        'id' => $staff->id,
                        'edit' => 'storeStaffEdit',
                        'delete' => 'storeStaffDelete',
                    ])->render();
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (\Throwable $e) {
            Log::error('Store staff datatable error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'draw' => (int) ($request->input('draw', 0)),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Unable to load store staff data.',
            ], 500);
        }
    }

    public function saveStoreStaff(array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($data) {
                $staffId = $data['staff_id'] ?? null;
                unset($data['staff_id']);

                if ($staffId) {
                    $staff = StoreStaff::findOrFail($staffId);
                    $staff->update($data);
                    $message = 'Staff updated successfully.';
                } else {
                    $staff = StoreStaff::create($data);
                    $message = 'Staff created successfully.';
                }

                return ApiResponse::success($staff->fresh(['store', 'user']), $message);
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving staff: '.$e->getMessage(), 500);
        }
    }

    public function getStoreStaffById(int $id): JsonResponse
    {
        try {
            $staff = StoreStaff::with(['store', 'user'])->findOrFail($id);

            return ApiResponse::success($staff);
        } catch (\Exception $e) {
            return ApiResponse::notFound('Staff not found.');
        }
    }

    public function deleteStoreStaff(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $staff = StoreStaff::findOrFail($id);
                $staff->delete();

                return ApiResponse::success(null, 'Staff deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting staff: '.$e->getMessage(), 500);
        }
    }
}
