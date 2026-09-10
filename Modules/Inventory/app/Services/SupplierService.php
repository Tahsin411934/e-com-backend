<?php

namespace Modules\Inventory\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Inventory\Models\Supplier;
use Modules\Store\Support\CurrentStore;
use Yajra\DataTables\DataTables;

class SupplierService
{
    public function getSupplierDataTable(Request $request)
    {
        $query = Supplier::forCurrentStore()->with('store')->orderByDesc('created_at');

        if ($request->store_id) {
            $query->where('store_id', $request->store_id);
        }

        return DataTables::of($query)
            ->addColumn('store_name', function (Supplier $supplier) {
                return $supplier->store?->name ?? 'Platform';
            })
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

                // Store owners/staff are locked to their own store.
                if (! $this->isPlatformAdmin()) {
                    $data['store_id'] = CurrentStore::id();
                }

                if (empty($data['slug'])) {
                    $data['slug'] = Str::slug($data['name']);
                }

                if ($supplierId) {
                    $supplier = Supplier::forCurrentStore()->findOrFail($supplierId);
                    $supplier->update($data);
                    $message = 'Supplier updated successfully.';
                } else {
                    $supplier = Supplier::create($data);
                    $message = 'Supplier created successfully.';
                }

                return ApiResponse::success($supplier->fresh()->load('store'), $message);
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving supplier: '.$e->getMessage(), 500);
        }
    }

    public function getSupplierById(int $id): JsonResponse
    {
        try {
            $supplier = Supplier::forCurrentStore()->with('store')->findOrFail($id);

            return ApiResponse::success($supplier);
        } catch (\Exception $e) {
            return ApiResponse::notFound('Supplier not found.');
        }
    }

    public function deleteSupplier(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $supplier = Supplier::forCurrentStore()->findOrFail($id);
                $supplier->delete();

                return ApiResponse::success(null, 'Supplier deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting supplier: '.$e->getMessage(), 500);
        }
    }

    public function getAllActiveSuppliers(): Collection
    {
        return Supplier::forCurrentStore()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    private function isPlatformAdmin(): bool
    {
        $actor = auth()->user();

        return (bool) ($actor && ($actor->hasRole('Super Admin') || $actor->hasRole('Admin')));
    }
}
