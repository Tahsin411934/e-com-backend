<?php

namespace Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Identity\Http\Requests\PermissionRequest;
use Modules\Identity\Services\PermissionService;

class PermissionController extends Controller
{
    public function __construct(private PermissionService $permissionService) {}

    public function index()
    {
        return view('identity::permissions.index');
    }

    public function dataTable(Request $request)
    {
        return $this->permissionService->getPermissionDataTable($request);
    }

    public function store(PermissionRequest $request)
    {
        return $this->permissionService->savePermission($request->validated());
    }

    public function show($id)
    {
        return $this->permissionService->getPermissionById((int) $id);
    }

    public function update(PermissionRequest $request, $id)
    {
        return $this->permissionService->savePermission($request->validated() + ['permission_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->permissionService->deletePermission((int) $id);
    }
}
