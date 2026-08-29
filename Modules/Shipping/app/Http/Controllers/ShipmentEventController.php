<?php

namespace Modules\Shipping\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Identity\Models\User;
use Modules\Shipping\Http\Requests\ShipmentEventRequest;
use Modules\Shipping\Models\DeliveryDriver;
use Modules\Shipping\Models\Shipment;
use Modules\Shipping\Services\ShipmentEventService;

class ShipmentEventController extends Controller
{
    public function __construct(protected ShipmentEventService $eventService) {}

    public function index()
    {
        $shipments = Shipment::orderByDesc('created_at')->limit(200)->get();
        $drivers = DeliveryDriver::orderBy('name')->get();
        $users = User::where('status', 'active')->orderBy('email')->get();

        return view('shipping::events.index', compact('shipments', 'drivers', 'users'));
    }

    public function dataTable(Request $request)
    {
        return $this->eventService->getEventDataTable($request);
    }

    public function store(ShipmentEventRequest $request)
    {
        return $this->eventService->saveEvent($request->validated());
    }

    public function show($id)
    {
        return $this->eventService->getEventById((int) $id);
    }

    public function update(ShipmentEventRequest $request, int $id)
    {
        return $this->eventService->saveEvent($request->validated() + ['event_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->eventService->deleteEvent((int) $id);
    }
}
