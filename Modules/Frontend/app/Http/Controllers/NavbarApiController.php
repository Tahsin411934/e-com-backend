<?php

namespace Modules\Frontend\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
     *
     * @queryParam status string Filter by status (active/inactive). Default: active
     * @queryParam per_page int Items per page for pagination. Default: all
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status', 'active');
        $perPage = $request->has('per_page') ? min((int) $request->query('per_page', 10), 100) : null;

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
            'items' => NavbarItemResource::collection($data),
        ];

        if ($data instanceof LengthAwarePaginator) {
            $response['meta'] = [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
            ];
        }

        return ApiResponse::success($response, 'Navbar items retrieved successfully.');
    }

    /**
     * Get a single navbar item with its subnavbar items.
     */
    public function show($id): JsonResponse
    {
        $navbarItem = NavbarItem::with(['subnavbarItems' => function ($q) {
            $q->where('status', 'active')->orderBy('sort_order')->orderBy('name');
        }])->find((int) $id);

        if (! $navbarItem) {
            return ApiResponse::notFound('Navbar item not found.');
        }

        return ApiResponse::success(new NavbarItemResource($navbarItem), 'Navbar item retrieved successfully.');
    }

    /**
     * Get all active subnavbar items for a specific navbar item.
     */
    public function children(int $navbarItemId): JsonResponse
    {
        $navbarItem = NavbarItem::find($navbarItemId);

        if (! $navbarItem) {
            return ApiResponse::notFound('Navbar item not found.');
        }

        $subnavbarItems = NavbarItem::find($navbarItemId)?->subnavbarItems()
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return ApiResponse::success(SubnavbarItemResource::collection($subnavbarItems), 'Subnavbar items retrieved successfully.');
    }
}
