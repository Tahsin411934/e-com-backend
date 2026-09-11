<?php

namespace Modules\Catalog\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Catalog\Models\Brand;
use Modules\Store\Support\CurrentStore;
use Yajra\DataTables\DataTables;

class BrandService
{
    public function getBrandDataTable(Request $request)
    {
        // Categories & brands are global reference data: everyone
        // (platform staff + SaaS store owners) sees the full list.
        // Only administrators may edit or delete them.
        $query = Brand::query()->with('store')->orderByDesc('created_at');

        if ($request->store_id) {
            $query->where('store_id', $request->store_id);
        }

        return DataTables::of($query)
            ->addColumn('logo', function (Brand $brand) {
                if (! $brand->logo_url) {
                    return '-';
                }

                return '<img src="'.e($this->logoUrl($brand->logo_url)).'" alt="'.e($brand->name).'" class="h-10 w-10 rounded-lg border border-gray-200 object-contain bg-white">';
            })
            ->editColumn('status', function (Brand $brand) {
                return ucfirst($brand->status);
            })
            ->addColumn('store_name', function (Brand $brand) {
                return $brand->store?->name ?? 'Platform';
            })
            ->editColumn('created_at', function (Brand $brand) {
                return $brand->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (Brand $brand) {
                return view('components.action-buttons', [
                'permission' => 'brands',
                'entityLabel' => 'Brand',
                'adminOnly' => true,
                    'id' => $brand->id,
                    'edit' => 'brandEdit',
                    'delete' => 'brandDelete',
                ])->render();
            })
            ->rawColumns(['logo', 'action'])
            ->make(true);
    }

    public function saveBrand(array $data): JsonResponse
    {
        $newLogoUrl = null;

        try {
            return DB::transaction(function () use ($data, &$newLogoUrl) {
                $brandId = $data['brand_id'] ?? null;
                $logo = $data['logo'] ?? null;
                $oldLogoUrl = null;

                // Only administrators may edit or delete brands;
                // store owners may create new ones for their store.
                if ($brandId && ! $this->isPlatformAdmin()) {
                    return ApiResponse::error('Only administrators can update brands.', 403);
                }

                $data['status'] = $data['status'] ?? 'active';
                unset($data['brand_id'], $data['logo']);

                // Store owners/staff are locked to their own store;
                // platform admins may pick any store (or Platform/null).
                if (! $this->isPlatformAdmin()) {
                    $data['store_id'] = CurrentStore::id();
                }

                if ($brandId) {
                    $brand = Brand::findOrFail($brandId);
                    $oldLogoUrl = $brand->logo_url;

                    if ($logo instanceof UploadedFile) {
                        $newLogoUrl = $this->storeLogo($logo, $data['name'] ?? $brand->name);
                        $data['logo_url'] = $newLogoUrl;
                    }

                    $brand->update($data);
                    $message = 'Brand updated successfully.';
                } else {
                    if ($logo instanceof UploadedFile) {
                        $newLogoUrl = $this->storeLogo($logo, $data['name'] ?? 'brand');
                        $data['logo_url'] = $newLogoUrl;
                    }

                    $brand = Brand::create($data);
                    $message = 'Brand created successfully.';
                }

                if ($newLogoUrl && $oldLogoUrl) {
                    $this->deleteLogo($oldLogoUrl);
                }

                if ($brandId) {
                    return ApiResponse::success($brand->fresh(), $message);
                }

                return ApiResponse::created($brand->fresh(), $message);
            });
        } catch (\Exception $e) {
            if ($newLogoUrl) {
                $this->deleteLogo($newLogoUrl);
            }

            return ApiResponse::error('Error saving brand: '.$e->getMessage(), 500);
        }
    }

    public function getBrandById(int $id): JsonResponse
    {
        try {
            $brand = Brand::findOrFail($id);
            $brand->logo_url = $brand->logo_url ? $this->logoUrl($brand->logo_url) : null;

            return ApiResponse::success($brand);
        } catch (\Exception) {
            return ApiResponse::notFound('Brand not found.');
        }
    }

    public function deleteBrand(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                if (! $this->isPlatformAdmin()) {
                    return ApiResponse::error('Only administrators can delete brands.', 403);
                }

                $brand = Brand::findOrFail($id);
                $this->deleteLogo($brand->logo_url);
                $brand->delete();

                return ApiResponse::success(null, 'Brand deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting brand: '.$e->getMessage(), 500);
        }
    }

    private function storeLogo(UploadedFile $logo, string $name): string
    {
        $name = Str::slug($name) ?: 'brand';
        $extension = $logo->getClientOriginalExtension();
        $fileName = $name.'-logo-'.now()->format('YmdHis').'.'.$extension;
        $path = $logo->storeAs('brands', $fileName, 'public');

        return '/storage/'.$path;
    }

    private function deleteLogo(?string $logoUrl): void
    {
        if (! $logoUrl) {
            return;
        }

        $path = parse_url($logoUrl, PHP_URL_PATH) ?: $logoUrl;

        if (! str_starts_with($path, '/storage/')) {
            return;
        }

        Storage::disk('public')->delete(substr($path, strlen('/storage/')));
    }

    private function logoUrl(string $logoUrl): string
    {
        $path = parse_url($logoUrl, PHP_URL_PATH) ?: $logoUrl;

        if (str_starts_with($path, '/storage/')) {
            return $path;
        }

        return $logoUrl;
    }

    private function isPlatformAdmin(): bool
    {
        $actor = auth()->user();

        return (bool) ($actor && ($actor->hasRole('Super Admin') || $actor->hasRole('Admin')));
    }
}
