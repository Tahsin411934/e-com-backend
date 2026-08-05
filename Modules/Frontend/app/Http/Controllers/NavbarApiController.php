<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Frontend\Http\Resources\NavbarItemResource;
use Modules\Frontend\Http\Resources\SubnavbarItemResource;
use Modules\Frontend\Models\NavbarItem;

class NavbarApiController extends Controller
{
    /**
     * Get all active navbar items with their subnavbar items.
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

        $query = NavbarItem::with(['subnavbarItems' => function ($q) use ($status) {
            if ($status) {
                $q->where('status', $status);
            }
            $q->orderBy('sort_order')->orderBy('name');
        }])
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
            'message' => 'Navbar items retrieved successfully.',
            'data'    => NavbarItemResource::collection($data),
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
     * Get a single navbar item with its subnavbar items.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $navbarItem = NavbarItem::with(['subnavbarItems' => function ($q) {
            $q->where('status', 'active')->orderBy('sort_order')->orderBy('name');
        }])->find($id);

        if (!$navbarItem) {
            return response()->json([
                'success' => false,
                'message' => 'Navbar item not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Navbar item retrieved successfully.',
            'data'    => new NavbarItemResource($navbarItem),
        ]);
    }

    /**
     * Get all active subnavbar items for a specific navbar item.
     *
     * @param int $navbarItemId
     * @return JsonResponse
     */
    public function children(int $navbarItemId): JsonResponse
    {
        $navbarItem = NavbarItem::find($navbarItemId);

        if (!$navbarItem) {
            return response()->json([
                'success' => false,
                'message' => 'Navbar item not found.',
            ], 404);
        }

        $subnavbarItems = NavbarItem::find($navbarItemId)?->subnavbarItems()
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Subnavbar items retrieved successfully.',
            'data'    => SubnavbarItemResource::collection($subnavbarItems),
        ]);
    }
}