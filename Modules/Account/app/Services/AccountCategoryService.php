<?php

namespace Modules\Account\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Account\Models\AccountCategory;
use Yajra\DataTables\DataTables;

class AccountCategoryService
{
    public function getDataTable(Request $request)
    {
        $query = AccountCategory::with('parent')->orderBy('type')->orderBy('name');

        return DataTables::of($query)
            ->addColumn('parent_name', fn (AccountCategory $category) => $category->parent?->name ?? '-')
            ->editColumn('type', fn (AccountCategory $category) => Str::headline($category->type))
            ->editColumn('is_system', fn (AccountCategory $category) => $category->is_system ? 'Yes' : 'No')
            ->editColumn('is_active', fn (AccountCategory $category) => $category->is_active ? 'Active' : 'Inactive')
            ->addColumn('action', function (AccountCategory $category) {
                if ($category->is_system) {
                    return '<span class="text-xs text-gray-400">System</span>';
                }

                return view('components.action-buttons', [
                    'id' => $category->id,
                    'edit' => 'accountcategoryEdit',
                    'delete' => 'accountcategoryDelete',
                ])->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function save(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                $id = $data['category_id'] ?? null;
                unset($data['category_id']);
                $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
                $data['is_active'] = (bool) ($data['is_active'] ?? true);

                if ($id) {
                    $category = AccountCategory::where('is_system', false)->findOrFail($id);
                    $category->update($data);
                    $message = 'Category updated successfully.';
                } else {
                    $category = AccountCategory::create($data);
                    $message = 'Category created successfully.';
                }

                return ['status' => 'success', 'message' => $message, 'category' => $category->fresh('parent')];
            });
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Error saving category: ' . $e->getMessage()];
        }
    }

    public function find(int $id): array
    {
        try {
            return ['status' => 'success', 'category' => AccountCategory::findOrFail($id)];
        } catch (\Exception) {
            return ['status' => 'error', 'message' => 'Category not found.'];
        }
    }

    public function delete(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $category = AccountCategory::where('is_system', false)->findOrFail($id);
                $category->delete();
                return ['status' => 'success', 'message' => 'Category deleted successfully.'];
            });
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Error deleting category: ' . $e->getMessage()];
        }
    }

    public function activeOptions(?string $type = null)
    {
        return AccountCategory::where('is_active', true)
            ->when($type, fn ($query) => $query->where('type', $type))
            ->orderBy('name')
            ->get();
    }
}
