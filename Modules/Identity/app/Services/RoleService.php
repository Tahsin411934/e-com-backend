<?php

namespace Modules\Identity\Services;

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
                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">' . $role->permissions_count . ' permissions</span>';
            })
            ->addColumn('action', function (Role $role) {
                return view('components.action-buttons', [
                    'id' => $role->id,
                    'edit' => 'roleEdit',
                    'delete' => 'roleDelete',
                ])->render();
            })
            ->rawColumns(['action', 'permissions_count'])
            ->make(true);
    }

    public function saveRole(array $data): array
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

                return [
                    'status' => 'success',
                    'message' => $message,
                    'role' => $role->fresh()->load('permissions'),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error saving role: ' . $e->getMessage(),
            ];
        }
    }

    public function getRoleById(int $id): array
    {
        try {
            $role = Role::with('permissions')->findOrFail($id);
            return [
                'status' => 'success',
                'role' => $role,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Role not found.',
            ];
        }
    }

    public function deleteRole(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $role = Role::findOrFail($id);
                $role->permissions()->detach();
                $role->users()->detach();
                $role->delete();

                return [
                    'status' => 'success',
                    'message' => 'Role deleted successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error deleting role: ' . $e->getMessage(),
            ];
        }
    }

    public function getAllRoles()
    {
        return Role::orderBy('name')->get();
    }
}