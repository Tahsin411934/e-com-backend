<?php

namespace Modules\Identity\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Identity\Models\Permission;
use Yajra\DataTables\DataTables;

class PermissionService
{
    public function getPermissionDataTable(Request $request)
    {
        $query = Permission::query()->orderByDesc('created_at');

        return DataTables::of($query)
            ->editColumn('created_at', function (Permission $permission) {
                return $permission->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (Permission $permission) {
                return view('components.action-buttons', [
                'permission' => 'permissions',
                'entityLabel' => 'Permission',
                    'id' => $permission->id,
                    'edit' => 'permissionEdit',
                    'delete' => 'permissionDelete',
                ])->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function savePermission(array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($data) {
                $permissionId = $data['permission_id'] ?? null;

                unset($data['permission_id']);

                if ($permissionId) {
                    $permission = Permission::findOrFail($permissionId);
                    $permission->update($data);
                    $message = 'Permission updated successfully.';
                } else {
                    $permission = Permission::create($data);
                    $message = 'Permission created successfully.';
                }

                return ApiResponse::success($permission->fresh(), $message);
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving permission: '.$e->getMessage(), 500);
        }
    }

    public function getPermissionById(int $id): JsonResponse
    {
        try {
            $permission = Permission::findOrFail($id);

            return ApiResponse::success($permission);
        } catch (\Exception $e) {
            return ApiResponse::notFound('Permission not found.');
        }
    }

    public function deletePermission(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $permission = Permission::findOrFail($id);
                $permission->roles()->detach();
                $permission->delete();

                return ApiResponse::success(null, 'Permission deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting permission: '.$e->getMessage(), 500);
        }
    }

    public function getAllPermissions()
    {
        return Permission::orderBy('name')->get();
    }
}
