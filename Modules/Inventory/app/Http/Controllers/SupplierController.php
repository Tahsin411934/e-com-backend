<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Inventory\Http\Requests\SupplierRequest;
use Modules\Inventory\Services\SupplierService;

class SupplierController extends Controller
{
    public function __construct(private SupplierService $supplierService) {}

    public function index()
    {
        return view('inventory::suppliers.index');
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
