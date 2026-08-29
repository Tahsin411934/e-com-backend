<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'store_id' => 'nullable|exists:stores,id',
            'account_id' => 'required|exists:account_accounts,id',
            'amount' => 'required|numeric|gt:0',
            'payment_date' => 'required|date',
            'payment_method' => 'nullable',
            'reference_no' => 'nullable|string|max:120',
            'note' => 'nullable|string',
        ]);

        return $this->service->save($validated);
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
