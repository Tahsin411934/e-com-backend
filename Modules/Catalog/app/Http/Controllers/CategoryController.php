<?php

namespace Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Catalog\Http\Requests\StoreCategoryRequest;
use Modules\Catalog\Http\Requests\UpdateCategoryRequest;
use Modules\Catalog\Services\CategoryService;
use Modules\Store\Models\Store;
use Modules\Store\Support\CurrentStore;

class CategoryController extends Controller
{
    public function __construct(private CategoryService $categoryService) {}

    public function index(Request $request)
    {
        $parents = $this->categoryService->getParentCategories();
        $actor = auth()->user();
        $canAssignStore = (bool) ($actor && ($actor->hasRole('Super Admin') || $actor->hasRole('Admin')));

        $stores = $canAssignStore
            ? Store::where('status', 'active')->orderBy('name')->get()
            : collect();
        $currentStore = CurrentStore::store();
        $canReorderCategories = (bool) ($actor
            && ($actor->hasRole('Super Admin') || $actor->hasRole('Admin'))
            && $actor->hasPermission('categories.edit'));

        return view('catalog::categories', compact('parents', 'stores', 'canAssignStore', 'currentStore', 'canReorderCategories'));
    }

    public function dataTable(Request $request)
    {
        return $this->categoryService->getCategoryDataTable($request);
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'category_ids' => ['required', 'array', 'min:1', 'max:1000'],
            'category_ids.*' => ['required', 'integer', 'distinct'],
            'start' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
        ]);

        return $this->categoryService->reorderCategories(
            $validated['category_ids'],
            (int) ($validated['start'] ?? 0),
            isset($validated['store_id']) && $validated['store_id'] !== '' ? (int) $validated['store_id'] : null,
        );
    }

    public function store(StoreCategoryRequest $request)
    {
        return $this->categoryService->saveCategory($request->validated());
    }

    public function show($id)
    {
        return $this->categoryService->getCategoryById((int) $id);
    }

    public function update(UpdateCategoryRequest $request, $id)
    {
        return $this->categoryService->saveCategory($request->validated() + ['category_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->categoryService->deleteCategory((int) $id);
    }
}
