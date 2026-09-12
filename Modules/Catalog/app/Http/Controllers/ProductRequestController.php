<?php

namespace Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Catalog\Http\Requests\ProductEnquiryRequest;
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

    public function store(ProductEnquiryRequest $request)
    {
        $validated = $request->validated();

        $validated['quantity'] = $validated['quantity'] ?? 1;
        $validated['status'] = $validated['status'] ?? 'pending';

        if ($request->hasFile('product_image')) {
            $validated['product_image'] = $request->file('product_image');
        }

        return $this->productRequestService->store($validated);
    }

    public function update(ProductEnquiryRequest $request, int $id)
    {
        $validated = $request->validated();

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
