<?php

namespace Modules\Store\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Identity\Services\PermissionService;
use Modules\Store\Http\Requests\StoreRoleRequest;
use Modules\Store\Services\StoreRoleService;

class StoreRoleController extends Controller
{
    public function __construct(private StoreRoleService $service, private PermissionService $permissionService) {}

    public function index()
    {
        $permissions = $this->permissionService->getAllPermissions();
        $permissions = $permissions->reject(function ($permission) {
            foreach (['users.', 'roles.', 'permissions.', 'store-roles.'] as $prefix) {
                if (str_starts_with($permission->name, $prefix)) {
                    return true;
                }
            }

            return false;
        });
        $groupedPermissions = $permissions->groupBy(fn ($permission) => ucfirst(explode('.', $permission->name)[0]));

        return view('store::store-roles.index', compact('groupedPermissions'));
    }

    public function dataTable(Request $request) { return $this->service->dataTable($request); }
    public function store(StoreRoleRequest $request) { return $this->service->save($request->validated()); }
    public function show($id) { return $this->service->show((int) $id); }
    public function update(StoreRoleRequest $request, $id) { return $this->service->save($request->validated() + ['role_id' => $id]); }
    public function destroy($id) { return $this->service->delete((int) $id); }
}
