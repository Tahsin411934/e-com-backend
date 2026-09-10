<?php

namespace Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Catalog\Http\Requests\StoreBrandRequest;
use Modules\Catalog\Http\Requests\UpdateBrandRequest;
use Modules\Catalog\Services\BrandService;
use Modules\Store\Models\Store;

class BrandController extends Controller
{
    public function __construct(private BrandService $brandService) {}

    public function index(Request $request)
    {
        $actor = auth()->user();
        // Store-wise filter is only meaningful for platform admins;
        // owners/staff are already scoped to their own store.
        $stores = ($actor && ($actor->hasRole('Super Admin') || $actor->hasRole('Admin')))
            ? Store::orderBy('name')->get()
            : collect();

        return view('catalog::brands', compact('stores'));
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
