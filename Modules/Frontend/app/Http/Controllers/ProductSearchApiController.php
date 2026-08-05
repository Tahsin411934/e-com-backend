<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Frontend\Http\Resources\ProductSearchResource;
use Modules\Frontend\Services\ProductSearchService;

class ProductSearchApiController extends Controller
{
    /**
     * Search products with fuzzy matching.
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @queryParam q string required The search query (e.g. "smartphne" or "ipad")
     * @queryParam category_id int optional Filter by category ID
     * @queryParam per_page int Items per page. Default: 10, Max: 40
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q'           => 'required|string|max:255',
            'category_id' => 'nullable|integer|exists:categories,id',
        ]);

        $query      = $request->query('q', '');
        $categoryId = $request->query('category_id');
        $perPage    = $request->has('per_page') ? min((int) $request->query('per_page', 10), 40) : 10;

        $service = app(ProductSearchService::class);
        $searchResult = $service->search($query, $perPage, $categoryId ? (int) $categoryId : null);

        $products = $searchResult['products'];

        $response = [
            'success' => true,
            'message' => $products->isEmpty() ? 'No products found.' : 'Products found.',
            'data'    => $products->isEmpty() ? [] : ProductSearchResource::collection($products),
            'query'   => $query,
        ];

        if ($categoryId) {
            $response['category_id'] = (int) $categoryId;
        }

        if ($searchResult['suggestion']) {
            $response['suggestion'] = $searchResult['suggestion'];
        }

        return response()->json($response);
    }
}