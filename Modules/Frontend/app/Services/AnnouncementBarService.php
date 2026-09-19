<?php

namespace Modules\Frontend\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Frontend\Models\AnnouncementBar;
use Modules\Identity\Models\User;
use Yajra\DataTables\DataTables;

class AnnouncementBarService
{
    public function getAnnouncementBarDataTable(Request $request)
    {
        $query = AnnouncementBar::query()->with('store')->orderByDesc('created_at');

        // Store Owner → only their own store's bars. Platform staff → all bars,
        // optionally filtered by a specific store (or "global").
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
            ->addColumn('store_name', function (AnnouncementBar $bar) {
                return $bar->store?->name ?? 'Global (Platform)';
            })
            ->editColumn('left_text', function (AnnouncementBar $bar) {
                return $bar->left_text ?? '-';
            })
            ->editColumn('center_text', function (AnnouncementBar $bar) {
                return $bar->center_text ?? '-';
            })
            ->editColumn('right_text', function (AnnouncementBar $bar) {
                return $bar->right_text ?? '-';
            })
            ->editColumn('background_color', function (AnnouncementBar $bar) {
                return '<span class="inline-block w-6 h-6 rounded border" style="background-color: '.e($bar->background_color).'"></span> '
                    .e($bar->background_color);
            })
            ->editColumn('status', function (AnnouncementBar $bar) {
                return ucfirst($bar->status);
            })
            ->editColumn('created_at', function (AnnouncementBar $bar) {
                return $bar->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (AnnouncementBar $bar) {
                $editBtn = '<button class="js-crud-action" data-crud-action="edit" data-crud-callback="announcement_barEdit" data-crud-id="'.$bar->id.'" class="bg-blue-900 text-white px-2 py-1 rounded text-sm hover:bg-blue-600 mr-2"><i class="fa fa-pencil"></i></button>';
                $deleteBtn = '<button class="js-crud-action" data-crud-action="delete" data-crud-callback="announcement_barDelete" data-crud-id="'.$bar->id.'" class="bg-red-500 text-white px-2 py-1 rounded text-sm hover:bg-red-600"><i class="fa fa-trash"></i></button>';

                return '<div class="flex space-x-2 justify-center">'.$editBtn.$deleteBtn.'</div>';
            })
            ->rawColumns(['background_color', 'action'])
            ->make(true);
    }

    /**
     * Resolve the announcement bar scope for the acting user:
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
     * Ownership check for a single announcement bar.
     */
    private function accessDenied(AnnouncementBar $bar): bool
    {
        $ownedStoreId = $this->resolveStoreScope(auth()->user());

        return $ownedStoreId !== null && (int) $bar->store_id !== $ownedStoreId;
    }

    public function saveAnnouncementBar(array $data): JsonResponse
    {
        try {
            $ownedStoreId = $this->resolveStoreScope(auth()->user());

            if ($ownedStoreId === 0) {
                return ApiResponse::error('No store is associated with your account, so announcement bars cannot be managed.', 403);
            }

            return DB::transaction(function () use ($data, $ownedStoreId) {
                $barId = $data['announcement_bar_id'] ?? null;
                $data['sort_order'] = $data['sort_order'] ?? 0;
                $data['status'] = $data['status'] ?? 'active';
                unset($data['announcement_bar_id']);

                // Store assignment is always resolved server-side.
                if ($ownedStoreId !== null) {
                    // Store Owner: bar always belongs to their own store.
                    $data['store_id'] = $ownedStoreId;
                } else {
                    // Platform staff: explicit store selection, or null for a global bar.
                    $data['store_id'] = (isset($data['store_id']) && $data['store_id'] !== '' && $data['store_id'] !== null)
                        ? (int) $data['store_id']
                        : null;
                }

                $bar = null;

                if ($barId) {
                    $bar = AnnouncementBar::find($barId);

                    if (! $bar) {
                        return ApiResponse::notFound('Announcement bar not found.');
                    }

                    if ($this->accessDenied($bar)) {
                        return ApiResponse::error('You are not allowed to modify this announcement bar.', 403);
                    }

                    $bar->update($data);
                    $message = 'Announcement bar updated successfully.';
                } else {
                    $bar = AnnouncementBar::create($data);
                    $message = 'Announcement bar created successfully.';
                }

                return ApiResponse::success($bar->fresh(), $message);
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving announcement bar: '.$e->getMessage(), 500);
        }
    }

    public function getAnnouncementBarById(int $id): JsonResponse
    {
        try {
            $bar = AnnouncementBar::findOrFail($id);

            if ($this->accessDenied($bar)) {
                return ApiResponse::error('You are not allowed to view this announcement bar.', 403);
            }

            return ApiResponse::success($bar->toArray());
        } catch (\Exception $e) {
            return ApiResponse::notFound('Announcement bar not found.');
        }
    }

    public function deleteAnnouncementBar(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $bar = AnnouncementBar::findOrFail($id);

                if ($this->accessDenied($bar)) {
                    return ApiResponse::error('You are not allowed to delete this announcement bar.', 403);
                }

                $bar->delete();

                return ApiResponse::success(null, 'Announcement bar deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting announcement bar: '.$e->getMessage(), 500);
        }
    }
}
