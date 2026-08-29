<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Catalog\Services\ProductRequestService;

class ProductRequestApiController extends Controller
{
    public function __construct(private ProductRequestService $productRequestService) {}

    /**
     * Store a product request from the frontend.
     */
    public function store(Request $request): JsonResponse
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
        ]);

        // If user is logged in, attach their ID
        if ($request->user()) {
            $validated['user_id'] = $request->user()->id;
        }

        $validated['quantity'] = $validated['quantity'] ?? 1;
        $validated['status'] = 'pending';

        if ($request->hasFile('product_image')) {
            $validated['product_image'] = $request->file('product_image');
        }

        return $this->productRequestService->storeFromFrontend($validated);
    }
}
