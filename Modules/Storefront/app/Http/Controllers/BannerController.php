<?php

namespace Modules\Storefront\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Frontend\Http\Resources\BannerResource;
use Modules\Frontend\Models\Banner;
use Modules\Storefront\Support\StorefrontScope;

class BannerController extends Controller
{
    /**
     * Get all active banners for the current storefront.
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status', 'active');
        $perPage = $request->has('per_page') ? min((int) $request->query('per_page', 10), 100) : null;

        $query = Banner::where('status', $status)
            ->orderBy('sort_order')
            ->orderByDesc('created_at');

        StorefrontScope::apply($query);

        if ($perPage) {
            $data = $query->paginate($perPage);
        } else {
            $data = $query->get();
        }

        $response = [
            'items' => BannerResource::collection($data),
        ];

        if ($data instanceof LengthAwarePaginator) {
            $response['meta'] = [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
            ];
        }

        return ApiResponse::success($response, 'Banners retrieved successfully.');
    }

    /**
     * Get a single banner by ID that belongs to this storefront.
     */
    public function show($id): JsonResponse
    {
        $query = Banner::query();

        StorefrontScope::apply($query);

        $banner = $query->find($id);

        if (! $banner) {
            return ApiResponse::notFound('Banner not found.');
        }

        return ApiResponse::success(new BannerResource($banner), 'Banner retrieved successfully.');
    }
}
