<?php

namespace Modules\Storefront\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\Models\Product;
use Modules\Frontend\Http\Resources\ProductDetailResource;
use Modules\Storefront\Support\StorefrontScope;

/**
 * Storefront product detail API.
 *
 * Same payload as the legacy /v1/products/{slug} endpoint (brand, categories,
 * gallery, variants, reviews, related products), but the product — and every
 * related product — must be visible on the resolved storefront.
 */
class ProductController extends Controller
{
    /**
     * Get a single product by slug for the product detail page.
     *
     * @urlParam slug string required The product slug (e.g. "iphone-15-pro-max")
     */
    public function show(string $slug, Request $request): JsonResponse
    {
        $query = Product::where('slug', $slug)
            ->where('status', 'active')
            ->where('visibility', 'public')
            ->with([
                'brand',
                'categories',
                'images' => function ($q) {
                    $q->orderBy('sort_order');
                },
                'variants' => function ($q) {
                    $q->whereNull('deleted_at')->where('status', 'active')->with(['inventoryStocks', 'options.inventoryStocks']);
                },
                'variants.images',
                'reviews' => function ($q) {
                    $q->where('status', 'approved')->with('user');
                },
            ]);

        StorefrontScope::apply($query);

        $product = $query->first();

        if (! $product) {
            return ApiResponse::notFound('Product not found.');
        }

        // Related products (same categories, excluding current) — tenant scoped too.
        $categoryIds = $product->categories->pluck('id')->toArray();
        $relatedIds = [];

        if (! empty($categoryIds)) {
            $relatedIds = DB::table('product_categories')
                ->whereIn('category_id', $categoryIds)
                ->where('product_id', '!=', $product->id)
                ->pluck('product_id')
                ->unique()
                ->shuffle()
                ->take(8)
                ->toArray();
        }

        $relatedProducts = collect();

        if (! empty($relatedIds)) {
            $relatedQuery = Product::whereIn('id', $relatedIds)
                ->where('status', 'active')
                ->where('visibility', 'public')
                ->with(['images', 'variants' => fn ($q) => $q->whereNull('deleted_at')->where('status', 'active')])
                ->orderBy('order_column');

            StorefrontScope::apply($relatedQuery);

            $relatedProducts = $relatedQuery->get();
        }

        $product->setRelation('relatedProducts', $relatedProducts);

        return ApiResponse::success(new ProductDetailResource($product), 'Product retrieved successfully.');
    }
}