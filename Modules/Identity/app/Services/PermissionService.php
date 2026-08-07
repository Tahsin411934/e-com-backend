<?php

namespace Modules\Identity\Services;

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
                    'id' => $permission->id,
                    'edit' => 'permissionEdit',
                    'delete' => 'permissionDelete',
                ])->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function savePermission(array $data): array
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

                return [
                    'status' => 'success',
                    'message' => $message,
                    'permission' => $permission->fresh(),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error saving permission: ' . $e->getMessage(),
            ];
        }
    }

    public function getPermissionById(int $id): array
    {
        try {
            $permission = Permission::findOrFail($id);
            return [
                'status' => 'success',
                'permission' => $permission,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Permission not found.',
            ];
        }
    }

    public function deletePermission(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $permission = Permission::findOrFail($id);
                $permission->roles()->detach();
                $permission->delete();

                return [
                    'status' => 'success',
                    'message' => 'Permission deleted successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error deleting permission: ' . $e->getMessage(),
            ];
        }
    }

    public function getAllPermissions()
    {
        return Permission::orderBy('name')->get();
    }
}