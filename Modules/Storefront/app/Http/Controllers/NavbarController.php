<?php

namespace Modules\Storefront\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Frontend\Http\Resources\NavbarItemResource;
use Modules\Frontend\Http\Resources\SubnavbarItemResource;
use Modules\Frontend\Models\NavbarItem;
use Modules\Storefront\Support\StorefrontScope;

class NavbarController extends Controller
{
    /**
     * Get all active navbar items with their subnavbar items — scoped to
     * the current storefront.
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

            StorefrontScope::apply($q);
        }])
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
        $query = NavbarItem::with(['subnavbarItems' => function ($q) {
            $q->where('status', 'active')->orderBy('sort_order')->orderBy('name');

            StorefrontScope::apply($q);
        }]);

        StorefrontScope::apply($query);

        $navbarItem = $query->find((int) $id);

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
        $query = NavbarItem::query();

        StorefrontScope::apply($query);

        $navbarItem = $query->find($navbarItemId);

        if (! $navbarItem) {
            return ApiResponse::notFound('Navbar item not found.');
        }

        $subnavbarQuery = $navbarItem->subnavbarItems()
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('name');

        StorefrontScope::apply($subnavbarQuery);

        $subnavbarItems = $subnavbarQuery->get();

        return ApiResponse::success(SubnavbarItemResource::collection($subnavbarItems), 'Subnavbar items retrieved successfully.');
    }
}
