<?php

namespace Modules\Pos\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Pos\Models\PosRegister;
use Modules\Pos\Models\PosShift;
use Modules\Store\Support\CurrentStore;
use Yajra\DataTables\DataTables;

class PosRegisterService
{
    public function getRegisterDataTable(Request $request)
    {
        $query = PosRegister::query()->with('store')->orderByDesc('created_at');
        if (($storeId = CurrentStore::id()) !== null) $query->where('store_id', $storeId);

        return DataTables::of($query)
            ->editColumn('status', function (PosRegister $register) {
                return ucfirst($register->status);
            })
            ->editColumn('type', function (PosRegister $register) {
                return ucfirst($register->type);
            })
            ->addColumn('store_name', function (PosRegister $register) {
                return $register->store ? $register->store->name : '-';
            })
            ->editColumn('created_at', function (PosRegister $register) {
                return $register->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (PosRegister $register) {
                return view('components.action-buttons', [
                    'id' => $register->id,
                    'edit' => 'posRegisterEdit',
                    'delete' => 'posRegisterDelete',
                ])->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function saveRegister(array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($data) {
                $registerId = $data['register_id'] ?? null;
                unset($data['register_id']);
                if (($storeId = CurrentStore::id()) !== null) $data['store_id'] = $storeId;

                if ($registerId) {
                    $register = PosRegister::query()->when(CurrentStore::id() !== null, fn ($q) => $q->where('store_id', CurrentStore::id()))->findOrFail($registerId);

                    if (($data['status'] ?? $register->status) !== 'active'
                        && PosShift::where('register_id', $register->id)->where('status', 'open')->exists()) {
                        throw ValidationException::withMessages([
                            'status' => ['This register has an open shift and cannot be deactivated.'],
                        ]);
                    }

                    $register->update($data);
                    $message = 'Register updated successfully.';
                } else {
                    $register = PosRegister::create($data);
                    $message = 'Register created successfully.';
                }

                return ApiResponse::success($register->fresh()->load('store'), $message);
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving register: '.$e->getMessage(), 500);
        }
    }

    public function getRegisterById(int $id): JsonResponse
    {
        try {
            $register = PosRegister::with('store')->when(CurrentStore::id() !== null, fn ($q) => $q->where('store_id', CurrentStore::id()))->findOrFail($id);

            return ApiResponse::success($register);
        } catch (\Exception $e) {
            return ApiResponse::notFound('Register not found.');
        }
    }

    public function deleteRegister(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $register = PosRegister::query()->when(CurrentStore::id() !== null, fn ($q) => $q->where('store_id', CurrentStore::id()))->findOrFail($id);

                if (PosShift::where('register_id', $register->id)->exists()) {
                    throw ValidationException::withMessages([
                        'register_id' => ['This register has shift history and cannot be deleted.'],
                    ]);
                }

                $register->delete();

                return ApiResponse::success(null, 'Register deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting register: '.$e->getMessage(), 500);
        }
    }

    public function getAllActiveRegisters(): array
    {
        return PosRegister::where('status', 'active')->when(CurrentStore::id() !== null, fn ($q) => $q->where('store_id', CurrentStore::id()))
            ->with('store')
            ->orderBy('name')
            ->get()
            ->toArray();
    }
}
