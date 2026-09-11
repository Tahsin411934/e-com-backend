<?php

namespace Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Catalog\Http\Requests\StoreProductRequest;
use Modules\Catalog\Http\Requests\UpdateProductRequest;
use Modules\Catalog\Models\Size;
use Modules\Catalog\Models\TaxRate;
use Modules\Catalog\Models\Unit;
use Modules\Catalog\Services\ProductService;
use Modules\Frontend\Models\NavbarItem;
use Modules\Frontend\Models\SubnavbarItem;
use Modules\Store\Models\Store;

class ProductController extends Controller
{
    public function __construct(private ProductService $productService) {}

    /**
     * Parse a JSON string input into an array. If already an array, return as-is.
     */
    private function parseJsonArray($value): array
    {
        if (is_array($value)) {
            return $value;
        }
        if (is_string($value)) {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /**
     * Store context for product forms/tables. Only platform admins
     * (Super Admin / Admin) may see every store and assign a product
     * to one; store owners/staff are locked to their current store.
     */
    private function storeContext(): array
    {
        $actor = auth()->user();
        $canAssignStore = (bool) ($actor && ($actor->hasRole('Super Admin') || $actor->hasRole('Admin')));

        return [
            'stores' => $canAssignStore ? Store::orderBy('name')->get() : collect(),
            'canAssignStore' => $canAssignStore,
        ];
    }

    public function index(Request $request)
    {
        $brands = $this->productService->getBrands();
        $categories = $this->productService->getCategories();

        return view('catalog::index', compact('brands', 'categories') + $this->storeContext());
    }

    public function dataTable(Request $request)
    {
        return $this->productService->getProductDataTable($request);
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $brands = $this->productService->getBrands();
        $categories = $this->productService->getCategories();
        $units = Unit::where('status', 'active')->orderBy('name')->get();
        $sizes = Size::where('status', 'active')->orderBy('group_name')->get();
        $taxRates = TaxRate::where('status', 'active')->orderBy('name')->get();
        $navbarItems = NavbarItem::where('status', 'active')->orderBy('sort_order')->orderBy('name')->get();
        $subnavbarItems = collect();

        return view('catalog::products.create', compact('brands', 'categories', 'units', 'sizes', 'taxRates', 'navbarItems', 'subnavbarItems') + $this->storeContext());
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        $data['images'] = $request->file('images', []);
        $data['deleted_image_ids'] = $this->parseJsonArray($request->input('deleted_image_ids', []));
        $data['deleted_variant_ids'] = $this->parseJsonArray($request->input('deleted_variant_ids', []));

        $response = $this->productService->saveProduct($data);

        if ($request->ajax() || $request->wantsJson()) {
            return $response;
        }

        $payload = $response->getData(true);

        if (($payload['status'] ?? '') === 'success') {
            return redirect()->route('products.index')->with('success', $payload['message']);
        }

        return redirect()->back()->withInput()->with('error', $payload['message'] ?? 'Failed to save product.');
    }

    public function show($id)
    {
        return $this->productService->getProductById((int) $id);
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit($id)
    {
        $product = $this->productService->getProductModel((int) $id);
        $brands = $this->productService->getBrands();
        $categories = $this->productService->getCategories();
        $units = Unit::where('status', 'active')->orderBy('name')->get();
        $sizes = Size::where('status', 'active')->orderBy('group_name')->get();
        $taxRates = TaxRate::where('status', 'active')->orderBy('name')->get();
        $navbarItems = NavbarItem::where('status', 'active')->orderBy('sort_order')->orderBy('name')->get();
        $subnavbarItems = $product->navbar_item_id
            ? SubnavbarItem::where('navbar_item_id', $product->navbar_item_id)->where('status', 'active')->orderBy('sort_order')->orderBy('name')->get()
            : collect();

        return view('catalog::products.edit', compact('product', 'brands', 'categories', 'units', 'sizes', 'taxRates', 'navbarItems', 'subnavbarItems') + $this->storeContext());
    }

    public function update(UpdateProductRequest $request, $id)
    {
        $data = $request->validated();
        $data['product_id'] = $id;
        $data['images'] = $request->file('images', []);
        $data['deleted_image_ids'] = $this->parseJsonArray($request->input('deleted_image_ids', []));
        $data['deleted_variant_ids'] = $this->parseJsonArray($request->input('deleted_variant_ids', []));

        $response = $this->productService->saveProduct($data);

        if ($request->ajax() || $request->wantsJson()) {
            return $response;
        }

        $payload = $response->getData(true);

        if (($payload['status'] ?? '') === 'success') {
            return redirect()->route('products.index')->with('success', $payload['message']);
        }

        return redirect()->back()->withInput()->with('error', $payload['message'] ?? 'Failed to update product.');
    }

    public function destroy($id)
    {
        return $this->productService->deleteProduct((int) $id);
    }

    /**
     * Duplicate a product with an optional new name and price.
     */
    public function duplicate(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:220'],
            'price' => ['nullable', 'numeric', 'min:0'],
        ]);

        return $this->productService->duplicateProduct((int) $id, $validated);
    }

    public function search(Request $request)
    {
        $categoryId = $request->filled('category_id') ? (int) $request->input('category_id') : null;

        return $this->productService->searchProducts((string) $request->input('q', ''), $categoryId);
    }

    /**
     * Reorder products based on the provided array of product IDs.
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'required|integer|exists:products,id',
        ]);

        return $this->productService->reorderProducts($validated['product_ids']);
    }
}
