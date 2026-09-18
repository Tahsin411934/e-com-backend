<?php

namespace Modules\Frontend\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Frontend\Models\NavbarItem;
use Modules\Frontend\Models\SubnavbarItem;
use Modules\Identity\Models\User;
use Yajra\DataTables\DataTables;

class NavbarService
{
    public function getNavbarDataTable(Request $request)
    {
        $query = NavbarItem::query()->with('store')->withCount('subnavbarItems')->orderByDesc('created_at');

        // Store Owner → only their own store's navbar items. Platform staff →
        // all items, optionally filtered by a specific store (or "global").
        $ownedStoreId = $this->resolveStoreScope($request->user());

        if ($ownedStoreId !== null) {
            $query->where('store_id', $ownedStoreId);
        } elseif ($request->filled('store_id')) {
            $filter = $request->input('store_id');

            if ($filter === 'global') {
                $query->whereNull('store_id');
            } elseif (is_numeric($filter)) {
                $query->where('store_id', (int) $filter);
            }
        }

        return DataTables::of($query)
            ->addColumn('store_name', function (NavbarItem $item) {
                return $item->store?->name ?? 'Global (Platform)';
            })
            ->editColumn('status', function (NavbarItem $item) {
                return ucfirst($item->status);
            })
            ->editColumn('created_at', function (NavbarItem $item) {
                return $item->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (NavbarItem $item) {
                $subnavbarUrl = route('frontend.nav-items.subnavbar.index', ['navbar_item_id' => $item->id]);
                $subBtn = '<a href="'.$subnavbarUrl.'" class="bg-green-600 text-white px-2 py-1 rounded text-sm hover:bg-green-500 mr-2" title="Manage Subnavbars"><i class="fa fa-list"></i></a>';
                $editBtn = '<button onclick="navbar_itemEdit('.$item->id.')" class="bg-blue-900 text-white px-2 py-1 rounded text-sm hover:bg-blue-600 mr-2"><i class="fa fa-pencil"></i></button>';
                $deleteBtn = '<button onclick="navbar_itemDelete('.$item->id.')" class="bg-red-500 text-white px-2 py-1 rounded text-sm hover:bg-red-600"><i class="fa fa-trash"></i></button>';

                return '<div class="flex space-x-2 justify-center">'.$subBtn.$editBtn.$deleteBtn.'</div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function getSubnavbarDataTable(Request $request)
    {
        $query = SubnavbarItem::query()->with('navbarItem.store');

        // Filter by navbar_item_id if provided
        if ($request->filled('navbar_item_id')) {
            $query->where('navbar_item_id', $request->navbar_item_id);
        }

        // Subnavbar items inherit their parent navbar item's store scope.
        $ownedStoreId = $this->resolveStoreScope($request->user());

        if ($ownedStoreId !== null) {
            $query->whereHas('navbarItem', function ($q) use ($ownedStoreId) {
                $q->where('store_id', $ownedStoreId);
            });
        } elseif ($request->filled('store_id')) {
            $filter = $request->input('store_id');

            if ($filter === 'global') {
                $query->whereHas('navbarItem', fn ($q) => $q->whereNull('store_id'));
            } elseif (is_numeric($filter)) {
                $query->whereHas('navbarItem', fn ($q) => $q->where('store_id', (int) $filter));
            }
        }

        $query->orderByDesc('created_at');

        return DataTables::of($query)
            ->addColumn('parent_navbar', function (SubnavbarItem $item) {
                return $item->navbarItem?->name ?? '-';
            })
            ->addColumn('store_name', function (SubnavbarItem $item) {
                return $item->navbarItem?->store?->name ?? 'Global (Platform)';
            })
            ->editColumn('status', function (SubnavbarItem $item) {
                return ucfirst($item->status);
            })
            ->editColumn('created_at', function (SubnavbarItem $item) {
                return $item->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (SubnavbarItem $item) {
                return view('components.action-buttons', [
                'permission' => 'frontend.navbar',
                'entityLabel' => 'Frontend.navbar',
                    'id' => $item->id,
                    'edit' => 'subnavbar_itemEdit',
                    'delete' => 'subnavbar_itemDelete',
                ])->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Resolve the navbar scope for the acting user:
     *  - Store Owner (with a store)  → their owned store id
     *  - Store Owner (without store) → 0 (matches nothing)
     *  - Everyone else (platform)    → null (unrestricted)
     */
    private function resolveStoreScope(?User $user): ?int
    {
        if (! $user || ! $user->isStoreOwner()) {
            return null;
        }

        $store = $user->ownedStore()->first(['id']);

        return $store ? (int) $store->id : 0;
    }

    /**
     * Ownership check for a single navbar item. Returns true when the acting
     * user is restricted to a store that does not own the item.
     */
    private function navbarItemAccessDenied(NavbarItem $item): bool
    {
        $ownedStoreId = $this->resolveStoreScope(auth()->user());

        return $ownedStoreId !== null && (int) $item->store_id !== $ownedStoreId;
    }

    /**
     * Ownership check for a subnavbar item — derived from its parent navbar item.
     */
    private function subnavbarItemAccessDenied(SubnavbarItem $item): bool
    {
        $ownedStoreId = $this->resolveStoreScope(auth()->user());

        return $ownedStoreId !== null && (int) ($item->navbarItem?->store_id) !== $ownedStoreId;
    }

    /**
     * Ownership check for a parent navbar item id (used when creating/moving
     * subnavbar items). Returns true when the acting owner does not own it.
     */
    private function parentNavbarAccessDenied(int $navbarItemId): bool
    {
        $ownedStoreId = $this->resolveStoreScope(auth()->user());

        if ($ownedStoreId === null) {
            return false;
        }

        $parent = NavbarItem::find($navbarItemId);

        return ! $parent || (int) $parent->store_id !== $ownedStoreId;
    }

    public function saveNavbarItem(array $data): JsonResponse
    {
        try {
            $ownedStoreId = $this->resolveStoreScope(auth()->user());

            if ($ownedStoreId === 0) {
                return ApiResponse::error('No store is associated with your account, so navbar items cannot be managed.', 403);
            }

            return DB::transaction(function () use ($data, $ownedStoreId) {
                $itemId = $data['navbar_item_id'] ?? null;
                $data['sort_order'] = $data['sort_order'] ?? 0;
                $data['status'] = $data['status'] ?? 'active';

                // Store assignment is always resolved server-side.
                if ($ownedStoreId !== null) {
                    // Store Owner: navbar item always belongs to their own store.
                    $data['store_id'] = $ownedStoreId;
                } else {
                    // Platform staff: explicit store selection, or null for a global item.
                    $data['store_id'] = (isset($data['store_id']) && $data['store_id'] !== '' && $data['store_id'] !== null)
                        ? (int) $data['store_id']
                        : null;
                }

                $item = null;

                if ($itemId) {
                    $item = NavbarItem::find($itemId);

                    if (! $item) {
                        return ApiResponse::notFound('Navbar item not found.');
                    }

                    if ($this->navbarItemAccessDenied($item)) {
                        return ApiResponse::error('You are not allowed to modify this navbar item.', 403);
                    }

                    unset($data['navbar_item_id']);
                    $item->update($data);
                    $message = 'Navbar item updated successfully.';
                } else {
                    unset($data['navbar_item_id']);
                    $item = NavbarItem::create($data);
                    $message = 'Navbar item created successfully.';
                }

                return ApiResponse::success($item->fresh(), $message);
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving navbar item: '.$e->getMessage(), 500);
        }
    }

    public function saveSubnavbarItem(array $data): JsonResponse
    {
        try {
            $ownedStoreId = $this->resolveStoreScope(auth()->user());

            if ($ownedStoreId === 0) {
                return ApiResponse::error('No store is associated with your account, so subnavbar items cannot be managed.', 403);
            }

            return DB::transaction(function () use ($data, $ownedStoreId) {
                $itemId = $data['subnavbar_item_id'] ?? null;
                $data['sort_order'] = $data['sort_order'] ?? 0;
                $data['status'] = $data['status'] ?? 'active';
                unset($data['subnavbar_item_id']);

                // A subnavbar item always lives under a parent navbar item —
                // owners may only attach to navbar items of their own store.
                if ($ownedStoreId !== null && $this->parentNavbarAccessDenied((int) $data['navbar_item_id'])) {
                    return ApiResponse::error('The selected parent navbar item does not belong to your store.', 403);
                }

                if ($itemId) {
                    $item = SubnavbarItem::with('navbarItem')->find($itemId);

                    if (! $item) {
                        return ApiResponse::notFound('Subnavbar item not found.');
                    }

                    if ($this->subnavbarItemAccessDenied($item)) {
                        return ApiResponse::error('You are not allowed to modify this subnavbar item.', 403);
                    }

                    $item->update($data);
                    $message = 'Subnavbar item updated successfully.';
                } else {
                    $item = SubnavbarItem::create($data);
                    $message = 'Subnavbar item created successfully.';
                }

                return ApiResponse::success($item->fresh(), $message);
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving subnavbar item: '.$e->getMessage(), 500);
        }
    }

    public function getNavbarItemById(int $id): JsonResponse
    {
        try {
            $item = NavbarItem::findOrFail($id);

            if ($this->navbarItemAccessDenied($item)) {
                return ApiResponse::error('You are not allowed to view this navbar item.', 403);
            }

            return ApiResponse::success($item);
        } catch (\Exception $e) {
            return ApiResponse::notFound('Navbar item not found.');
        }
    }

    public function getSubnavbarItemById(int $id): JsonResponse
    {
        try {
            $item = SubnavbarItem::with('navbarItem')->findOrFail($id);

            if ($this->subnavbarItemAccessDenied($item)) {
                return ApiResponse::error('You are not allowed to view this subnavbar item.', 403);
            }

            return ApiResponse::success($item);
        } catch (\Exception $e) {
            return ApiResponse::notFound('Subnavbar item not found.');
        }
    }

    public function deleteNavbarItem(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $item = NavbarItem::findOrFail($id);

                if ($this->navbarItemAccessDenied($item)) {
                    return ApiResponse::error('You are not allowed to delete this navbar item.', 403);
                }

                $item->delete();

                return ApiResponse::success(null, 'Navbar item deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting navbar item: '.$e->getMessage(), 500);
        }
    }

    public function deleteSubnavbarItem(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $item = SubnavbarItem::with('navbarItem')->findOrFail($id);

                if ($this->subnavbarItemAccessDenied($item)) {
                    return ApiResponse::error('You are not allowed to delete this subnavbar item.', 403);
                }

                $item->delete();

                return ApiResponse::success(null, 'Subnavbar item deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting subnavbar item: '.$e->getMessage(), 500);
        }
    }

    public function getAllNavbarItems()
    {
        $query = NavbarItem::where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('name');

        // Store Owner → only their own store's items (used by the parent dropdown).
        $ownedStoreId = $this->resolveStoreScope(auth()->user());

        if ($ownedStoreId !== null) {
            $query->where('store_id', $ownedStoreId);
        }

        return $query->get()->toArray();
    }
}
