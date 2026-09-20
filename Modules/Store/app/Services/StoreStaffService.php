<?php

namespace Modules\Store\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Identity\Models\Role;
use Modules\Identity\Models\User;
use Modules\Store\Support\CurrentStore;
use Modules\Store\Models\StoreStaff;
use Yajra\DataTables\DataTables;

class StoreStaffService
{
    public function getStoreStaffDataTable(Request $request)
    {
        try {
            $query = StoreStaff::query()->with(['store', 'user', 'roles'])->orderByDesc('created_at');
            if (($storeId = CurrentStore::id()) !== null) {
                $query->where('store_id', $storeId);
            }

            return DataTables::of($query)
                ->addColumn('store_name', function (StoreStaff $staff) {
                    return $staff->store?->name ?? '-';
                })
                ->addColumn('user_name', function (StoreStaff $staff) {
                    $firstName = $staff->user?->first_name ?? '';
                    $lastName = $staff->user?->last_name ?? '';
                    $name = trim($firstName.' '.$lastName);

                    return $name !== '' ? $name : '-';
                })
                ->editColumn('status', function (StoreStaff $staff) {
                    return ucfirst((string) $staff->status);
                })
                ->editColumn('hired_at', function (StoreStaff $staff) {
                    return $staff->hired_at ? $staff->hired_at->format('d M Y') : '-';
                })
                ->editColumn('created_at', function (StoreStaff $staff) {
                    return $staff->created_at ? $staff->created_at->format('d M Y H:i') : '-';
                })
                ->addColumn('action', function (StoreStaff $staff) {
                    return view('components.action-buttons', [
                'permission' => 'store-staff',
                'entityLabel' => 'Store Staff',
                        'id' => $staff->id,
                        'edit' => 'storeStaffEdit',
                        'delete' => 'storeStaffDelete',
                    ])->render();
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (\Throwable $e) {
            Log::error('Store staff datatable error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'draw' => (int) ($request->input('draw', 0)),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Unable to load store staff data.',
            ], 500);
        }
    }

    public function saveStoreStaff(array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($data) {
                $staffId = $data['staff_id'] ?? null;
                unset($data['staff_id']);
                $roleIds = $data['role_ids'] ?? null;
                unset($data['role_ids']);

                if (empty($data['user_id'])) {
                    $newUser = User::create([
                        'public_id' => (string) Str::uuid(),
                        'first_name' => $data['first_name'],
                        'last_name' => $data['last_name'],
                        'email' => $data['email'],
                        'password_hash' => Hash::make($data['password']),
                        'status' => 'active',
                    ]);
                    $data['user_id'] = $newUser->id;
                }
                unset($data['first_name'], $data['last_name'], $data['email'], $data['password']);

                $storeId = CurrentStore::id() ?? ($data['store_id'] ?? null);
                if (! $storeId) {
                    throw ValidationException::withMessages([
                        'store_id' => ['A store is required for staff assignment.'],
                    ]);
                }
                $data['store_id'] = $storeId;

                $duplicate = StoreStaff::where('store_id', $storeId)
                    ->where('user_id', $data['user_id'])
                    ->when($staffId, fn ($q) => $q->where('id', '!=', $staffId))
                    ->exists();
                if ($duplicate) {
                    throw ValidationException::withMessages([
                        'user_id' => ['This user is already assigned to this store.'],
                    ]);
                }

                $allowedRoles = Role::where('scope', 'store')
                    ->where('store_id', $storeId)
                    ->pluck('id')
                    ->all();
                if ($roleIds !== null && array_diff($roleIds, $allowedRoles)) {
                    throw ValidationException::withMessages([
                        'role_ids' => ['Only store-scoped roles can be assigned to store staff.'],
                    ]);
                }

                if ($staffId) {
                    $staff = StoreStaff::where('store_id', $storeId)->findOrFail($staffId);
                    $staff->update($data);
                    $message = 'Staff updated successfully.';
                } else {
                    $staff = StoreStaff::create($data);
                    $message = 'Staff created successfully.';
                }

                if ($roleIds !== null) {
                    $staff->roles()->sync($roleIds);
                } elseif (! $staffId) {
                    $defaultRole = Role::where('scope', 'store')
                        ->where('store_id', $storeId)
                        ->where('name', 'Store Staff')
                        ->first();
                    if ($defaultRole) {
                        $staff->roles()->sync([$defaultRole->id]);
                    }
                }

                if ($staff->status !== 'active') {
                    $staff->user?->tokens()->delete();
                }

                return ApiResponse::success($staff->fresh(['store', 'user', 'roles']), $message);
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving staff: '.$e->getMessage(), 500);
        }
    }

    public function getStoreStaffById(int $id): JsonResponse
    {
        try {
            $staff = StoreStaff::with(['store', 'user', 'roles'])
                ->when(CurrentStore::id() !== null, fn ($q) => $q->where('store_id', CurrentStore::id()))
                ->findOrFail($id);

            return ApiResponse::success($staff);
        } catch (\Exception $e) {
            return ApiResponse::notFound('Staff not found.');
        }
    }

    public function deleteStoreStaff(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $staff = StoreStaff::when(CurrentStore::id() !== null, fn ($q) => $q->where('store_id', CurrentStore::id()))->findOrFail($id);
                $staff->user?->tokens()->delete();
                $staff->delete();

                return ApiResponse::success(null, 'Staff deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting staff: '.$e->getMessage(), 500);
        }
    }
}
