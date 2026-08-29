<?php

namespace Modules\Shipping\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Shipping\Models\DeliveryDriver;
use Modules\Shipping\Models\Shipment;
use Modules\Shipping\Models\ShipmentEvent;
use Yajra\DataTables\DataTables;

class ShipmentService
{
    public function getShipmentDataTable(Request $request)
    {
        $query = Shipment::with(['order', 'store', 'zone', 'driver'])->orderByDesc('created_at');

        return DataTables::of($query)
            ->addColumn('order_number', fn (Shipment $shipment) => $shipment->order?->order_number ?? '-')
            ->addColumn('store_name', fn (Shipment $shipment) => $shipment->store?->name ?? '-')
            ->addColumn('zone_name', fn (Shipment $shipment) => $shipment->zone?->name ?? '-')
            ->addColumn('driver_name', fn (Shipment $shipment) => $shipment->driver?->name ?? '-')
            ->editColumn('status', fn (Shipment $shipment) => ucfirst(str_replace('_', ' ', $shipment->status)))
            ->editColumn('service_level', fn (Shipment $shipment) => ucfirst(str_replace('_', ' ', $shipment->service_level)))
            ->editColumn('shipping_cost', fn (Shipment $shipment) => number_format($shipment->shipping_cost, 2))
            ->editColumn('scheduled_delivery_date', fn (Shipment $shipment) => $shipment->scheduled_delivery_date?->format('d M Y') ?? '-')
            ->editColumn('created_at', fn (Shipment $shipment) => $shipment->created_at->format('d M Y H:i'))
            ->addColumn('action', function (Shipment $shipment) {
                return view('components.action-buttons', [
                    'id' => $shipment->id,
                    'edit' => 'shipmentEdit',
                    'delete' => 'shipmentDelete',
                ])->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function saveShipment(array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($data) {
                $shipmentId = $data['shipment_id'] ?? null;
                unset($data['shipment_id']);
                $oldStatus = null;
                $oldDriverId = null;

                if (empty($data['tracking_number'])) {
                    $data['tracking_number'] = $this->generateTrackingNumber();
                }

                if ($shipmentId) {
                    $shipment = Shipment::findOrFail($shipmentId);
                    $oldStatus = $shipment->status;
                    $oldDriverId = $shipment->driver_id;
                    $shipment->update($data);
                    $message = 'Shipment updated successfully.';
                } else {
                    $shipment = Shipment::create($data);
                    $message = 'Shipment created successfully.';
                }

                if (! $shipmentId || $oldStatus !== $shipment->status) {
                    $this->recordStatusEvent($shipment, $oldStatus);
                }

                $this->syncDriverStatus($shipment, $oldDriverId);

                if ($shipmentId) {
                    return ApiResponse::success($shipment->fresh()->load(['order', 'store', 'zone', 'driver', 'shippingAddress']), $message);
                }

                return ApiResponse::created($shipment->fresh()->load(['order', 'store', 'zone', 'driver', 'shippingAddress']), $message);
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving shipment: '.$e->getMessage(), 500);
        }
    }

    public function getShipmentById(int $id): JsonResponse
    {
        try {
            return ApiResponse::success(
                Shipment::with(['order', 'store', 'zone', 'driver', 'shippingAddress', 'events'])->findOrFail($id)
            );
        } catch (\Exception) {
            return ApiResponse::notFound('Shipment not found.');
        }
    }

    public function deleteShipment(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $shipment = Shipment::findOrFail($id);
                $driverId = $shipment->driver_id;
                $shipment->delete();
                $this->freeDriverIfIdle($driverId);

                return ApiResponse::success(null, 'Shipment deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting shipment: '.$e->getMessage(), 500);
        }
    }

    protected function generateTrackingNumber(): string
    {
        do {
            $number = 'SHP-'.now()->format('ymd').'-'.strtoupper(substr(uniqid(), -6));
        } while (Shipment::where('tracking_number', $number)->exists());

        return $number;
    }

    protected function recordStatusEvent(Shipment $shipment, ?string $oldStatus): void
    {
        ShipmentEvent::create([
            'shipment_id' => $shipment->id,
            'driver_id' => $shipment->driver_id,
            'event_type' => $oldStatus ? 'status_update' : 'note',
            'status' => $shipment->status,
            'title' => $oldStatus
                ? 'Shipment status changed to '.str_replace('_', ' ', $shipment->status)
                : 'Shipment created',
            'description' => $oldStatus
                ? 'Previous status: '.str_replace('_', ' ', $oldStatus)
                : 'Tracking number '.$shipment->tracking_number.' was created.',
            'occurred_at' => now(),
        ]);
    }

    protected function syncDriverStatus(Shipment $shipment, ?int $oldDriverId): void
    {
        if ($oldDriverId && $oldDriverId !== $shipment->driver_id) {
            $this->freeDriverIfIdle($oldDriverId);
        }

        if (! $shipment->driver_id) {
            return;
        }

        $busyStatuses = ['ready_for_pickup', 'out_for_delivery'];
        $availableStatuses = ['delivered', 'failed', 'returned', 'cancelled'];

        if (in_array($shipment->status, $busyStatuses, true)) {
            DeliveryDriver::whereKey($shipment->driver_id)->update(['status' => 'busy']);
        } elseif (in_array($shipment->status, $availableStatuses, true)) {
            $this->freeDriverIfIdle($shipment->driver_id);
        }
    }

    protected function freeDriverIfIdle(?int $driverId): void
    {
        if (! $driverId) {
            return;
        }

        $hasActiveShipment = Shipment::where('driver_id', $driverId)
            ->whereIn('status', ['ready_for_pickup', 'out_for_delivery'])
            ->exists();

        if (! $hasActiveShipment) {
            DeliveryDriver::whereKey($driverId)->update(['status' => 'available']);
        }
    }
}
