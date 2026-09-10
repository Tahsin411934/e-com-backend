<?php

namespace Modules\Inventory\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\Models\InventoryLocation;
use Modules\Inventory\Models\InventoryMovement;
use Modules\Store\Support\CurrentStore;
use Yajra\DataTables\DataTables;

class InventoryMovementService
{
    public function getMovementDataTable(Request $request)
    {
        $query = InventoryMovement::query()
            ->with(['location.store', 'createdBy'])
            ->whereHas('location', fn ($q) => $q->forCurrentStore())
            ->orderByDesc('created_at');

        if ($request->store_id) {
            $query->whereHas('location', fn ($q) => $q->where('store_id', $request->store_id));
        }

        return DataTables::of($query)
            ->addColumn('location_name', function (InventoryMovement $movement) {
                return $movement->location ? $movement->location->name : '-';
            })
            ->editColumn('movement_type', function (InventoryMovement $movement) {
                return ucfirst(str_replace('_', ' ', $movement->movement_type));
            })
            ->editColumn('quantity', function (InventoryMovement $movement) {
                return $movement->quantity > 0
                    ? '<span class="text-green-600">+'.number_format($movement->quantity).'</span>'
                    : '<span class="text-red-600">'.number_format($movement->quantity).'</span>';
            })
            ->addColumn('store_name', function (InventoryMovement $movement) {
                return $movement->location?->store?->name ?? '-';
            })
            ->addColumn('created_by_name', function (InventoryMovement $movement) {
                return $movement->createdBy ? $movement->createdBy->name : 'System';
            })
            ->editColumn('created_at', function (InventoryMovement $movement) {
                return $movement->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (InventoryMovement $movement) {
                return view('components.action-buttons', [
                    'id' => $movement->id,
                    'edit' => 'movementEdit',
                    'delete' => 'movementDelete',
                ])->render();
            })
            ->rawColumns(['quantity', 'action'])
            ->make(true);
    }

    public function saveMovement(array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($data) {
                $movementId = $data['movement_id'] ?? null;
                unset($data['movement_id']);

                if (! isset($data['created_by'])) {
                    $data['created_by'] = auth()->id();
                }

                // Location must belong to the acting store (tenant isolation).
                $location = InventoryLocation::forCurrentStore()->find($data['location_id'] ?? null);
                if (! $location) {
                    throw new \RuntimeException('Selected location does not belong to your store.');
                }

                if ($movementId) {
                    $movement = InventoryMovement::forCurrentStore()->findOrFail($movementId);
                    $movement->update($data);
                    $message = 'Movement updated successfully.';
                } else {
                    $movement = InventoryMovement::create($data);
                    $message = 'Movement created successfully.';
                }

                return ApiResponse::success($movement->fresh()->load(['location', 'createdBy']), $message);
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving movement: '.$e->getMessage(), 500);
        }
    }

    public function getMovementById(int $id): JsonResponse
    {
        try {
            $movement = InventoryMovement::forCurrentStore()->with(['location.store', 'createdBy'])->findOrFail($id);

            return ApiResponse::success($movement);
        } catch (\Exception $e) {
            return ApiResponse::notFound('Movement not found.');
        }
    }

    public function deleteMovement(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $movement = InventoryMovement::forCurrentStore()->findOrFail($id);
                $movement->delete();

                return ApiResponse::success(null, 'Movement deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting movement: '.$e->getMessage(), 500);
        }
    }
}
