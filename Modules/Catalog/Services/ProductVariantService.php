<?php

namespace Modules\Catalog\Services;

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

    public function saveVariant(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                $variantId = $data['variant_id'] ?? null;
                $data['track_inventory'] = $data['track_inventory'] ?? false;
                $data['allow_backorder'] = $data['allow_backorder'] ?? false;

                if ($variantId) {
                    $variant = ProductVariant::findOrFail($variantId);
                    $variant->update($data);
                    $message = 'Product variant updated successfully.';
                } else {
                    $variant = ProductVariant::create($data);
                    $message = 'Product variant created successfully.';
                }

                return [
                    'status' => 'success',
                    'message' => $message,
                    'variant' => $variant->fresh()->load('product'),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error saving variant: ' . $e->getMessage(),
            ];
        }
    }

    public function getVariantById(int $id): array
    {
        try {
            $variant = ProductVariant::with('product')->findOrFail($id);
            return [
                'status' => 'success',
                'variant' => $variant,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Variant not found.',
                'variant' => null,
            ];
        }
    }

    public function deleteVariant(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $variant = ProductVariant::findOrFail($id);
                $variant->delete();

                return [
                    'status' => 'success',
                    'message' => 'Product variant deleted successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error deleting variant: ' . $e->getMessage(),
            ];
        }
    }
}
