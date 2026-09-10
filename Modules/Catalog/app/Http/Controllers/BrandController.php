<?php

namespace Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Catalog\Http\Requests\StoreBrandRequest;
use Modules\Catalog\Http\Requests\UpdateBrandRequest;
use Modules\Catalog\Services\BrandService;
use Modules\Store\Models\Store;
use Modules\Store\Support\CurrentStore;

class BrandController extends Controller
{
    public function __construct(private BrandService $brandService) {}

    public function index(Request $request)
    {
        $actor = auth()->user();
        $canAssignStore = (bool) ($actor && ($actor->hasRole('Super Admin') || $actor->hasRole('Admin')));

        $stores = $canAssignStore
            ? Store::where('status', 'active')->orderBy('name')->get()
            : collect();
        $currentStore = CurrentStore::store();

        return view('catalog::brands', compact('stores', 'canAssignStore', 'currentStore'));
    }

    public function dataTable(Request $request)
    {
        return $this->brandService->getBrandDataTable($request);
    }

    public function store(StoreBrandRequest $request)
    {
        return $this->brandService->saveBrand($request->validated());
    }

    public function show($id)
    {
        return $this->brandService->getBrandById((int) $id);
    }

    public function update(UpdateBrandRequest $request, $id)
    {
        return $this->brandService->saveBrand($request->validated() + ['brand_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->brandService->deleteBrand((int) $id);
    }
}
