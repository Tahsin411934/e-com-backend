<?php

namespace Modules\Storefront\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Frontend\Http\Resources\ProductSearchResource;
use Modules\Storefront\Services\ProductSearchService;

/**
 * Storefront product search.
 *
 * Reuses the Frontend module's fuzzy search algorithm (via the tenant-scoped
 * Storefront service), so only products visible on this storefront can ever
 * appear in the results.
 */
class ProductSearchController extends Controller
{
    /**
     * Search products with fuzzy matching.
     *
     * @queryParam q string required The search query (e.g. "smartphne")
     * @queryParam category_id int optional Filter by category ID
     * @queryParam per_page int Items per page. Default: 10, Max: 40
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|max:255',
            'category_id' => 'nullable|integer|exists:categories,id',
        ]);

        $query = $request->query('q', '');
        $categoryId = $request->query('category_id');
        $perPage = $request->has('per_page') ? min((int) $request->query('per_page', 10), 40) : 10;

        $searchResult = app(ProductSearchService::class)->search(
            $query,
            $perPage,
            $categoryId ? (int) $categoryId : null,
        );

        $products = $searchResult['products'];

        $response = [
            'query' => $query,
            'items' => $products->isEmpty() ? [] : ProductSearchResource::collection($products),
        ];

        if ($categoryId) {
            $response['category_id'] = (int) $categoryId;
        }

        if ($searchResult['suggestion']) {
            $response['suggestion'] = $searchResult['suggestion'];
        }

        return ApiResponse::success($response, $products->isEmpty() ? 'No products found.' : 'Products found.');
    }
}