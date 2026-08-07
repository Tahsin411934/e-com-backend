<?php

namespace Modules\Catalog\Services;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Catalog\Models\Brand;
use Yajra\DataTables\DataTables;

class BrandService
{
    public function getBrandDataTable(Request $request)
    {
        $query = Brand::query()->orderByDesc('created_at');

        return DataTables::of($query)
            ->addColumn('logo', function (Brand $brand) {
                if (!$brand->logo_url) {
                    return '-';
                }

                return '<img src="' . e($this->logoUrl($brand->logo_url)) . '" alt="' . e($brand->name) . '" class="h-10 w-10 rounded-lg border border-gray-200 object-contain bg-white">';
            })
            ->editColumn('status', function (Brand $brand) {
                return ucfirst($brand->status);
            })
            ->editColumn('created_at', function (Brand $brand) {
                return $brand->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (Brand $brand) {
                return view('components.action-buttons', [
                    'id' => $brand->id,
                    'edit' => 'brandEdit',
                    'delete' => 'brandDelete',
                ])->render();
            })
            ->rawColumns(['logo', 'action'])
            ->make(true);
    }

    public function saveBrand(array $data): array
    {
        $newLogoUrl = null;

        try {
            return DB::transaction(function () use ($data, &$newLogoUrl) {
                $brandId = $data['brand_id'] ?? null;
                $logo = $data['logo'] ?? null;
                $oldLogoUrl = null;

                $data['status'] = $data['status'] ?? 'active';
                unset($data['brand_id'], $data['logo']);

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

                return [
                    'status' => 'success',
                    'message' => $message,
                    'brand' => $brand->fresh(),
                ];
            });
        } catch (\Exception $e) {
            if ($newLogoUrl) {
                $this->deleteLogo($newLogoUrl);
            }

            return [
                'status' => 'error',
                'message' => 'Error saving brand: ' . $e->getMessage(),
            ];
        }
    }

    public function getBrandById(int $id): array
    {
        try {
            $brand = Brand::findOrFail($id);
            $brand->logo_url = $brand->logo_url ? $this->logoUrl($brand->logo_url) : null;

            return [
                'status' => 'success',
                'brand' => $brand,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Brand not found.',
            ];
        }
    }

    public function deleteBrand(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $brand = Brand::findOrFail($id);
                $this->deleteLogo($brand->logo_url);
                $brand->delete();

                return [
                    'status' => 'success',
                    'message' => 'Brand deleted successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error deleting brand: ' . $e->getMessage(),
            ];
        }
    }

    private function storeLogo(UploadedFile $logo, string $name): string
    {
        $name = Str::slug($name) ?: 'brand';
        $extension = $logo->getClientOriginalExtension();
        $fileName = $name . '-logo-' . now()->format('YmdHis') . '.' . $extension;
        $path = $logo->storeAs('brands', $fileName, 'public');

        return '/storage/' . $path;
    }

    private function deleteLogo(?string $logoUrl): void
    {
        if (!$logoUrl) {
            return;
        }

        $path = parse_url($logoUrl, PHP_URL_PATH) ?: $logoUrl;

        if (!str_starts_with($path, '/storage/')) {
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
}
