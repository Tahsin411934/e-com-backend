<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Inventory\Http\Requests\SupplierRequest;
use Modules\Inventory\Services\SupplierService;
use Modules\Store\Models\Store;
use Modules\Store\Support\CurrentStore;

class SupplierController extends Controller
{
    public function __construct(private SupplierService $supplierService) {}

    public function index()
    {
        $actor = auth()->user();
        $canAssignStore = (bool) ($actor && ($actor->hasRole('Super Admin') || $actor->hasRole('Admin')));

        $stores = $canAssignStore
            ? Store::where('status', 'active')->orderBy('name')->get()
            : collect();
        $currentStore = CurrentStore::store();

        return view('inventory::suppliers.index', compact('stores', 'currentStore', 'canAssignStore'));
    }

    public function dataTable(Request $request)
    {
        return $this->supplierService->getSupplierDataTable($request);
    }

    public function store(SupplierRequest $request)
    {
        return $this->supplierService->saveSupplier($request->validated());
    }

    public function show($id)
    {
        return $this->supplierService->getSupplierById((int) $id);
    }

    public function update(SupplierRequest $request, $id)
    {
        return $this->supplierService->saveSupplier($request->validated() + ['supplier_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->supplierService->deleteSupplier((int) $id);
    }
}
