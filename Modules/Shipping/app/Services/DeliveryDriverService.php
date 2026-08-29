<?php

namespace Modules\Shipping\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Shipping\Models\DeliveryDriver;
use Yajra\DataTables\DataTables;

class DeliveryDriverService
{
    public function getDriverDataTable(Request $request)
    {
        $query = DeliveryDriver::with(['store', 'zone'])->orderByDesc('created_at');

        return DataTables::of($query)
            ->addColumn('store_name', fn (DeliveryDriver $driver) => $driver->store?->name ?? '-')
            ->addColumn('zone_name', fn (DeliveryDriver $driver) => $driver->zone?->name ?? '-')
            ->editColumn('vehicle_type', fn (DeliveryDriver $driver) => ucfirst(str_replace('_', ' ', $driver->vehicle_type)))
            ->editColumn('status', fn (DeliveryDriver $driver) => ucfirst($driver->status))
            ->editColumn('last_seen_at', fn (DeliveryDriver $driver) => $driver->last_seen_at?->format('d M Y H:i') ?? '-')
            ->editColumn('created_at', fn (DeliveryDriver $driver) => $driver->created_at->format('d M Y H:i'))
            ->addColumn('action', function (DeliveryDriver $driver) {
                return view('components.action-buttons', [
                    'id' => $driver->id,
                    'edit' => 'delivery-driverEdit',
                    'delete' => 'delivery-driverDelete',
                ])->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function saveDriver(array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($data) {
                $driverId = $data['driver_id'] ?? null;
                unset($data['driver_id']);

                if ($driverId) {
                    $driver = DeliveryDriver::findOrFail($driverId);
                    $driver->update($data);
                    $message = 'Delivery driver updated successfully.';
                } else {
                    $driver = DeliveryDriver::create($data);
                    $message = 'Delivery driver created successfully.';
                }

                if ($driverId) {
                    return ApiResponse::success($driver->fresh()->load(['store', 'zone']), $message);
                }

                return ApiResponse::created($driver->fresh()->load(['store', 'zone']), $message);
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving delivery driver: '.$e->getMessage(), 500);
        }
    }

    public function getDriverById(int $id): JsonResponse
    {
        try {
            return ApiResponse::success(DeliveryDriver::with(['store', 'zone'])->findOrFail($id));
        } catch (\Exception) {
            return ApiResponse::notFound('Delivery driver not found.');
        }
    }

    public function deleteDriver(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                DeliveryDriver::findOrFail($id)->delete();

                return ApiResponse::success(null, 'Delivery driver deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting delivery driver: '.$e->getMessage(), 500);
        }
    }
}
