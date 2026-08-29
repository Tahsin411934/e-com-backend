<?php

namespace Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Catalog\Services\ProductRequestService;

class ProductRequestController extends Controller
{
    public function __construct(private ProductRequestService $productRequestService) {}

    public function index()
    {
        return view('catalog::product-requests.index');
    }

    public function dataTable(Request $request)
    {
        return $this->productRequestService->getProductRequestDataTable($request);
    }

    public function show($id)
    {
        return $this->productRequestService->getProductRequestById((int) $id);
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

        return $this->productRequestService->store($validated);
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

        return $this->productRequestService->update($id, $validated);
    }

    public function destroy($id)
    {
        return $this->productRequestService->destroy((int) $id);
    }

    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,fulfilled',
        ]);

        return $this->productRequestService->updateStatus($id, $request->input('status'));
    }
}
