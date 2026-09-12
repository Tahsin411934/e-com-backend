<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Inventory\Http\Requests\SupplierPaymentRequest;
use Modules\Inventory\Services\SupplierPaymentService;

class SupplierPaymentController extends Controller
{
    public function __construct(private SupplierPaymentService $service) {}

    public function index(Request $request)
    {
        return view('inventory::supplier-payments.index', [
            'suppliers' => $this->service->getSuppliers(),
            'stores' => $this->service->getStores(),
            'purchaseOrders' => $this->service->getPurchaseOrders(),
            'accounts' => $this->service->getAccounts(),
        ]);
    }

    public function dataTable(Request $request)
    {
        return $this->service->getDataTable($request);
    }

    public function store(SupplierPaymentRequest $request)
    {
        return $this->service->save($request->validated());
    }

    public function show($id)
    {
        return $this->service->find((int) $id);
    }

    public function destroy($id)
    {
        return $this->service->delete((int) $id);
    }
}
