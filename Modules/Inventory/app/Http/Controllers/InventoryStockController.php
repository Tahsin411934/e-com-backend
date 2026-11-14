<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\Models\Product;
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

    public function bulkAddPage()
    {
        $locations = InventoryLocation::forCurrentStore()
            ->with('store')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $oldProduct = old('product_id')
            ? Product::forCurrentStore()->find(old('product_id'))
            : null;

        return view('inventory::stock.bulk-add', compact('locations', 'oldProduct'));
    }

    public function searchBulkProducts(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $products = Product::forCurrentStore()
            ->where('status', 'active')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('name')
            ->limit(25)
            ->get(['id', 'name'])
            ->map(fn ($product) => ['id' => $product->id, 'text' => $product->name]);

        return response()->json(['results' => $products]);
    }

    public function bulkProductDetails(Request $request, int $product)
    {
        $location = InventoryLocation::forCurrentStore()
            ->where('status', 'active')
            ->findOrFail($request->integer('location_id'));
        $product = Product::forCurrentStore()
            ->where('status', 'active')
            ->with(['variants' => fn ($query) => $query->where('status', 'active')->with(['options' => fn ($options) => $options->where('status', 'active')->orderBy('sort_order')])])
            ->findOrFail($product);

        $variantIds = $product->variants->pluck('id');
        $stocks = \Modules\Inventory\Models\InventoryStock::query()
            ->where('location_id', $location->id)
            ->whereIn('variant_id', $variantIds)
            ->get(['variant_id', 'variant_option_id', 'quantity_on_hand'])
            ->mapWithKeys(fn ($stock) => [
                $stock->variant_id.':'.($stock->variant_option_id ?? 0) => $stock->quantity_on_hand,
            ]);

        $variants = $product->variants->flatMap(fn ($variant) => $variant->options->isNotEmpty()
            ? $variant->options->map(fn ($option) => [
                'variant_id' => $variant->id,
                'variant_name' => $variant->name,
                'variant_sku' => $variant->sku,
                'variant_option_id' => $option->id,
                'name' => $variant->name.' / '.$option->color_name,
                'sku' => $option->sku ?: $variant->sku,
                'current_stock' => (int) ($stocks[$variant->id.':'.$option->id] ?? 0),
            ])
            : [[
                'variant_id' => $variant->id,
                'variant_name' => $variant->name,
                'variant_sku' => $variant->sku,
                'variant_option_id' => null,
                'name' => $variant->name,
                'sku' => $variant->sku,
                'current_stock' => (int) ($stocks[$variant->id.':0'] ?? 0),
            ]])->values();

        return response()->json([
            'product' => ['id' => $product->id, 'name' => $product->name],
            'variants' => $variants,
        ]);
    }

    public function bulkAdd(Request $request)
    {
        $data = $request->validate([
            'location_id' => ['required', 'integer'],
            'product_id' => ['required', 'integer'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.variant_id' => ['required', 'integer'],
            'items.*.variant_option_id' => ['nullable', 'integer'],
            'items.*.quantity_to_add' => ['required', 'integer', 'min:0'],
        ]);

        $items = collect($data['items'])->filter(fn ($item) => (int) $item['quantity_to_add'] > 0)->values();
        if ($items->isEmpty()) {
            return back()->withInput()->withErrors(['items' => 'Enter an add quantity for at least one variant.']);
        }

        $location = InventoryLocation::forCurrentStore()
            ->where('status', 'active')
            ->findOrFail($data['location_id']);

        $product = Product::forCurrentStore()->where('status', 'active')->findOrFail($data['product_id']);

        DB::transaction(function () use ($items, $location, $product) {
            // Serialize bulk additions for this product so two staff members do not
            // create duplicate stock rows when the location has no row yet.
            $lockedProduct = Product::forCurrentStore()->lockForUpdate()->findOrFail($product->id);

            foreach ($items as $item) {
                $variant = ProductVariant::query()
                    ->where('product_id', $lockedProduct->id)
                    ->where('status', 'active')
                    ->with('options')
                    ->findOrFail($item['variant_id']);

                $optionId = $item['variant_option_id'] ?? null;
                $optionName = null;
                if ($optionId) {
                    $option = $variant->options->firstWhere('id', (int) $optionId);
                    if (! $option || $option->status !== 'active') {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'items' => 'A selected color option does not belong to this product variant.',
                        ]);
                    }
                    $optionName = $option->color_name;
                }

                $stockQuery = \Modules\Inventory\Models\InventoryStock::query()
                    ->where('location_id', $location->id)
                    ->where('variant_id', $variant->id);
                $optionId ? $stockQuery->where('variant_option_id', $optionId) : $stockQuery->whereNull('variant_option_id');

                $stock = $stockQuery->lockForUpdate()->first();
                if (! $stock) {
                    $stock = new \Modules\Inventory\Models\InventoryStock([
                        'location_id' => $location->id,
                        'variant_id' => $variant->id,
                        'variant_option_id' => $optionId,
                        'quantity_on_hand' => 0,
                        'quantity_reserved' => 0,
                        'reorder_point' => 5,
                    ]);
                }
                $stock->quantity_on_hand += (int) $item['quantity_to_add'];
                $stock->save();

                $this->stockService->recordBulkAddition(
                    $location->id,
                    $variant->id,
                    (int) $item['quantity_to_add'],
                    $variant->name.($optionName ? ' / '.$optionName : ''),
                    auth()->id()
                );
            }
        });

        return redirect()->route('inventory-stock.bulk-add')
            ->with('success', 'Stock quantities added successfully for '.$items->count().' variant option(s).');
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
