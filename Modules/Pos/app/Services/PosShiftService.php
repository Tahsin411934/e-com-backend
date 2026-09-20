<?php

namespace Modules\Pos\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Pos\Models\PosShift;
use Modules\Pos\Models\PosRegister;
use Modules\Pos\Models\PosSale;
use Modules\Store\Support\CurrentStore;
use Yajra\DataTables\DataTables;

class PosShiftService
{
    public function getShiftDataTable(Request $request)
    {
        $query = PosShift::query()->with(['register.store', 'user'])->orderByDesc('created_at');
        if (($storeId = CurrentStore::id()) !== null) $query->whereHas('register', fn ($q) => $q->where('store_id', $storeId));

        return DataTables::of($query)
            ->editColumn('status', function (PosShift $shift) {
                return ucfirst($shift->status);
            })
            ->addColumn('register_name', function (PosShift $shift) {
                return $shift->register ? $shift->register->name : '-';
            })
            ->addColumn('store_name', function (PosShift $shift) {
                return $shift->register && $shift->register->store ? $shift->register->store->name : '-';
            })
            ->addColumn('user_name', function (PosShift $shift) {
                return $shift->user ? $shift->user->name : '-';
            })
            ->editColumn('opened_at', function (PosShift $shift) {
                return $shift->opened_at ? $shift->opened_at->format('d M Y H:i') : '-';
            })
            ->editColumn('closed_at', function (PosShift $shift) {
                return $shift->closed_at ? $shift->closed_at->format('d M Y H:i') : '-';
            })
            ->editColumn('total_sales', function (PosShift $shift) {
                return number_format($shift->total_sales, 2);
            })
            ->editColumn('opening_balance', function (PosShift $shift) {
                return number_format($shift->opening_balance, 2);
            })
            ->addColumn('action', function (PosShift $shift) {
                return view('components.action-buttons', [
                    'id' => $shift->id,
                    'edit' => 'posShiftEdit',
                    'delete' => 'posShiftDelete',
                ])->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function saveShift(array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($data) {
                $shiftId = $data['shift_id'] ?? null;
                unset($data['shift_id']);

                if (! empty($data['register_id'])) {
                    $registerQuery = PosRegister::query()->whereKey($data['register_id']);
                    if (($storeId = CurrentStore::id()) !== null) {
                        $registerQuery->where('store_id', $storeId);
                    }

                    $register = $registerQuery->first();
                    if (! $register) {
                        throw ValidationException::withMessages([
                            'register_id' => ['The selected register does not belong to the current store.'],
                        ]);
                    }

                    if ($register->status !== 'active') {
                        throw ValidationException::withMessages([
                            'register_id' => ['A shift can only be opened on an active register.'],
                        ]);
                    }
                }

                if ($shiftId) {
                    $shift = PosShift::query()->when(CurrentStore::id() !== null, fn ($q) => $q->whereHas('register', fn ($r) => $r->where('store_id', CurrentStore::id())))->findOrFail($shiftId);

                    if ($shift->status === 'closed') {
                        throw ValidationException::withMessages([
                            'shift_id' => ['A closed shift cannot be edited.'],
                        ]);
                    }

                    $shift->update($data);
                    $message = 'Shift updated successfully.';
                } else {
                    if (! isset($data['opened_at'])) {
                        $data['opened_at'] = now();
                    }

                    $openShiftQuery = PosShift::query()
                        ->where('register_id', $data['register_id'])
                        ->where('status', 'open');

                    if ($openShiftQuery->exists()) {
                        throw ValidationException::withMessages([
                            'register_id' => ['This register already has an open shift.'],
                        ]);
                    }

                    $shift = PosShift::create($data);
                    $message = 'Shift created successfully.';
                }

                return ApiResponse::success($shift->fresh()->load(['register.store', 'user']), $message);
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving shift: '.$e->getMessage(), 500);
        }
    }

    public function getShiftById(int $id): JsonResponse
    {
        try {
            $shift = PosShift::with(['register.store', 'user'])->when(CurrentStore::id() !== null, fn ($q) => $q->whereHas('register', fn ($r) => $r->where('store_id', CurrentStore::id())))->findOrFail($id);

            return ApiResponse::success($shift);
        } catch (\Exception $e) {
            return ApiResponse::notFound('Shift not found.');
        }
    }

    public function deleteShift(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $shift = PosShift::query()->when(CurrentStore::id() !== null, fn ($q) => $q->whereHas('register', fn ($r) => $r->where('store_id', CurrentStore::id())))->findOrFail($id);

                if (PosSale::where('shift_id', $shift->id)->exists()) {
                    throw ValidationException::withMessages([
                        'shift_id' => ['This shift has sales and cannot be deleted.'],
                    ]);
                }

                $shift->delete();

                return ApiResponse::success(null, 'Shift deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting shift: '.$e->getMessage(), 500);
        }
    }

    public function getOpenShifts(): array
    {
        return PosShift::where('status', 'open')->when(CurrentStore::id() !== null, fn ($q) => $q->whereHas('register', fn ($r) => $r->where('store_id', CurrentStore::id())))
            ->with(['register.store', 'user'])
            ->orderByDesc('opened_at')
            ->get()
            ->toArray();
    }

    public function closeShift(int $id, array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id, $data) {
                $shift = PosShift::query()->when(CurrentStore::id() !== null, fn ($q) => $q->whereHas('register', fn ($r) => $r->where('store_id', CurrentStore::id())))->findOrFail($id);

                if ($shift->status !== 'open') {
                    throw ValidationException::withMessages([
                        'shift_id' => ['Only an open shift can be closed.'],
                    ]);
                }

                $expectedBalance = $shift->opening_balance + $shift->cash_sales;
                $discrepancy = ($data['declared_cash'] ?? 0) - $expectedBalance;

                $shift->update([
                    'closed_at' => now(),
                    'closing_balance' => $data['declared_cash'] ?? 0,
                    'expected_balance' => $expectedBalance,
                    'declared_cash' => $data['declared_cash'] ?? 0,
                    'discrepancy' => $discrepancy,
                    'notes' => $data['notes'] ?? null,
                    'status' => 'closed',
                ]);

                return ApiResponse::success($shift->fresh()->load(['register.store', 'user']), 'Shift closed successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error closing shift: '.$e->getMessage(), 500);
        }
    }
}
