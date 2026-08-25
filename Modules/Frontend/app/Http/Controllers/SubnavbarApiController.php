<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Frontend\Models\SubnavbarItem;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductVariant;
use Modules\Frontend\Services\ProductPricingService;

class SubnavbarApiController extends Controller
{
    /**
     * Get products by subnavbar slug.
     *
     * @param string $slug
     * @param Request $request
     * @return JsonResponse
     *
     * @queryParam page int Page number. Default: 1
     * @queryParam per_page int Items per page. Default: 20
     * @queryParam sort string Sort order (latest, price_asc, price_desc, name). Default: latest
     */
    public function products(string $slug, Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 20), 40);
        $page    = (int) $request->query('page', 1);
        $sort    = $request->query('sort', 'latest');

        $subnavbarItem = SubnavbarItem::where('slug', $slug)->where('status', 'active')->first();

        if (!$subnavbarItem) {
            return response()->json([
                'success' => false,
                'message' => 'Subnavbar item not found.',
            ], 404);
        }

        $query = Product::where('status', 'active')
            ->where('visibility', 'public')
            ->where('subnavbar_item_id', $subnavbarItem->id);

        // Apply sorting
        switch ($sort) {
            case 'price_asc':
                $query->orderBy(
                    ProductVariant::selectRaw('COALESCE(MIN(sale_price), 0)')
                        ->whereColumn('product_id', 'products.id')
                );
                break;
            case 'price_desc':
                $query->orderByDesc(
                    ProductVariant::selectRaw('COALESCE(MIN(sale_price), 0)')
                        ->whereColumn('product_id', 'products.id')
                );
                break;
            case 'name':
                $query->orderBy('name');
                break;
            default:
                $query->orderByDesc('created_at');
                break;
        }

        $products = $query->with([
            'images' => function ($q) {
                $q->where('is_main', true);
            },
            'variants' => function ($q) {
                $q->where('status', 'active');
            },
        ])->paginate($perPage, [
            'products.id',
            'products.name',
            'products.slug',
            'products.short_description',
            'products.product_type',
            'products.status',
        ], page: $page);

        // Format products
        $pricing = app(ProductPricingService::class);
        $formatted = $products->map(function ($product) use ($pricing) {
            $priceInfo = $pricing->priceInfo($product);
            $mainImage = $product->images->first();

            return [
                'id'                => $product->id,
                'name'              => $product->name,
                'slug'              => $product->slug,
                'short_description' => $product->short_description,
                'main_image'        => $mainImage?->image_url,
                // Price after discount
                'price'             => $priceInfo['price'],
                'regular_price'     => $priceInfo['regular_price'],
                'discount_percent'  => $priceInfo['discount_percent'],
                'discount_amount'   => $priceInfo['discount_amount'],
                'has_discount'      => $priceInfo['has_discount'],
                'product_type'      => $product->product_type,
                'stock_status'      => 'in_stock',
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully.',
            'data'    => [
                'subnavbar' => [
                    'id'             => $subnavbarItem->id,
                    'navbar_item_id' => $subnavbarItem->navbar_item_id,
                    'name'           => $subnavbarItem->name,
                    'slug'           => $subnavbarItem->slug,
                    'description'    => null,
                    'image'          => null,
                ],
                'products'  => $formatted,
                'meta'      => [
                    'current_page' => $products->currentPage(),
                    'last_page'    => $products->lastPage(),
                    'per_page'     => $products->perPage(),
                    'total'        => $products->total(),
                ],
            ],
        ]);
    }
}