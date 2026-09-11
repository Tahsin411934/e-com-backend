<?php

namespace Modules\Identity\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Identity\Models\Role;
use Yajra\DataTables\DataTables;

class RoleService
{
    public function getRoleDataTable(Request $request)
    {
        $query = Role::withCount('permissions')->orderByDesc('created_at');

        return DataTables::of($query)
            ->editColumn('created_at', function (Role $role) {
                return $role->created_at->format('d M Y H:i');
            })
            ->editColumn('permissions_count', function (Role $role) {
                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">'.$role->permissions_count.' permissions</span>';
            })
            ->addColumn('action', function (Role $role) {
                return view('components.action-buttons', [
                'permission' => 'roles',
                'entityLabel' => 'Role',
                    'id' => $role->id,
                    'edit' => 'roleEdit',
                    'delete' => 'roleDelete',
                ])->render();
            })
            ->rawColumns(['action', 'permissions_count'])
            ->make(true);
    }

    public function saveRole(array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($data) {
                $roleId = $data['role_id'] ?? null;

                unset($data['role_id']);

                if ($roleId) {
                    $role = Role::findOrFail($roleId);
                    $role->update($data);
                    $message = 'Role updated successfully.';
                } else {
                    $role = Role::create($data);
                    $message = 'Role created successfully.';
                }

                // Sync permissions if provided
                if (isset($data['permissions']) && is_array($data['permissions'])) {
                    $role->permissions()->sync($data['permissions']);
                }

                return ApiResponse::success($role->fresh()->load('permissions'), $message);
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving role: '.$e->getMessage(), 500);
        }
    }

    public function getRoleById(int $id): JsonResponse
    {
        try {
            $role = Role::with('permissions')->findOrFail($id);

            return ApiResponse::success($role);
        } catch (\Exception $e) {
            return ApiResponse::notFound('Role not found.');
        }
    }

    public function deleteRole(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $role = Role::findOrFail($id);
                $role->permissions()->detach();
                $role->users()->detach();
                $role->delete();

                return ApiResponse::success(null, 'Role deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting role: '.$e->getMessage(), 500);
        }
    }

    public function getAllRoles()
    {
        return Role::orderBy('name')->get();
    }
}
