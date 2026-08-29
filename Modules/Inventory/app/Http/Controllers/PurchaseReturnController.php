<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Inventory\Http\Requests\PurchaseReturnRequest;
use Modules\Inventory\Services\PurchaseReturnService;

class PurchaseReturnController extends Controller
{
    public function __construct(private PurchaseReturnService $purchaseReturnService) {}

    public function index(Request $request)
    {
        $stores = $this->purchaseReturnService->getStores();
        $suppliers = $this->purchaseReturnService->getSuppliers();
        $purchaseOrders = $this->purchaseReturnService->getPurchaseOrders();

        return view('inventory::purchase-returns.index', compact('stores', 'suppliers', 'purchaseOrders'));
    }

    public function dataTable(Request $request)
    {
        return $this->purchaseReturnService->getDataTable($request);
    }

    public function create()
    {
        $stores = $this->purchaseReturnService->getStores();
        $suppliers = $this->purchaseReturnService->getSuppliers();
        $purchaseOrders = $this->purchaseReturnService->getPurchaseOrders();

        return view('inventory::purchase-returns.index', compact('stores', 'suppliers', 'purchaseOrders'));
    }

    public function store(PurchaseReturnRequest $request)
    {
        $response = $this->purchaseReturnService->saveReturn($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return $response;
        }

        $payload = $response->getData(true);

        return redirect()->route('purchase-returns.index')
            ->with($payload['status'] === 'success' ? 'success' : 'error', $payload['message'] ?? 'Done.');
    }

    public function show($id)
    {
        return $this->purchaseReturnService->getReturnById((int) $id);
    }

    public function edit($id)
    {
        $return = $this->purchaseReturnService->getReturnModel((int) $id);
        $stores = $this->purchaseReturnService->getStores();
        $suppliers = $this->purchaseReturnService->getSuppliers();
        $purchaseOrders = $this->purchaseReturnService->getPurchaseOrders();

        return view('inventory::purchase-returns.index', compact('return', 'stores', 'suppliers', 'purchaseOrders'));
    }

    public function update(PurchaseReturnRequest $request, $id)
    {
        return $this->purchaseReturnService->saveReturn($request->validated() + ['return_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->purchaseReturnService->deleteReturn((int) $id);
    }
}
