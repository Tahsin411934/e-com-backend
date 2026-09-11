<?php

namespace Modules\Shipping\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Shipping\Models\Shipment;
use Modules\Shipping\Models\ShipmentEvent;
use Yajra\DataTables\DataTables;

class ShipmentEventService
{
    public function getEventDataTable(Request $request)
    {
        $query = ShipmentEvent::with(['shipment', 'driver', 'createdBy'])->orderByDesc('occurred_at');

        return DataTables::of($query)
            ->addColumn('tracking_number', fn (ShipmentEvent $event) => $event->shipment?->tracking_number ?? '-')
            ->addColumn('driver_name', fn (ShipmentEvent $event) => $event->driver?->name ?? '-')
            ->addColumn('created_by_name', fn (ShipmentEvent $event) => $event->createdBy?->email ?? '-')
            ->editColumn('event_type', fn (ShipmentEvent $event) => ucfirst(str_replace('_', ' ', $event->event_type)))
            ->editColumn('status', fn (ShipmentEvent $event) => $event->status ? ucfirst(str_replace('_', ' ', $event->status)) : '-')
            ->editColumn('occurred_at', fn (ShipmentEvent $event) => $event->occurred_at->format('d M Y H:i'))
            ->addColumn('action', function (ShipmentEvent $event) {
                return view('components.action-buttons', [
                'permission' => 'shipment-events',
                'entityLabel' => 'Shipment Event',
                    'id' => $event->id,
                    'edit' => 'shipment-eventEdit',
                    'delete' => 'shipment-eventDelete',
                ])->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function saveEvent(array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($data) {
                $eventId = $data['event_id'] ?? null;
                unset($data['event_id']);

                if ($eventId) {
                    $event = ShipmentEvent::findOrFail($eventId);
                    $event->update($data);
                    $message = 'Shipment event updated successfully.';
                } else {
                    $event = ShipmentEvent::create($data);
                    $message = 'Shipment event created successfully.';
                }

                if (! empty($data['status'])) {
                    Shipment::whereKey($data['shipment_id'])->update(['status' => $data['status']]);
                }

                if ($eventId) {
                    return ApiResponse::success($event->fresh()->load(['shipment', 'driver', 'createdBy']), $message);
                }

                return ApiResponse::created($event->fresh()->load(['shipment', 'driver', 'createdBy']), $message);
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving shipment event: '.$e->getMessage(), 500);
        }
    }

    public function getEventById(int $id): JsonResponse
    {
        try {
            return ApiResponse::success(ShipmentEvent::with(['shipment', 'driver', 'createdBy'])->findOrFail($id));
        } catch (\Exception) {
            return ApiResponse::notFound('Shipment event not found.');
        }
    }

    public function deleteEvent(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                ShipmentEvent::findOrFail($id)->delete();

                return ApiResponse::success(null, 'Shipment event deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting shipment event: '.$e->getMessage(), 500);
        }
    }
}
