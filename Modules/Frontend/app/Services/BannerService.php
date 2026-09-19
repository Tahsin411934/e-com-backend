<?php

namespace Modules\Frontend\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Frontend\Models\Banner;
use Modules\Identity\Models\User;
use Yajra\DataTables\DataTables;

class BannerService
{
    public function getBannerDataTable(Request $request)
    {
        $query = Banner::query()->with('store')->orderByDesc('created_at');

        // Store Owner → only their own store's banners. Platform staff → all
        // banners, optionally filtered by a specific store (or "global").
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
            ->editColumn('banner_image', function (Banner $banner) {
                if ($banner->banner_image) {
                    $url = asset('storage/'.$banner->banner_image);

                    return '<img src="'.$url.'" alt="Banner" class="w-16 h-10 object-cover rounded" />';
                }

                return '-';
            })
            ->addColumn('store_name', function (Banner $banner) {
                return $banner->store?->name ?? 'Global (Platform)';
            })
            ->editColumn('status', function (Banner $banner) {
                return ucfirst($banner->status);
            })
            ->editColumn('created_at', function (Banner $banner) {
                return $banner->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (Banner $banner) {
                $editBtn = '<button class="js-crud-action bg-blue-900 text-white px-2 py-1 rounded text-sm hover:bg-blue-600 mr-2" data-crud-action="edit" data-crud-callback="bannerEdit" data-crud-id="'.$banner->id.'"><i class="fa fa-pencil"></i></button>';
                $deleteBtn = '<button class="js-crud-action bg-red-500 text-white px-2 py-1 rounded text-sm hover:bg-red-600" data-crud-action="delete" data-crud-callback="bannerDelete" data-crud-id="'.$banner->id.'"><i class="fa fa-trash"></i></button>';

                return '<div class="flex space-x-2 justify-center">'.$editBtn.$deleteBtn.'</div>';
            })
            ->rawColumns(['banner_image', 'action'])
            ->make(true);
    }

    /**
     * Resolve the banner scope for the acting user:
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
     * Ownership check for a single banner. Returns true when the acting user
     * is restricted to a store that does not own the banner.
     */
    private function accessDenied(Banner $banner): bool
    {
        $ownedStoreId = $this->resolveStoreScope(auth()->user());

        return $ownedStoreId !== null && (int) $banner->store_id !== $ownedStoreId;
    }

    public function saveBanner(array $data): JsonResponse
    {
        try {
            $ownedStoreId = $this->resolveStoreScope(auth()->user());

            if ($ownedStoreId === 0) {
                return ApiResponse::error('No store is associated with your account, so banners cannot be managed.', 403);
            }

            return DB::transaction(function () use ($data, $ownedStoreId) {
                $bannerId = $data['banner_id'] ?? null;
                $data['sort_order'] = $data['sort_order'] ?? 0;
                $data['status'] = $data['status'] ?? 'active';
                unset($data['banner_id']);

                // Store assignment is always resolved server-side.
                if ($ownedStoreId !== null) {
                    // Store Owner: banner always belongs to their own store.
                    $data['store_id'] = $ownedStoreId;
                } else {
                    // Platform staff: explicit store selection, or null for a global banner.
                    $data['store_id'] = (isset($data['store_id']) && $data['store_id'] !== '' && $data['store_id'] !== null)
                        ? (int) $data['store_id']
                        : null;
                }

                $banner = null;

                if ($bannerId) {
                    $banner = Banner::find($bannerId);

                    if (! $banner) {
                        return ApiResponse::notFound('Banner not found.');
                    }

                    if ($this->accessDenied($banner)) {
                        return ApiResponse::error('You are not allowed to modify this banner.', 403);
                    }
                }

                // Handle image upload
                if (isset($data['banner_image']) && $data['banner_image'] instanceof UploadedFile) {
                    // Delete old image if updating
                    if ($banner && $banner->banner_image) {
                        Storage::disk('public')->delete($banner->banner_image);
                    }
                    $data['banner_image'] = $data['banner_image']->store('banners', 'public');
                } else {
                    // Keep existing image if no new file uploaded
                    unset($data['banner_image']);
                }

                if ($banner) {
                    $banner->update($data);
                    $message = 'Banner updated successfully.';
                } else {
                    $banner = Banner::create($data);
                    $message = 'Banner created successfully.';
                }

                return ApiResponse::success($banner->fresh(), $message);
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving banner: '.$e->getMessage(), 500);
        }
    }

    public function getBannerById(int $id): JsonResponse
    {
        try {
            $banner = Banner::findOrFail($id);

            if ($this->accessDenied($banner)) {
                return ApiResponse::error('You are not allowed to view this banner.', 403);
            }

            // Add full image URL for the form
            $bannerArray = $banner->toArray();
            if ($banner->banner_image) {
                $bannerArray['banner_image_url'] = filter_var($banner->banner_image, FILTER_VALIDATE_URL)
                    ? $banner->banner_image
                    : asset('storage/'.preg_replace('#^storage/#', '', ltrim($banner->banner_image, '/')));
            }

            return ApiResponse::success($bannerArray);
        } catch (\Exception $e) {
            return ApiResponse::notFound('Banner not found.');
        }
    }

    public function deleteBanner(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $banner = Banner::findOrFail($id);

                if ($this->accessDenied($banner)) {
                    return ApiResponse::error('You are not allowed to delete this banner.', 403);
                }

                // Delete image file
                if ($banner->banner_image) {
                    Storage::disk('public')->delete($banner->banner_image);
                }

                $banner->delete();

                return ApiResponse::success(null, 'Banner deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting banner: '.$e->getMessage(), 500);
        }
    }
}
