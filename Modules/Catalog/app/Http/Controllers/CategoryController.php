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

        return view('catalog::categories', compact('parents', 'stores', 'canAssignStore', 'currentStore'));
    }

    public function dataTable(Request $request)
    {
        return $this->categoryService->getCategoryDataTable($request);
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
