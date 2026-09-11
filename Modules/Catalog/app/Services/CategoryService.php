<?php

namespace Modules\Catalog\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Catalog\Models\Category;
use Modules\Store\Support\CurrentStore;
use Yajra\DataTables\DataTables;

class CategoryService
{
    public function getParentCategories()
    {
        return Category::forCurrentStore()
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();
    }

    public function getCategoryDataTable(Request $request)
    {
        $query = Category::forCurrentStore()->with(['parent', 'store'])->orderByDesc('created_at');

        if ($request->store_id) {
            $query->where('store_id', $request->store_id);
        }

        return DataTables::of($query)
            ->addColumn('image_preview', function (Category $category) {
                $imageUrl = $this->getCategoryImageUrl($category);
                if ($imageUrl) {
                    return '<img src="'.$imageUrl.'" alt="'.e($category->name).'" class="w-12 h-12 object-cover rounded" />';
                }

                return '-';
            })
            ->addColumn('parent', function (Category $category) {
                return $category->parent?->name ?: '-';
            })
            ->editColumn('status', function (Category $category) {
                return ucfirst($category->status);
            })
            ->addColumn('store_name', function (Category $category) {
                return $category->store?->name ?? 'Platform';
            })
            ->editColumn('created_at', function (Category $category) {
                return $category->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (Category $category) {
                return view('components.action-buttons', [
                'permission' => 'categories',
                'entityLabel' => 'Category',
                    'id' => $category->id,
                    'edit' => 'categoryEdit',
                    'delete' => 'categoryDelete',
                ])->render();
            })
            ->rawColumns(['image_preview', 'action'])
            ->make(true);
    }

    /**
     * Get the category's image URL (uploaded file or legacy URL).
     */
    public function getCategoryImageUrl(Category $category): ?string
    {
        if ($category->image) {
            return asset('storage/'.$category->image);
        }
        if ($category->image_url) {
            return $category->image_url;
        }

        return null;
    }

    public function saveCategory(array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($data) {
                $categoryId = $data['category_id'] ?? null;
                $data['parent_id'] = $data['parent_id'] ?: null;
                $data['status'] = $data['status'] ?? 'active';

                // Store owners/staff are locked to their own store;
                // platform admins may pick any store (or Platform/null).
                if (! $this->isPlatformAdmin()) {
                    $data['store_id'] = CurrentStore::id();
                }

                // Handle image upload
                if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                    // Delete old uploaded image if updating
                    if ($categoryId) {
                        $oldCategory = Category::forCurrentStore()->find($categoryId);
                        if ($oldCategory && $oldCategory->image) {
                            Storage::disk('public')->delete($oldCategory->image);
                        }
                    }
                    $data['image'] = $data['image']->store('categories', 'public');
                    // Clear legacy image_url when new image uploaded
                    $data['image_url'] = null;
                } else {
                    unset($data['image']);
                }

                if ($categoryId) {
                    $category = Category::forCurrentStore()->findOrFail($categoryId);
                    $category->update($data);

                    return ApiResponse::success($category->fresh(), 'Category updated successfully.');
                }

                $category = Category::create($data);

                return ApiResponse::created($category->fresh(), 'Category created successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving category: '.$e->getMessage(), 500);
        }
    }

    public function getCategoryById(int $id): JsonResponse
    {
        try {
            $category = Category::forCurrentStore()->findOrFail($id);
            $categoryArray = $category->toArray();
            $categoryArray['category_image_url'] = $this->getCategoryImageUrl($category);

            return ApiResponse::success($categoryArray);
        } catch (\Exception) {
            return ApiResponse::notFound('Category not found.');
        }
    }

    public function deleteCategory(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $category = Category::forCurrentStore()->findOrFail($id);

                // Delete uploaded image file
                if ($category->image) {
                    Storage::disk('public')->delete($category->image);
                }

                $category->delete();

                return ApiResponse::success(null, 'Category deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting category: '.$e->getMessage(), 500);
        }
    }

    private function isPlatformAdmin(): bool
    {
        $actor = auth()->user();

        return (bool) ($actor && ($actor->hasRole('Super Admin') || $actor->hasRole('Admin')));
    }
}
