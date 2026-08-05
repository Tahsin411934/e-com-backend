<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Frontend\Http\Resources\BannerResource;
use Modules\Frontend\Models\Banner;

class BannerApiController extends Controller
{
    /**
     * Get all active banners.
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @queryParam status string Filter by status (active/inactive). Default: active
     * @queryParam per_page int Items per page for pagination. Default: all
     */
    public function index(Request $request): JsonResponse
    {
        $status   = $request->query('status', 'active');
        $perPage  = $request->has('per_page') ? min((int) $request->query('per_page', 10), 100) : null;

        $query = Banner::where('status', $status)
            ->orderBy('sort_order')
            ->orderByDesc('created_at');

        if ($perPage) {
            $data = $query->paginate($perPage);
        } else {
            $data = $query->get();
        }

        $response = [
            'success' => true,
            'message' => 'Banners retrieved successfully.',
            'data'    => BannerResource::collection($data),
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
     * Get a single banner by ID.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $banner = Banner::find($id);

        if (!$banner) {
            return response()->json([
                'success' => false,
                'message' => 'Banner not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Banner retrieved successfully.',
            'data'    => new BannerResource($banner),
        ]);
    }
}