<?php

namespace Modules\Catalog\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\Models\ProductVariant;
use Yajra\DataTables\DataTables;

class ProductVariantService
{
    public function getVariantDataTable(Request $request)
    {
        $query = ProductVariant::with('product')->orderByDesc('created_at');

        return DataTables::of($query)
            ->addColumn('product', function (ProductVariant $variant) {
                return $variant->product?->name ?: '-';
            })
            ->editColumn('sale_price', function (ProductVariant $variant) {
                return moneyFormat($variant->sale_price);
            })
            ->editColumn('status', function (ProductVariant $variant) {
                return ucfirst($variant->status);
            })
            ->editColumn('created_at', function (ProductVariant $variant) {
                return $variant->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (ProductVariant $variant) {
                return view('components.action-buttons', [
                    'id' => $variant->id,
                    'edit' => 'variantEdit',
                    'delete' => 'variantDelete',
                ])->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function saveVariant(array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($data) {
                $variantId = $data['variant_id'] ?? null;
                $data['track_inventory'] = $data['track_inventory'] ?? false;
                $data['allow_backorder'] = $data['allow_backorder'] ?? false;

                if ($variantId) {
                    $variant = ProductVariant::findOrFail($variantId);
                    $variant->update($data);

                    return ApiResponse::success($variant->fresh()->load('product'), 'Product variant updated successfully.');
                }

                $variant = ProductVariant::create($data);

                return ApiResponse::created($variant->fresh()->load('product'), 'Product variant created successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving variant: '.$e->getMessage(), 500);
        }
    }

    public function getVariantById(int $id): JsonResponse
    {
        try {
            return ApiResponse::success(ProductVariant::with('product')->findOrFail($id));
        } catch (\Exception) {
            return ApiResponse::notFound('Variant not found.');
        }
    }

    public function deleteVariant(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $variant = ProductVariant::findOrFail($id);
                $variant->delete();

                return ApiResponse::success(null, 'Product variant deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting variant: '.$e->getMessage(), 500);
        }
    }
}
