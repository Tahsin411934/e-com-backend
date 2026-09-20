<?php

namespace Modules\Store\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Identity\Models\Role;
use Modules\Identity\Models\Permission;
use Modules\Store\Support\CurrentStore;
use Yajra\DataTables\DataTables;

class StoreRoleService
{
    private function query()
    {
        return Role::query()->where('scope', 'store')->where('store_id', CurrentStore::id());
    }

    public function dataTable(Request $request)
    {
        return DataTables::of($this->query()->withCount('permissions')->orderBy('name'))
            ->editColumn('permissions_count', fn (Role $role) => $role->permissions_count.' permissions')
            ->addColumn('action', fn (Role $role) => view('components.action-buttons', [
                'permission' => 'store-roles',
                'entityLabel' => 'Store Role',
                'id' => $role->id,
                'edit' => 'storeRoleEdit',
                'delete' => 'storeRoleDelete',
            ])->render())
            ->rawColumns(['action'])
            ->make(true);
    }

    public function save(array $data): JsonResponse
    {
        $storeId = CurrentStore::id();
        if (! $storeId) {
            throw ValidationException::withMessages(['store_id' => ['A store context is required.']]);
        }

        return DB::transaction(function () use ($data, $storeId) {
            $roleId = $data['role_id'] ?? null;
            $permissions = $data['permissions'] ?? [];
            unset($data['role_id'], $data['permissions']);

            $blockedPrefixes = ['users.', 'roles.', 'permissions.', 'store-roles.'];
            $requestedPermissions = Permission::whereIn('id', $permissions)->pluck('name');
            $hasBlockedPermission = $requestedPermissions->contains(function ($name) use ($blockedPrefixes) {
                foreach ($blockedPrefixes as $prefix) {
                    if (str_starts_with($name, $prefix)) {
                        return true;
                    }
                }

                return false;
            });
            if ($hasBlockedPermission) {
                throw ValidationException::withMessages([
                    'permissions' => ['Platform identity and role-management permissions cannot be assigned to store roles.'],
                ]);
            }

            $duplicate = $this->query()->where('name', $data['name'])
                ->when($roleId, fn ($q) => $q->where('id', '!=', $roleId))
                ->exists();
            if ($duplicate) {
                throw ValidationException::withMessages(['name' => ['This role name already exists in this store.']]);
            }

            $role = $roleId
                ? $this->query()->findOrFail($roleId)
                : Role::create(['name' => $data['name'], 'description' => $data['description'] ?? null, 'scope' => 'store', 'store_id' => $storeId]);

            if ($roleId) {
                $role->update(['name' => $data['name'], 'description' => $data['description'] ?? null]);
            }
            $role->permissions()->sync($permissions);

            return ApiResponse::success($role->fresh('permissions'), 'Store role saved successfully.');
        });
    }

    public function show(int $id): JsonResponse
    {
        return ApiResponse::success($this->query()->with('permissions')->findOrFail($id));
    }

    public function delete(int $id): JsonResponse
    {
        return DB::transaction(function () use ($id) {
            $role = $this->query()->findOrFail($id);

            if ($role->storeStaff()->exists()) {
                throw ValidationException::withMessages([
                    'role_id' => ['This role is assigned to staff and cannot be deleted. Reassign staff first.'],
                ]);
            }

            $role->permissions()->detach();
            $role->delete();

            return ApiResponse::success(null, 'Store role deleted successfully.');
        });
    }
}
