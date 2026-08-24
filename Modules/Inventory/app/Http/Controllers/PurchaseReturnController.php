<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Inventory\Services\PurchaseReturnService;
use Modules\Inventory\Http\Requests\PurchaseReturnRequest;

class PurchaseReturnController extends Controller
{
    protected PurchaseReturnService $purchaseReturnService;

    public function __construct(PurchaseReturnService $purchaseReturnService)
    {
        $this->purchaseReturnService = $purchaseReturnService;
    }

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
        $result = $this->purchaseReturnService->saveReturn($request->validated());
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($result);
        }
        if ($result['status'] === 'success') {
            return redirect()->route('purchase-returns.index')->with('success', $result['message']);
        }
        return redirect()->back()->withInput()->with('error', $result['message']);
    }

    public function show($id)
    {
        $result = $this->purchaseReturnService->getReturnById($id);
        return response()->json($result);
    }

    public function edit($id)
    {
        $result = $this->purchaseReturnService->getReturnById($id);
        if ($result['status'] === 'error') {
            abort(404);
        }
        $return = $result['return'];
        $stores = $this->purchaseReturnService->getStores();
        $suppliers = $this->purchaseReturnService->getSuppliers();
        $purchaseOrders = $this->purchaseReturnService->getPurchaseOrders();
        return view('inventory::purchase-returns.index', compact('return', 'stores', 'suppliers', 'purchaseOrders'));
    }

    public function update(PurchaseReturnRequest $request, $id)
    {
        $data = $request->validated();
        $data['return_id'] = $id;
        $result = $this->purchaseReturnService->saveReturn($data);
        return response()->json($result, $result['status'] === 'success' ? 200 : 500);
    }

    public function destroy($id)
    {
        $result = $this->purchaseReturnService->deleteReturn($id);
        return response()->json($result, $result['status'] === 'success' ? 200 : 500);
    }
}