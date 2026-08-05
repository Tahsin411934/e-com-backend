<?php

namespace Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Catalog\Models\ProductRequest;
use Modules\Catalog\Services\ProductRequestService;

class ProductRequestController extends Controller
{
    protected ProductRequestService $productRequestService;

    public function __construct(ProductRequestService $productRequestService)
    {
        $this->productRequestService = $productRequestService;
    }

    public function index()
    {
        return view('catalog::product-requests.index');
    }

    public function dataTable(Request $request)
    {
        return $this->productRequestService->getProductRequestDataTable($request);
    }

    public function show(int $id)
    {
        $result = $this->productRequestService->getProductRequestById($id);
        return response()->json($result);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:160',
            'customer_email' => 'required|email|max:160',
            'customer_phone' => 'nullable|string|max:30',
            'product_name' => 'required|string|max:220',
            'product_description' => 'nullable|string|max:2000',
            'product_image' => 'nullable|image|max:5120',
            'quantity' => 'nullable|integer|min:1',
            'expected_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
            'status' => 'nullable|in:pending,approved,rejected,fulfilled',
        ]);

        $validated['quantity'] = $validated['quantity'] ?? 1;
        $validated['status'] = $validated['status'] ?? 'pending';

        if ($request->hasFile('product_image')) {
            $validated['product_image'] = $request->file('product_image');
        }

        $result = $this->productRequestService->store($validated);
        return response()->json($result, $result['status'] === 'success' ? 201 : 500);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:160',
            'customer_email' => 'required|email|max:160',
            'customer_phone' => 'nullable|string|max:30',
            'product_name' => 'required|string|max:220',
            'product_description' => 'nullable|string|max:2000',
            'product_image' => 'nullable|image|max:5120',
            'quantity' => 'nullable|integer|min:1',
            'expected_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
            'status' => 'nullable|in:pending,approved,rejected,fulfilled',
        ]);

        $validated['quantity'] = $validated['quantity'] ?? 1;

        if ($request->hasFile('product_image')) {
            $validated['product_image'] = $request->file('product_image');
        }

        $result = $this->productRequestService->update($id, $validated);
        return response()->json($result);
    }

    public function destroy(int $id)
    {
        $result = $this->productRequestService->destroy($id);
        return response()->json($result);
    }

    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,fulfilled',
        ]);

        $result = $this->productRequestService->updateStatus($id, $request->input('status'));
        return response()->json($result);
    }
}
