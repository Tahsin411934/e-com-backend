<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Catalog\Models\ProductVariant;
use Modules\Inventory\Http\Requests\InventoryStockRequest;
use Modules\Inventory\Models\InventoryLocation;
use Modules\Inventory\Services\InventoryStockService;
use Modules\Store\Models\Store;

class InventoryStockController extends Controller
{
    public function __construct(private InventoryStockService $stockService) {}

    public function index()
    {
        $locations = InventoryLocation::forCurrentStore()->with('store')->where('status', 'active')->orderBy('name')->get();
        $variants = ProductVariant::with(['product', 'options' => fn ($query) => $query->where('status', 'active')])
            ->whereHas('product', fn ($q) => $q->forCurrentStore())
            ->where('status', 'active')->orderBy('name')->get();

        $actor = auth()->user();
        $canAssignStore = (bool) ($actor && ($actor->hasRole('Super Admin') || $actor->hasRole('Admin')));

        $stores = $canAssignStore
            ? Store::where('status', 'active')->orderBy('name')->get()
            : collect();

        return view('inventory::stock.index', compact('locations', 'variants', 'stores', 'canAssignStore'));
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
