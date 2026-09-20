<?php

namespace Modules\Store\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Store\Http\Requests\StoreStaffRequest;
use Modules\Identity\Models\Role;
use Modules\Store\Models\Store;
use Modules\Store\Support\CurrentStore;
use Modules\Store\Services\StoreService;
use Modules\Store\Services\StoreStaffService;

class StoreStaffController extends Controller
{
    public function __construct(private StoreStaffService $storeStaffService, private StoreService $storeService) {}

    public function index()
    {
        $stores = CurrentStore::id()
            ? Store::whereKey(CurrentStore::id())->where('status', 'active')->get()->map(fn ($store) => [
                'id' => $store->id,
                'name' => $store->name,
            ])->all()
            : $this->storeService->getAllActiveStores();
        $roles = Role::where('scope', 'store')
            ->where('store_id', CurrentStore::id())
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('store::store-staff.index', compact('stores', 'roles'));
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
