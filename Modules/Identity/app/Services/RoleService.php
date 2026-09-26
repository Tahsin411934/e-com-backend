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
        $query = Role::query()
            ->select('roles.*')
            ->withCount(['permissions' => fn ($permissions) => $permissions->whereNull('permissions.deleted_at')])
            ->orderByDesc('roles.created_at');

        return DataTables::eloquent($query)
            ->filter(function ($query) use ($request) {
                $search = trim((string) data_get($request->all(), 'search.value', ''));
                if ($search === '') {
                    return;
                }

                $like = '%'.addcslashes(mb_strtolower($search), '%_\\').'%';
                $query->where(function ($filter) use ($like) {
                    $filter->whereRaw('LOWER(roles.name) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(COALESCE(roles.description, \'\')) LIKE ?', [$like])
                        ->orWhereHas('permissions', function ($permissions) use ($like) {
                            $permissions->whereNull('permissions.deleted_at')
                                ->whereRaw('LOWER(permissions.name) LIKE ?', [$like]);
                        })
                        ->orWhereHas('permissions', function ($permissions) use ($like) {
                            $permissions->whereNull('permissions.deleted_at')
                                ->whereRaw('LOWER(permissions.description) LIKE ?', [$like]);
                        });
                });
            }, false)
            ->orderColumn('name', 'roles.name $1')
            ->orderColumn('description', 'roles.description $1')
            ->orderColumn('created_at', 'roles.created_at $1')
            ->orderColumn('permissions_count', 'permissions_count $1')
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
