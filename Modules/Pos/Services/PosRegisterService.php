<?php

namespace Modules\Pos\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Pos\Models\PosRegister;
use Yajra\DataTables\DataTables;

class PosRegisterService
{
    public function getRegisterDataTable(Request $request)
    {
        $query = PosRegister::query()->with('store')->orderByDesc('created_at');

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

                if ($registerId) {
                    $register = PosRegister::findOrFail($registerId);
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
            $register = PosRegister::with('store')->findOrFail($id);

            return ApiResponse::success($register);
        } catch (\Exception $e) {
            return ApiResponse::notFound('Register not found.');
        }
    }

    public function deleteRegister(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $register = PosRegister::findOrFail($id);
                $register->delete();

                return ApiResponse::success(null, 'Register deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting register: '.$e->getMessage(), 500);
        }
    }

    public function getAllActiveRegisters(): JsonResponse
    {
        return PosRegister::where('status', 'active')
            ->with('store')
            ->orderBy('name')
            ->get()
            ->toArray();
    }
}
