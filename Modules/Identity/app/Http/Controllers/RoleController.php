<?php

namespace Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Identity\Http\Requests\RoleRequest;
use Modules\Identity\Services\PermissionService;
use Modules\Identity\Services\RoleService;

class RoleController extends Controller
{
    public function __construct(private RoleService $roleService, private PermissionService $permissionService) {}

    public function index()
    {
        $permissions = $this->permissionService->getAllPermissions();

        // Group permissions by module (e.g., "users.view" -> "users")
        $groupedPermissions = $permissions->groupBy(function ($permission) {
            $parts = explode('.', $permission->name);

            return ucfirst($parts[0]);
        })->map(function ($group) {
            return $group->sortBy('name')->values();
        })->sortKeys();

        return view('identity::roles.index', compact('groupedPermissions'));
    }

    public function dataTable(Request $request)
    {
        return $this->roleService->getRoleDataTable($request);
    }

    public function store(RoleRequest $request)
    {
        return $this->roleService->saveRole($request->validated());
    }

    public function show($id)
    {
        return $this->roleService->getRoleById((int) $id);
    }

    public function update(RoleRequest $request, $id)
    {
        return $this->roleService->saveRole($request->validated() + ['role_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->roleService->deleteRole((int) $id);
    }
}
