<?php

namespace Modules\Storefront\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Catalog\Models\Brand;
use Modules\Storefront\Support\StorefrontScope;

class BrandController extends Controller
{
    /**
     * Get all active brands for the current storefront (filter dropdowns).
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('q');
        $limit = min((int) $request->query('limit', 20), 100);
        $page = max((int) $request->query('page', 1), 1);

        $query = Brand::where('status', 'active')
            ->orderBy('name');

        StorefrontScope::apply($query);

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $total = $query->count();
        $brands = $query->skip(($page - 1) * $limit)
            ->take($limit)
            ->get(['id', 'name', 'slug', 'logo_url']);

        $data = $brands->map(function ($brand) {
            return [
                'id' => $brand->id,
                'name' => $brand->name,
                'slug' => $brand->slug,
                'logo' => $brand->logo_url ? asset('storage/'.$brand->logo_url) : null,
            ];
        });

        return ApiResponse::success([
            'items' => $data,
            'meta' => [
                'current_page' => $page,
                'last_page' => (int) ceil($total / $limit),
                'per_page' => $limit,
                'total' => $total,
            ],
        ], 'Brands retrieved successfully.');
    }
}
