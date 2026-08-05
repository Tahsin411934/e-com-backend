<?php

namespace Modules\Frontend\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Frontend\Models\Banner;
use Yajra\DataTables\DataTables;

class BannerService
{
    public function getBannerDataTable(Request $request)
    {
        $query = Banner::query()->orderByDesc('created_at');

        return DataTables::of($query)
            ->editColumn('banner_image', function (Banner $banner) {
                if ($banner->banner_image) {
                    $url = asset('storage/' . $banner->banner_image);
                    return '<img src="' . $url . '" alt="Banner" class="w-16 h-10 object-cover rounded" />';
                }
                return '-';
            })
            ->editColumn('status', function (Banner $banner) {
                return ucfirst($banner->status);
            })
            ->editColumn('created_at', function (Banner $banner) {
                return $banner->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (Banner $banner) {
                $editBtn = '<button onclick="bannerEdit(' . $banner->id . ')" class="bg-blue-900 text-white px-2 py-1 rounded text-sm hover:bg-blue-600 mr-2"><i class="fa fa-pencil"></i></button>';
                $deleteBtn = '<button onclick="bannerDelete(' . $banner->id . ')" class="bg-red-500 text-white px-2 py-1 rounded text-sm hover:bg-red-600"><i class="fa fa-trash"></i></button>';
                return '<div class="flex space-x-2 justify-center">' . $editBtn . $deleteBtn . '</div>';
            })
            ->rawColumns(['banner_image', 'action'])
            ->make(true);
    }

    public function saveBanner(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                $bannerId = $data['banner_id'] ?? null;
                $data['sort_order'] = $data['sort_order'] ?? 0;
                $data['status'] = $data['status'] ?? 'active';
                unset($data['banner_id']);

                // Handle image upload
                if (isset($data['banner_image']) && $data['banner_image'] instanceof \Illuminate\Http\UploadedFile) {
                    // Delete old image if updating
                    if ($bannerId) {
                        $oldBanner = Banner::find($bannerId);
                        if ($oldBanner && $oldBanner->banner_image) {
                            Storage::disk('public')->delete($oldBanner->banner_image);
                        }
                    }
                    $data['banner_image'] = $data['banner_image']->store('banners', 'public');
                } else {
                    // Keep existing image if no new file uploaded
                    unset($data['banner_image']);
                }

                if ($bannerId) {
                    $banner = Banner::findOrFail($bannerId);
                    $banner->update($data);
                    $message = 'Banner updated successfully.';
                } else {
                    $banner = Banner::create($data);
                    $message = 'Banner created successfully.';
                }

                return [
                    'status' => 'success',
                    'message' => $message,
                    'banner' => $banner->fresh(),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error saving banner: ' . $e->getMessage(),
            ];
        }
    }

    public function getBannerById(int $id): array
    {
        try {
            $banner = Banner::findOrFail($id);

            // Add full image URL for the form
            $bannerArray = $banner->toArray();
            if ($banner->banner_image) {
                $bannerArray['banner_image_url'] = asset('storage/' . $banner->banner_image);
            }

            return [
                'status' => 'success',
                'banner' => $bannerArray,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Banner not found.',
            ];
        }
    }

    public function deleteBanner(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $banner = Banner::findOrFail($id);

                // Delete image file
                if ($banner->banner_image) {
                    Storage::disk('public')->delete($banner->banner_image);
                }

                $banner->delete();

                return [
                    'status' => 'success',
                    'message' => 'Banner deleted successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error deleting banner: ' . $e->getMessage(),
            ];
        }
    }

}
