<?php

namespace Modules\Frontend\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Frontend\Models\NavbarItem;
use Modules\Frontend\Models\SubnavbarItem;
use Yajra\DataTables\DataTables;

class NavbarService
{
    public function getNavbarDataTable(Request $request)
    {
        $query = NavbarItem::query()->withCount('subnavbarItems')->orderByDesc('created_at');

        return DataTables::of($query)
            ->editColumn('status', function (NavbarItem $item) {
                return ucfirst($item->status);
            })
            ->editColumn('created_at', function (NavbarItem $item) {
                return $item->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (NavbarItem $item) {
                $subnavbarUrl = route('frontend.nav-items.subnavbar.index', ['navbar_item_id' => $item->id]);
                $subBtn = '<a href="' . $subnavbarUrl . '" class="bg-green-600 text-white px-2 py-1 rounded text-sm hover:bg-green-500 mr-2" title="Manage Subnavbars"><i class="fa fa-list"></i></a>';
                $editBtn = '<button onclick="navbar_itemEdit(' . $item->id . ')" class="bg-blue-900 text-white px-2 py-1 rounded text-sm hover:bg-blue-600 mr-2"><i class="fa fa-pencil"></i></button>';
                $deleteBtn = '<button onclick="navbar_itemDelete(' . $item->id . ')" class="bg-red-500 text-white px-2 py-1 rounded text-sm hover:bg-red-600"><i class="fa fa-trash"></i></button>';
                return '<div class="flex space-x-2 justify-center">' . $subBtn . $editBtn . $deleteBtn . '</div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function getSubnavbarDataTable(Request $request)
    {
        $query = SubnavbarItem::query()->with('navbarItem');

        // Filter by navbar_item_id if provided
        if ($request->filled('navbar_item_id')) {
            $query->where('navbar_item_id', $request->navbar_item_id);
        }

        $query->orderByDesc('created_at');

        return DataTables::of($query)
            ->addColumn('parent_navbar', function (SubnavbarItem $item) {
                return $item->navbarItem?->name ?? '-';
            })
            ->editColumn('status', function (SubnavbarItem $item) {
                return ucfirst($item->status);
            })
            ->editColumn('created_at', function (SubnavbarItem $item) {
                return $item->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (SubnavbarItem $item) {
                return view('components.action-buttons', [
                    'id' => $item->id,
                    'edit' => 'subnavbar_itemEdit',
                    'delete' => 'subnavbar_itemDelete',
                ])->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function saveNavbarItem(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                $itemId = $data['navbar_item_id'] ?? null;
                $data['sort_order'] = $data['sort_order'] ?? 0;
                $data['status'] = $data['status'] ?? 'active';
                unset($data['navbar_item_id']);

                if ($itemId) {
                    $item = NavbarItem::findOrFail($itemId);
                    $item->update($data);
                    $message = 'Navbar item updated successfully.';
                } else {
                    $item = NavbarItem::create($data);
                    $message = 'Navbar item created successfully.';
                }

                return [
                    'status' => 'success',
                    'message' => $message,
                    'navbar_item' => $item->fresh(),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error saving navbar item: ' . $e->getMessage(),
            ];
        }
    }

    public function saveSubnavbarItem(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                $itemId = $data['subnavbar_item_id'] ?? null;
                $data['sort_order'] = $data['sort_order'] ?? 0;
                $data['status'] = $data['status'] ?? 'active';
                unset($data['subnavbar_item_id']);

                if ($itemId) {
                    $item = SubnavbarItem::findOrFail($itemId);
                    $item->update($data);
                    $message = 'Subnavbar item updated successfully.';
                } else {
                    $item = SubnavbarItem::create($data);
                    $message = 'Subnavbar item created successfully.';
                }

                return [
                    'status' => 'success',
                    'message' => $message,
                    'subnavbar_item' => $item->fresh(),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error saving subnavbar item: ' . $e->getMessage(),
            ];
        }
    }

    public function getNavbarItemById(int $id): array
    {
        try {
            $item = NavbarItem::findOrFail($id);

            return [
                'status' => 'success',
                'navbar_item' => $item,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Navbar item not found.',
            ];
        }
    }

    public function getSubnavbarItemById(int $id): array
    {
        try {
            $item = SubnavbarItem::with('navbarItem')->findOrFail($id);

            return [
                'status' => 'success',
                'subnavbar_item' => $item,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Subnavbar item not found.',
            ];
        }
    }

    public function deleteNavbarItem(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $item = NavbarItem::findOrFail($id);
                $item->delete();

                return [
                    'status' => 'success',
                    'message' => 'Navbar item deleted successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error deleting navbar item: ' . $e->getMessage(),
            ];
        }
    }

    public function deleteSubnavbarItem(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $item = SubnavbarItem::findOrFail($id);
                $item->delete();

                return [
                    'status' => 'success',
                    'message' => 'Subnavbar item deleted successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error deleting subnavbar item: ' . $e->getMessage(),
            ];
        }
    }

    public function getAllNavbarItems(): array
    {
        return NavbarItem::where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->toArray();
    }

}
