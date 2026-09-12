<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Catalog\Http\Requests\ProductEnquiryRequest;
use Modules\Catalog\Services\ProductRequestService;

class ProductRequestApiController extends Controller
{
    public function __construct(private ProductRequestService $productRequestService) {}

    /**
     * Store a product request from the frontend.
     */
    public function store(ProductEnquiryRequest $request): JsonResponse
    {
        $validated = $request->validated();

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
