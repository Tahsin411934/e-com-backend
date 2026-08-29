<?php

namespace Modules\Inventory\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Inventory\Models\Supplier;
use Yajra\DataTables\DataTables;

class SupplierService
{
    public function getSupplierDataTable(Request $request)
    {
        $query = Supplier::query()->orderByDesc('created_at');

        return DataTables::of($query)
            ->editColumn('status', function (Supplier $supplier) {
                return ucfirst($supplier->status);
            })
            ->editColumn('created_at', function (Supplier $supplier) {
                return $supplier->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (Supplier $supplier) {
                return view('components.action-buttons', [
                    'id' => $supplier->id,
                    'edit' => 'supplierEdit',
                    'delete' => 'supplierDelete',
                ])->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function saveSupplier(array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($data) {
                $supplierId = $data['supplier_id'] ?? null;
                unset($data['supplier_id']);

                if (empty($data['slug'])) {
                    $data['slug'] = Str::slug($data['name']);
                }

                if ($supplierId) {
                    $supplier = Supplier::findOrFail($supplierId);
                    $supplier->update($data);
                    $message = 'Supplier updated successfully.';
                } else {
                    $supplier = Supplier::create($data);
                    $message = 'Supplier created successfully.';
                }

                return ApiResponse::success($supplier->fresh(), $message);
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving supplier: '.$e->getMessage(), 500);
        }
    }

    public function getSupplierById(int $id): JsonResponse
    {
        try {
            $supplier = Supplier::findOrFail($id);

            return ApiResponse::success($supplier);
        } catch (\Exception $e) {
            return ApiResponse::notFound('Supplier not found.');
        }
    }

    public function deleteSupplier(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $supplier = Supplier::findOrFail($id);
                $supplier->delete();

                return ApiResponse::success(null, 'Supplier deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting supplier: '.$e->getMessage(), 500);
        }
    }

    public function getAllActiveSuppliers(): Collection
    {
        return Supplier::where('status', 'active')
            ->orderBy('name')
            ->get();
    }
}
