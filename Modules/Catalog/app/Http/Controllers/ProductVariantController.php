<?php

namespace Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Catalog\Http\Requests\StoreProductVariantRequest;
use Modules\Catalog\Http\Requests\UpdateProductVariantRequest;
use Modules\Catalog\Services\ProductVariantService;

class ProductVariantController extends Controller
{
    public function __construct(private ProductVariantService $variantService) {}

    public function index(Request $request)
    {
        return view('catalog::variants');
    }

    public function dataTable(Request $request)
    {
        return $this->variantService->getVariantDataTable($request);
    }

    public function store(StoreProductVariantRequest $request)
    {
        return $this->variantService->saveVariant($request->validated());
    }

    public function show($id)
    {
        return $this->variantService->getVariantById((int) $id);
    }

    public function update(UpdateProductVariantRequest $request, $id)
    {
        return $this->variantService->saveVariant($request->validated() + ['variant_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->variantService->deleteVariant((int) $id);
    }
}
