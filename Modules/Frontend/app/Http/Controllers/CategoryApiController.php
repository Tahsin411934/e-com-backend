<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Catalog\Models\Category;
use Modules\Frontend\Http\Resources\CategoryResource;
use Modules\Frontend\Http\Resources\HomeProductResource;

class CategoryApiController extends Controller
{
    /**
     * Get all categories (flat list).
     *
     * Returns both parent and child categories together in one flat array.
     * The frontend can use the parent_id field to build the hierarchy as needed.
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @queryParam status string Filter by status (active/inactive). Default: active
     * @queryParam per_page int Items per page for pagination. Default: all
     */
    public function index(Request $request): JsonResponse
    {
        $status  = $request->query('status', 'active');
        $perPage = $request->has('per_page') ? min((int) $request->query('per_page', 10), 100) : null;

        $query = Category::withCount('products')
            ->where('status', $status)
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($perPage) {
            $data = $query->paginate($perPage);
        } else {
            $data = $query->get();
        }

        $response = [
            'success' => true,
            'message' => 'Categories retrieved successfully.',
            'data'    => CategoryResource::collection($data),
        ];

        if ($data instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
            $response['meta'] = [
                'current_page' => $data->currentPage(),
                'last_page'    => $data->lastPage(),
                'per_page'     => $data->perPage(),
                'total'        => $data->total(),
            ];
        }

        return response()->json($response);
    }

    /**
     * Get a single category by slug.
     *
     * Returns the category info without products.
     * Useful for breadcrumbs, page titles, and SEO metadata.
     *
     * @param string $slug
     * @return JsonResponse
     *
     * @urlParam slug string required The category slug (e.g. "electronics")
     */
    public function show(string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)
            ->where('status', 'active')
            ->withCount('products')
            ->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Category retrieved successfully.',
            'data'    => new CategoryResource($category),
        ]);
    }

    /**
     * Get a single category by slug with its products (paginated).
     *
     * Use this for the "Products by Category" page.
     * The frontend receives the category info along with a paginated list of products.
     *
     * @param string $slug
     * @param Request $request
     * @return JsonResponse
     *
     * @urlParam slug string required The category slug (e.g. "electronics")
     * @queryParam per_page int Items per page. Default: 20, Max: 40
     * @queryParam sort string Sort by: "latest", "price_asc", "price_desc", "name". Default: "latest"
     * @queryParam brand_id int Filter by brand ID
     */
    public function products(string $slug, Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 20), 40);
        $sort    = $request->query('sort', 'latest');
        $brandId = $request->query('brand_id');

        // Find category by slug
        $category = Category::where('slug', $slug)
            ->where('status', 'active')
            ->withCount('products')
            ->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.',
            ], 404);
        }

        // Get products for this category
        $query = $category->products()
            ->where('status', 'active')
            ->where('visibility', 'public')
            ->with([
                'images',
                'variants' => function ($q) {
                    $q->where('status', 'active');
                },
            ]);

        // Apply brand filter
        if ($brandId) {
            $query->where('brand_id', (int) $brandId);
        }

        // Apply sorting
        switch ($sort) {
            case 'price_asc':
                $query->orderBy(
                    \DB::raw('(SELECT MIN(sale_price) FROM product_variants WHERE product_variants.product_id = products.id AND product_variants.status = "active")'),
                    'asc'
                );
                break;
            case 'price_desc':
                $query->orderBy(
                    \DB::raw('(SELECT MIN(sale_price) FROM product_variants WHERE product_variants.product_id = products.id AND product_variants.status = "active")'),
                    'desc'
                );
                break;
            case 'name':
                $query->orderBy('name');
                break;
            default: // latest
                $query->orderBy('published_at', 'desc');
        }

        $products = $query->paginate($perPage);

        // Build category data
        $categoryImage = null;
        if ($category->image) {
            $categoryImage = asset('storage/' . $category->image);
        } elseif ($category->image_url) {
            $categoryImage = $category->image_url;
        }

        $categoryData = [
            'id'             => $category->id,
            'name'           => $category->name,
            'slug'           => $category->slug,
            'description'    => $category->description,
            'image'          => $categoryImage,
            'parent_id'      => $category->parent_id,
            'products_count' => $category->products_count,
        ];

        return response()->json([
            'success'  => true,
            'message'  => 'Category products retrieved successfully.',
            'data'     => $categoryData,
            'products' => HomeProductResource::collection($products),
            'meta'     => [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'per_page'     => $products->perPage(),
                'total'        => $products->total(),
            ],
        ]);
    }
}