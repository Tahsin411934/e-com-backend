<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Frontend\Http\Resources\AnnouncementBarResource;
use Modules\Frontend\Models\AnnouncementBar;

class AnnouncementBarApiController extends Controller
{
    /**
     * Get all active announcement bars.
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
            'data'    => AnnouncementBarResource::collection($data),
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
     * Get a single announcement bar by ID.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $announcementBar = AnnouncementBar::find($id);

        if (!$announcementBar) {
            return response()->json([
                'success' => false,
                'message' => 'Announcement bar not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Announcement bar retrieved successfully.',
            'data'    => new AnnouncementBarResource($announcementBar),
        ]);
    }
}