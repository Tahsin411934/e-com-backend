<?php

namespace Modules\Pos\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Pos\Models\PosShift;
use Yajra\DataTables\DataTables;

class PosShiftService
{
    public function getShiftDataTable(Request $request)
    {
        $query = PosShift::query()->with(['register.store', 'user'])->orderByDesc('created_at');

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

                if ($shiftId) {
                    $shift = PosShift::findOrFail($shiftId);
                    $shift->update($data);
                    $message = 'Shift updated successfully.';
                } else {
                    if (! isset($data['opened_at'])) {
                        $data['opened_at'] = now();
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
            $shift = PosShift::with(['register.store', 'user'])->findOrFail($id);

            return ApiResponse::success($shift);
        } catch (\Exception $e) {
            return ApiResponse::notFound('Shift not found.');
        }
    }

    public function deleteShift(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $shift = PosShift::findOrFail($id);
                $shift->delete();

                return ApiResponse::success(null, 'Shift deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting shift: '.$e->getMessage(), 500);
        }
    }

    public function getOpenShifts(): JsonResponse
    {
        return PosShift::where('status', 'open')
            ->with(['register.store', 'user'])
            ->orderByDesc('opened_at')
            ->get()
            ->toArray();
    }

    public function closeShift(int $id, array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id, $data) {
                $shift = PosShift::findOrFail($id);

                $totalSales = $shift->cash_sales + $shift->card_sales + $shift->other_sales;
                $expectedBalance = $shift->opening_balance + $totalSales;
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
