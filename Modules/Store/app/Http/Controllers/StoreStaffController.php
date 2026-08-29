<?php

namespace Modules\Store\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Store\Http\Requests\StoreStaffRequest;
use Modules\Store\Services\StoreService;
use Modules\Store\Services\StoreStaffService;

class StoreStaffController extends Controller
{
    public function __construct(private StoreStaffService $storeStaffService, private StoreService $storeService) {}

    public function index()
    {
        $stores = $this->storeService->getAllActiveStores();

        return view('store::store-staff.index', compact('stores'));
    }

    public function dataTable(Request $request)
    {
        return $this->storeStaffService->getStoreStaffDataTable($request);
    }

    public function store(StoreStaffRequest $request)
    {
        return $this->storeStaffService->saveStoreStaff($request->validated());
    }

    public function show($id)
    {
        return $this->storeStaffService->getStoreStaffById((int) $id);
    }

    public function update(StoreStaffRequest $request, $id)
    {
        return $this->storeStaffService->saveStoreStaff($request->validated() + ['staff_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->storeStaffService->deleteStoreStaff((int) $id);
    }
}
