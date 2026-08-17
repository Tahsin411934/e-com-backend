<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Catalog\Models\Product;
use Modules\Frontend\Http\Resources\ProductDetailResource;

class ProductApiController extends Controller
{
    /**
     * Get a single product by slug for the product detail page.
     *
     * Returns comprehensive product data:
     * - Full product info (name, description, pricing)
     * - Brand details
     * - Category tree
     * - Image gallery (all images)
     * - Variants (with prices, SKUs, stock)
     * - Reviews (approved only) with user info, average rating, distribution
     * - Related products (same categories)
     *
     * @param string $slug
     * @param Request $request
     * @return JsonResponse
     *
     * @urlParam slug string required The product slug (e.g. "iphone-15-pro-max")
     */
    public function show(string $slug, Request $request): JsonResponse
    {
        $product = Product::where('slug', $slug)
            ->where('status', 'active')
            ->where('visibility', 'public')
            ->with([
                'brand',
                'categories',
                'images' => function ($q) {
                    $q->orderBy('sort_order');
                },
                'variants' => function ($q) {
                    $q->where('status', 'active')->with(['inventoryStocks', 'options']);
                },
                'variants.images',
                'reviews' => function ($q) {
                    $q->where('status', 'approved')->with('user');
                },
            ])
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        // Fetch related products separately (same categories, excluding current)
        $categoryIds = $product->categories->pluck('id')->toArray();
        $relatedIds = [];

        if (!empty($categoryIds)) {
            $relatedIds = \Illuminate\Support\Facades\DB::table('product_categories')
                ->whereIn('category_id', $categoryIds)
                ->where('product_id', '!=', $product->id)
                ->pluck('product_id')
                ->unique()
                ->shuffle()
                ->take(8)
                ->toArray();
        }

        $relatedProducts = collect();
        if (!empty($relatedIds)) {
            $relatedProducts = Product::whereIn('id', $relatedIds)
                ->where('status', 'active')
                ->where('visibility', 'public')
                ->with(['images', 'variants' => fn($q) => $q->where('status', 'active')])
                ->orderBy('order_column')
                ->get();
        }

        // Manually set relatedProducts for the resource
        $product->setRelation('relatedProducts', $relatedProducts);

        return response()->json([
            'success' => true,
            'message' => 'Product retrieved successfully.',
            'data'    => new ProductDetailResource($product),
        ]);
    }
}