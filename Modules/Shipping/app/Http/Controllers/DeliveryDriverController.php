<?php

namespace Modules\Shipping\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Identity\Models\User;
use Modules\Shipping\Http\Requests\DeliveryDriverRequest;
use Modules\Shipping\Models\DeliveryZone;
use Modules\Shipping\Services\DeliveryDriverService;
use Modules\Store\Models\Store;

class DeliveryDriverController extends Controller
{
    public function __construct(protected DeliveryDriverService $driverService) {}

    public function index()
    {
        $stores = Store::where('status', 'active')->orderBy('name')->get();
        $zones = DeliveryZone::where('status', 'active')->orderBy('name')->get();
        $users = User::where('status', 'active')->orderBy('email')->get();

        return view('shipping::drivers.index', compact('stores', 'zones', 'users'));
    }

    public function dataTable(Request $request)
    {
        return $this->driverService->getDriverDataTable($request);
    }

    public function store(DeliveryDriverRequest $request)
    {
        return $this->driverService->saveDriver($request->validated());
    }

    public function show($id)
    {
        return $this->driverService->getDriverById((int) $id);
    }

    public function update(DeliveryDriverRequest $request, int $id)
    {
        return $this->driverService->saveDriver($request->validated() + ['driver_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->driverService->deleteDriver((int) $id);
    }
}
