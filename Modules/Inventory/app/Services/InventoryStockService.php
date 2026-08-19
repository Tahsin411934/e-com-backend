<?php

namespace Modules\Inventory\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\Models\InventoryStock;
use Modules\Inventory\Models\InventoryMovement;
use Yajra\DataTables\DataTables;

class InventoryStockService
{
    public function getStockDataTable(Request $request)
    {
        $query = InventoryStock::query()
            ->with(['location.store', 'variant.product', 'variantOption'])
            ->orderByDesc('updated_at');

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
                    ? $variantName . ' / ' . $stock->variantOption->color_name
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
                    'id' => $stock->id,
                    'edit' => 'stockEdit',
                    'delete' => 'stockDelete',
                ])->render();
            })
            ->rawColumns(['low_stock', 'action'])
            ->make(true);
    }

    public function saveStock(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                $stockId = $data['stock_id'] ?? null;
                unset($data['stock_id']);

                if ($stockId) {
                    $stock = InventoryStock::findOrFail($stockId);

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

                return [
                    'status' => 'success',
                    'message' => $message,
                    'stock' => $stock->fresh()->load('location.store'),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error saving stock: ' . $e->getMessage(),
            ];
        }
    }

    public function getStockById(int $id): array
    {
        try {
            $stock = InventoryStock::with('location.store')->findOrFail($id);
            return [
                'status' => 'success',
                'stock' => $stock,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Stock record not found.',
            ];
        }
    }

    public function deleteStock(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $stock = InventoryStock::findOrFail($id);
                $stock->delete();
                return [
                    'status' => 'success',
                    'message' => 'Stock record deleted successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error deleting stock: ' . $e->getMessage(),
            ];
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

    public function getLowStockItems(): array
    {
        return InventoryStock::whereRaw('(quantity_on_hand - quantity_reserved) <= reorder_point')
            ->with('location.store')
            ->orderBy('quantity_on_hand')
            ->get()
            ->toArray();
    }
}