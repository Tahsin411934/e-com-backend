<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Catalog\Models\ProductVariant;
use Modules\Inventory\Services\InventoryStockService;
use Modules\Inventory\Http\Requests\InventoryStockRequest;
use Modules\Inventory\Models\InventoryLocation;

class InventoryStockController extends Controller
{
    protected InventoryStockService $stockService;

    public function __construct(InventoryStockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index()
    {
        $locations = InventoryLocation::with('store')->where('status', 'active')->orderBy('name')->get();
        $variants = ProductVariant::with(['product', 'options' => fn ($query) => $query->where('status', 'active')])
            ->where('status', 'active')->orderBy('name')->get();
        return view('inventory::stock.index', compact('locations', 'variants'));
    }

    public function dataTable(Request $request)
    {
        return $this->stockService->getStockDataTable($request);
    }

    public function store(InventoryStockRequest $request)
    {
        $result = $this->stockService->saveStock($request->validated());
        return response()->json($result, $result['status'] === 'success' ? 200 : 500);
    }

    public function show($id)
    {
        $result = $this->stockService->getStockById($id);
        return response()->json($result);
    }

    public function update(InventoryStockRequest $request, $id)
    {
        $data = $request->validated();
        $data['stock_id'] = $id;
        $result = $this->stockService->saveStock($data);
        return response()->json($result, $result['status'] === 'success' ? 200 : 500);
    }

    public function destroy($id)
    {
        $result = $this->stockService->deleteStock($id);
        return response()->json($result, $result['status'] === 'success' ? 200 : 500);
    }
}