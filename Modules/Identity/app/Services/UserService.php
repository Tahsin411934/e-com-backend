<?php

namespace Modules\Identity\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Identity\Models\User;
use Yajra\DataTables\DataTables;

class UserService
{
    public function getUserDataTable(Request $request)
    {
        $query = User::with('roles')->orderByDesc('created_at');

        return DataTables::of($query)
            ->editColumn('status', function (User $user) {
                return ucfirst($user->status);
            })
            ->editColumn('created_at', function (User $user) {
                return $user->created_at->format('d M Y H:i');
            })
            ->addColumn('full_name', function (User $user) {
                return trim($user->first_name . ' ' . $user->last_name);
            })
            ->addColumn('role_name', function (User $user) {
                return $user->roles->pluck('name')->implode(', ') ?? 'No Role';
            })
            ->addColumn('action', function (User $user) {
                return view('components.action-buttons', [
                    'id' => $user->id,
                    'edit' => 'userEdit',
                    'delete' => 'userDelete',
                ])->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function saveUser(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                $userId = $data['user_id'] ?? null;
                $roleId = $data['role_id'] ?? null;

                unset($data['user_id'], $data['role_id']);

                if (!isset($data['public_id'])) {
                    $data['public_id'] = (string) Str::uuid();
                }

                if ($userId) {
                    $user = User::findOrFail($userId);
                    // Only update password if provided
                    if (empty($data['password_hash'])) {
                        unset($data['password_hash']);
                    }
                    $user->update($data);
                    $message = 'User updated successfully.';
                } else {
                    $user = User::create($data);
                    $message = 'User created successfully.';
                }

                // Sync role
                if ($roleId) {
                    $user->roles()->sync([$roleId]);
                }

                return [
                    'status' => 'success',
                    'message' => $message,
                    'user' => $user->fresh()->load('roles'),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error saving user: ' . $e->getMessage(),
            ];
        }
    }

    public function getUserById(int $id): array
    {
        try {
            $user = User::with('roles')->findOrFail($id);
            return [
                'status' => 'success',
                'user' => $user,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'User not found.',
            ];
        }
    }

    public function deleteUser(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $user = User::findOrFail($id);
                $user->roles()->detach();
                $user->delete();

                return [
                    'status' => 'success',
                    'message' => 'User deleted successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error deleting user: ' . $e->getMessage(),
            ];
        }
    }

    public function getAllActiveUsers(): array
    {
        return User::where('status', 'active')->orderBy('first_name')->get()->toArray();
    }
}