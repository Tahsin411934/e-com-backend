<?php

namespace Modules\Inventory\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\Models\InventoryLocation;
use Modules\Store\Support\CurrentStore;
use Yajra\DataTables\DataTables;

class InventoryLocationService
{
    public function getLocationDataTable(Request $request)
    {
        $query = InventoryLocation::forCurrentStore()->with('store')->orderByDesc('created_at');

        if ($request->store_id) {
            $query->where('store_id', $request->store_id);
        }

        return DataTables::of($query)
            ->editColumn('status', function (InventoryLocation $location) {
                return ucfirst($location->status);
            })
            ->editColumn('location_type', function (InventoryLocation $location) {
                return ucfirst(str_replace('_', ' ', $location->location_type));
            })
            ->addColumn('store_name', function (InventoryLocation $location) {
                return $location->store ? $location->store->name : '-';
            })
            ->editColumn('created_at', function (InventoryLocation $location) {
                return $location->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (InventoryLocation $location) {
                return view('components.action-buttons', [
                'permission' => 'inventory-locations',
                'entityLabel' => 'Inventory Location',
                    'id' => $location->id,
                    'edit' => 'locationEdit',
                    'delete' => 'locationDelete',
                ])->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function saveLocation(array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($data) {
                $locationId = $data['location_id'] ?? null;
                unset($data['location_id']);

                // Store owners/staff are locked to their own store.
                if (! $this->isPlatformAdmin()) {
                    $data['store_id'] = CurrentStore::id();
                }

                if ($locationId) {
                    $location = InventoryLocation::forCurrentStore()->findOrFail($locationId);
                    $location->update($data);
                    $message = 'Location updated successfully.';
                } else {
                    $location = InventoryLocation::create($data);
                    $message = 'Location created successfully.';
                }

                return ApiResponse::success($location->fresh()->load('store'), $message);
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving location: '.$e->getMessage(), 500);
        }
    }

    public function getLocationById(int $id): JsonResponse
    {
        try {
            $location = InventoryLocation::forCurrentStore()->with('store')->findOrFail($id);

            return ApiResponse::success($location);
        } catch (\Exception $e) {
            return ApiResponse::notFound('Location not found.');
        }
    }

    public function deleteLocation(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $location = InventoryLocation::forCurrentStore()->findOrFail($id);
                $location->delete();

                return ApiResponse::success(null, 'Location deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting location: '.$e->getMessage(), 500);
        }
    }

    public function getAllActiveLocations(): JsonResponse
    {
        return InventoryLocation::forCurrentStore()
            ->where('status', 'active')
            ->with('store')
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    private function isPlatformAdmin(): bool
    {
        $actor = auth()->user();

        return (bool) ($actor && ($actor->hasRole('Super Admin') || $actor->hasRole('Admin')));
    }
}
