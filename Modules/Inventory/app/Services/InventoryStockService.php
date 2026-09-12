<?php

namespace Modules\Inventory\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\Models\InventoryLocation;
use Modules\Inventory\Models\InventoryMovement;
use Modules\Inventory\Models\InventoryStock;
use Yajra\DataTables\DataTables;

class InventoryStockService
{
    public function getStockDataTable(Request $request)
    {
        $query = InventoryStock::forCurrentStore()
            ->with(['location.store', 'variant.product', 'variantOption'])
            ->orderByDesc('updated_at');

        if ($request->store_id) {
            $query->whereHas('location', fn ($q) => $q->where('store_id', $request->store_id));
        }

        return DataTables::of($query)
            ->addColumn('location_name', function (InventoryStock $stock) {
                return $stock->location ? $stock->location->name : '-';
            })
            ->addColumn('store_name', function (InventoryStock $stock) {
                return $stock->location && $stock->location->store ? $stock->location->store->name : '-';
            })
            ->addColumn('product_name', function (InventoryStock $stock) {
                return $stock->variant?->product?->name ?? '-';
            })
            ->addColumn('variant_name', function (InventoryStock $stock) {
                $variantName = $stock->variant?->name ?? '-';

                return $stock->variantOption?->color_name
                    ? $variantName.' / '.$stock->variantOption->color_name
                    : $variantName;
            })
            ->addColumn('available_quantity', function (InventoryStock $stock) {
                return $stock->quantity_on_hand - $stock->quantity_reserved;
            })
            ->editColumn('quantity_on_hand', function (InventoryStock $stock) {
                return number_format($stock->quantity_on_hand);
            })
            ->editColumn('quantity_reserved', function (InventoryStock $stock) {
                return number_format($stock->quantity_reserved);
            })
            ->editColumn('reorder_point', function (InventoryStock $stock) {
                return number_format($stock->reorder_point);
            })
            ->addColumn('low_stock', function (InventoryStock $stock) {
                $available = $stock->quantity_on_hand - $stock->quantity_reserved;
                if ($available <= $stock->reorder_point) {
                    return '<span class="text-red-600 font-medium">Yes</span>';
                }

                return '<span class="text-green-600">No</span>';
            })
            ->editColumn('updated_at', function (InventoryStock $stock) {
                return $stock->updated_at->format('d M Y H:i');
            })
            ->addColumn('action', function (InventoryStock $stock) {
                return view('components.action-buttons', [
                    'permission' => 'inventory-stock',
                    'entityLabel' => 'Inventory Stock',
                    'id' => $stock->id,
                    'edit' => 'inventoryStockEdit',
                    'delete' => 'inventoryStockDelete',
                ])->render();
            })
            ->rawColumns(['low_stock', 'action'])
            ->make(true);
    }

    public function saveStock(array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($data) {
                $stockId = $data['stock_id'] ?? null;
                unset($data['stock_id']);

                if ($stockId) {
                    $stock = InventoryStock::forCurrentStore()->findOrFail($stockId);

                    $oldQuantity = $stock->quantity_on_hand;
                    $stock->update($data);

                    // Log movement if quantity changed
                    if ($oldQuantity != $stock->quantity_on_hand) {
                        $this->logMovement(
                            $stock->location_id,
                            $stock->variant_id,
                            'adjustment',
                            $stock->quantity_on_hand - $oldQuantity,
                            'Stock adjustment via edit',
                            auth()->id()
                        );
                    }

                    $message = 'Stock record updated successfully.';
                } else {
                    // Location must belong to the acting store (tenant isolation).
                    $location = InventoryLocation::forCurrentStore()->find($data['location_id'] ?? null);
                    if (! $location) {
                        throw new \RuntimeException('Selected location does not belong to your store.');
                    }

                    $stock = InventoryStock::create($data);
                    $this->logMovement(
                        $stock->location_id,
                        $stock->variant_id,
                        'adjustment',
                        $stock->quantity_on_hand,
                        'Initial stock entry',
                        auth()->id()
                    );
                    $message = 'Stock record created successfully.';
                }

                return ApiResponse::success($stock->fresh()->load('location.store'), $message);
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving stock: '.$e->getMessage(), 500);
        }
    }

    public function getStockById(int $id): JsonResponse
    {
        try {
            $stock = InventoryStock::forCurrentStore()->with('location.store')->findOrFail($id);

            return ApiResponse::success($stock);
        } catch (\Exception $e) {
            return ApiResponse::notFound('Stock record not found.');
        }
    }

    public function deleteStock(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $stock = InventoryStock::forCurrentStore()->findOrFail($id);
                $stock->delete();

                return ApiResponse::success(null, 'Stock record deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting stock: '.$e->getMessage(), 500);
        }
    }

    private function logMovement(int $locationId, int $variantId, string $type, int $quantity, ?string $note, ?int $userId): void
    {
        InventoryMovement::create([
            'location_id' => $locationId,
            'variant_id' => $variantId,
            'movement_type' => $type,
            'quantity' => $quantity,
            'note' => $note,
            'created_by' => $userId,
        ]);
    }

    public function getLowStockItems(): JsonResponse
    {
        return InventoryStock::query()
            ->whereHas('location', fn ($q) => $q->forCurrentStore())
            ->select('inventory_stock.*')
            ->join('product_variants', 'inventory_stock.variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->whereNull('product_variants.deleted_at')
            ->whereNull('products.deleted_at')
            ->where(function ($q) {
                $q->whereNull('inventory_stock.variant_option_id')
                    ->orWhereHas('variantOption', fn ($o) => $o->whereNull('deleted_at'));
            })
            ->whereRaw('(inventory_stock.quantity_on_hand - inventory_stock.quantity_reserved) <= inventory_stock.reorder_point')
            ->with('location.store')
            ->orderBy('quantity_on_hand')
            ->get()
            ->toArray();
    }
}
