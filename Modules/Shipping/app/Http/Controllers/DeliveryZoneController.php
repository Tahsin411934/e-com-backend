<?php

namespace Modules\Shipping\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Shipping\Http\Requests\DeliveryZoneRequest;
use Modules\Shipping\Services\DeliveryZoneService;
use Modules\Store\Models\Country;
use Modules\Store\Models\Store;

class DeliveryZoneController extends Controller
{
    public function __construct(protected DeliveryZoneService $zoneService) {}

    public function index()
    {
        $stores = Store::where('status', 'active')->orderBy('name')->get();
        $countries = Country::orderBy('name')->get();

        return view('shipping::zones.index', compact('stores', 'countries'));
    }

    public function dataTable(Request $request)
    {
        return $this->zoneService->getZoneDataTable($request);
    }

    public function store(DeliveryZoneRequest $request)
    {
        return $this->zoneService->saveZone($request->validated());
    }

    public function show($id)
    {
        return $this->zoneService->getZoneById((int) $id);
    }

    public function update(DeliveryZoneRequest $request, int $id)
    {
        return $this->zoneService->saveZone($request->validated() + ['zone_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->zoneService->deleteZone((int) $id);
    }
}
