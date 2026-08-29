<?php

namespace Modules\Inventory\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\Models\InventoryMovement;
use Yajra\DataTables\DataTables;

class InventoryMovementService
{
    public function getMovementDataTable(Request $request)
    {
        $query = InventoryMovement::query()
            ->with(['location', 'createdBy'])
            ->orderByDesc('created_at');

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

                if ($movementId) {
                    $movement = InventoryMovement::findOrFail($movementId);
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
            $movement = InventoryMovement::with(['location', 'createdBy'])->findOrFail($id);

            return ApiResponse::success($movement);
        } catch (\Exception $e) {
            return ApiResponse::notFound('Movement not found.');
        }
    }

    public function deleteMovement(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $movement = InventoryMovement::findOrFail($id);
                $movement->delete();

                return ApiResponse::success(null, 'Movement deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting movement: '.$e->getMessage(), 500);
        }
    }
}
