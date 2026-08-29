<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Inventory\Http\Requests\InventoryLocationRequest;
use Modules\Inventory\Services\InventoryLocationService;
use Modules\Store\Models\Store;

class InventoryLocationController extends Controller
{
    public function __construct(private InventoryLocationService $locationService) {}

    public function index()
    {
        $stores = Store::where('status', 'active')->orderBy('name')->get();

        return view('inventory::locations.index', compact('stores'));
    }

    public function dataTable(Request $request)
    {
        return $this->locationService->getLocationDataTable($request);
    }

    public function store(InventoryLocationRequest $request)
    {
        return $this->locationService->saveLocation($request->validated());
    }

    public function show($id)
    {
        return $this->locationService->getLocationById((int) $id);
    }

    public function update(InventoryLocationRequest $request, $id)
    {
        return $this->locationService->saveLocation($request->validated() + ['location_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->locationService->deleteLocation((int) $id);
    }
}
