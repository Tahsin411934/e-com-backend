<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Catalog\Models\ProductVariant;
use Modules\Inventory\Http\Requests\InventoryStockRequest;
use Modules\Inventory\Models\InventoryLocation;
use Modules\Inventory\Services\InventoryStockService;

class InventoryStockController extends Controller
{
    public function __construct(private InventoryStockService $stockService) {}

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
        return $this->stockService->saveStock($request->validated());
    }

    public function show($id)
    {
        return $this->stockService->getStockById((int) $id);
    }

    public function update(InventoryStockRequest $request, $id)
    {
        return $this->stockService->saveStock($request->validated() + ['stock_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->stockService->deleteStock((int) $id);
    }
}
