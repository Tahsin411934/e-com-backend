<?php

namespace Modules\Storefront\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Catalog\Http\Requests\ProductEnquiryRequest;
use Modules\Catalog\Services\ProductRequestService;
use Modules\Store\Support\CurrentStore;

/**
 * Storefront product request (customer enquiry) submission.
 *
 * Same request/response contract as the legacy endpoint, except the enquiry is
 * stamped with the resolved tenant store id — never with a client-supplied one.
 */
class ProductRequestController extends Controller
{
    public function __construct(private ProductRequestService $productRequestService) {}

    /**
     * Store a product request submitted from this storefront.
     */
    public function store(ProductEnquiryRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if ($request->user()) {
            $validated['user_id'] = $request->user()->id;
        }

        // Tenant scope comes from the resolved storefront host, not the payload.
        $validated['store_id'] = CurrentStore::id();
        $validated['quantity'] = $validated['quantity'] ?? 1;
        $validated['status'] = 'pending';

        if ($request->hasFile('product_image')) {
            $validated['product_image'] = $request->file('product_image');
        }

        return $this->productRequestService->storeFromFrontend($validated);
    }
}