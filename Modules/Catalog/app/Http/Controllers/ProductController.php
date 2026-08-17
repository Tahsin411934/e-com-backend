<?php

namespace Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Catalog\Models\Size;
use Modules\Catalog\Models\Unit;
use Modules\Catalog\Services\ProductService;
use Modules\Catalog\Http\Requests\StoreProductRequest;
use Modules\Catalog\Http\Requests\UpdateProductRequest;
use Modules\Frontend\Models\NavbarItem;
use Modules\Frontend\Models\SubnavbarItem;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

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

    public function index(Request $request)
    {
        $brands = $this->productService->getBrands();
        $categories = $this->productService->getCategories();
        return view('catalog::index', compact('brands', 'categories'));
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
        $taxRates = \Modules\Catalog\Models\TaxRate::where('status', 'active')->orderBy('name')->get();
        $navbarItems = NavbarItem::where('status', 'active')->orderBy('sort_order')->orderBy('name')->get();
        $subnavbarItems = collect();
        return view('catalog::products.create', compact('brands', 'categories', 'units', 'sizes', 'taxRates', 'navbarItems', 'subnavbarItems'));
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        $data['images'] = $request->file('images', []);
        $data['deleted_image_ids'] = $this->parseJsonArray($request->input('deleted_image_ids', []));
        $data['deleted_variant_ids'] = $this->parseJsonArray($request->input('deleted_variant_ids', []));
        $result = $this->productService->saveProduct($data);
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($result);
        }
        if ($result['status'] === 'success') {
            return redirect()->route('products.index')
                ->with('success', $result['message']);
        }
        return redirect()->back()->withInput()->with('error', $result['message']);
    }

    public function show($id)
    {
        $result = $this->productService->getProductById($id);
        return response()->json($result);
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit($id)
    {
        $result = $this->productService->getProductById($id);
        if ($result['status'] === 'error') {
            abort(404, $result['message']);
        }
        $product = $result['product'];
        $brands = $this->productService->getBrands();
        $categories = $this->productService->getCategories();
        $units = Unit::where('status', 'active')->orderBy('name')->get();
        $sizes = Size::where('status', 'active')->orderBy('group_name')->get();
        $taxRates = \Modules\Catalog\Models\TaxRate::where('status', 'active')->orderBy('name')->get();
        $navbarItems = NavbarItem::where('status', 'active')->orderBy('sort_order')->orderBy('name')->get();
        $subnavbarItems = $product->navbar_item_id
            ? SubnavbarItem::where('navbar_item_id', $product->navbar_item_id)->where('status', 'active')->orderBy('sort_order')->orderBy('name')->get()
            : collect();
       
        return view('catalog::products.edit', compact('product', 'brands', 'categories', 'units', 'sizes', 'taxRates', 'navbarItems', 'subnavbarItems'));
    }

    public function update(UpdateProductRequest $request, $id)
    {
        $data = $request->validated();
        $data['product_id'] = $id;
        $data['images'] = $request->file('images', []);
        $data['deleted_image_ids'] = $this->parseJsonArray($request->input('deleted_image_ids', []));
        $data['deleted_variant_ids'] = $this->parseJsonArray($request->input('deleted_variant_ids', []));
        $result = $this->productService->saveProduct($data);
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($result);
        }
        if ($result['status'] === 'success') {
            return redirect()->route('products.index')
                ->with('success', $result['message']);
        }
        return redirect()->back()->withInput()->with('error', $result['message']);
    }

    public function destroy($id)
    {
        $result = $this->productService->deleteProduct($id);
        return response()->json($result);
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

        $result = $this->productService->duplicateProduct($id, $validated);

        return response()->json($result, $result['status'] === 'success' ? 200 : 500);
    }

    public function search(Request $request)
    {
        $query = $request->input('q', '');
        $categoryId = $request->input('category_id');

        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Search query is required',
                'data' => [],
            ]);
        }

        $result = $this->productService->searchProducts($query, $categoryId);
        
        return response()->json([
            'success' => $result['status'] === 'success',
            'message' => $result['message'],
            'data' => $result['data'],
        ]);
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

        $result = $this->productService->reorderProducts($validated['product_ids']);

        return response()->json($result, $result['status'] === 'success' ? 200 : 500);
    }
}

