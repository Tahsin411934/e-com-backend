<?php

namespace Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Identity\Http\Requests\UserRequest;
use Modules\Identity\Services\RoleService;
use Modules\Identity\Services\UserService;

class UserController extends Controller
{
    public function __construct(private UserService $userService, private RoleService $roleService) {}

    public function index()
    {
        $roles = $this->roleService->getAllRoles();

        return view('identity::users.index', compact('roles'));
    }

    public function dataTable(Request $request)
    {
        return $this->userService->getUserDataTable($request);
    }

    public function store(UserRequest $request)
    {
        return $this->userService->saveUser($request->validated());
    }

    public function show($id)
    {
        return $this->userService->getUserById((int) $id);
    }

    public function update(UserRequest $request, $id)
    {
        return $this->userService->saveUser($request->validated() + ['user_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->userService->deleteUser((int) $id);
    }
}
