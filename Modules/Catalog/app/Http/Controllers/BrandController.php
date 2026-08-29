<?php

namespace Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Catalog\Http\Requests\StoreBrandRequest;
use Modules\Catalog\Http\Requests\UpdateBrandRequest;
use Modules\Catalog\Services\BrandService;

class BrandController extends Controller
{
    public function __construct(private BrandService $brandService) {}

    public function index(Request $request)
    {
        return view('catalog::brands');
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
