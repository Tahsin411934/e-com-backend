<?php

namespace Modules\Frontend\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Frontend\Http\Resources\AnnouncementBarResource;
use Modules\Frontend\Models\AnnouncementBar;

class AnnouncementBarApiController extends Controller
{
    /**
     * Get all active announcement bars.
     *
     *
     * @queryParam status string Filter by status (active/inactive). Default: active
     * @queryParam per_page int Items per page for pagination. Default: all
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status', 'active');
        $perPage = $request->has('per_page') ? min((int) $request->query('per_page', 10), 100) : null;

        $query = AnnouncementBar::where('status', $status)
            ->orderBy('sort_order')
            ->orderByDesc('created_at');

        if ($perPage) {
            $data = $query->paginate($perPage);
        } else {
            $data = $query->get();
        }

        $response = [
            'success' => true,
            'message' => 'Announcement bars retrieved successfully.',
            'data' => AnnouncementBarResource::collection($data),
        ];

        if ($data instanceof LengthAwarePaginator) {
            $response['meta'] = [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
            ];
        }

        return ApiResponse::fromResult($response);
    }

    /**
     * Get a single announcement bar by ID.
     */
    public function show($id): JsonResponse
    {
        $announcementBar = AnnouncementBar::find($id);

        if (! $announcementBar) {
            return ApiResponse::fromResult([
                'success' => false,
                'message' => 'Announcement bar not found.',
            ], 200, 404);
        }

        return ApiResponse::fromResult([
            'success' => true,
            'message' => 'Announcement bar retrieved successfully.',
            'data' => new AnnouncementBarResource($announcementBar),
        ]);
    }
}
