<?php

namespace Modules\Storefront\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\Models\Category;
use Modules\Frontend\Http\Resources\CategoryResource;
use Modules\Frontend\Http\Resources\HomeProductResource;
use Modules\Storefront\Support\StorefrontScope;

/**
 * Storefront category API.
 *
 * Same response shapes as the legacy Frontend module endpoints, except every
 * query is scoped to the resolved tenant (store rows + global platform rows)
 * and category product counts only include products visible on this storefront.
 */
class CategoryController extends Controller
{
    /**
     * Get all categories (flat list) for the current storefront.
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status', 'active');
        $perPage = $request->has('per_page') ? min((int) $request->query('per_page', 10), 100) : null;

        $query = Category::withCount(['products' => fn (Builder $q) => StorefrontScope::apply($q)])
            ->where('status', $status)
            ->orderBy('sort_order')
            ->orderBy('name');

        StorefrontScope::apply($query);

        if ($perPage) {
            $data = $query->paginate($perPage);
        } else {
            $data = $query->get();
        }

        $response = [
            'items' => CategoryResource::collection($data),
        ];

        if ($data instanceof LengthAwarePaginator) {
            $response['meta'] = [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
            ];
        }

        return ApiResponse::success($response, 'Categories retrieved successfully.');
    }

    /**
     * Get a single category by slug (breadcrumbs, page titles, SEO metadata).
     */
    public function show(string $slug): JsonResponse
    {
        $query = Category::where('slug', $slug)
            ->where('status', 'active')
            ->withCount(['products' => fn (Builder $q) => StorefrontScope::apply($q)]);

        StorefrontScope::apply($query);

        $category = $query->first();

        if (! $category) {
            return ApiResponse::notFound('Category not found.');
        }

        return ApiResponse::success(new CategoryResource($category), 'Category retrieved successfully.');
    }

    /**
     * Get a single category by slug with its products (paginated).
     */
    public function products(string $slug, Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 20), 40);
        $sort = $request->query('sort', 'latest');
        $brandId = $request->query('brand_id');

        $categoryQuery = Category::where('slug', $slug)
            ->where('status', 'active')
            ->withCount(['products' => fn (Builder $q) => StorefrontScope::apply($q)]);

        StorefrontScope::apply($categoryQuery);

        $category = $categoryQuery->first();

        if (! $category) {
            return ApiResponse::notFound('Category not found.');
        }

        // Only products that belong to this storefront (or the platform catalog).
        $query = $category->products()
            ->where('products.status', 'active')
            ->where('products.visibility', 'public')
            ->with([
                'images',
                'variants' => function ($q) {
                    $q->where('status', 'active');
                },
            ]);

        StorefrontScope::apply($query);

        if ($brandId) {
            $query->where('products.brand_id', (int) $brandId);
        }

        // Apply sorting (order_column first, then secondary sort).
        // Price sorts use the discounted price so the order matches the UI.
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('products.order_column')->orderBy(
                    DB::raw('(SELECT MIN(sale_price * (1 - COALESCE(discount_percent, 0) / 100)) FROM product_variants WHERE product_variants.product_id = products.id AND product_variants.status = "active")'),
                    'asc'
                );
                break;
            case 'price_desc':
                $query->orderBy('products.order_column')->orderBy(
                    DB::raw('(SELECT MIN(sale_price * (1 - COALESCE(discount_percent, 0) / 100)) FROM product_variants WHERE product_variants.product_id = products.id AND product_variants.status = "active")'),
                    'desc'
                );
                break;
            case 'name':
                $query->orderBy('products.order_column')->orderBy('products.name');
                break;
            default: // latest
                $query->orderBy('products.order_column')->orderBy('products.published_at', 'desc');
        }

        $products = $query->paginate($perPage);

        $categoryImage = null;
        if ($category->image) {
            $categoryImage = filter_var($category->image, FILTER_VALIDATE_URL)
                ? $category->image
                : asset('storage/'.ltrim($category->image, '/'));
        } elseif ($category->image_url) {
            $categoryImage = $category->image_url;
        }

        return ApiResponse::success([
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'image' => $categoryImage,
                'parent_id' => $category->parent_id,
                'products_count' => $category->products_count,
            ],
            'products' => HomeProductResource::collection($products),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ], 'Category products retrieved successfully.');
    }
}
