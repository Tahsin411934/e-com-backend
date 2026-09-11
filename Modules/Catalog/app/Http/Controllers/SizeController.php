<?php

namespace Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Catalog\Http\Requests\StoreSizeRequest;
use Modules\Catalog\Services\SizeService;

class SizeController extends Controller
{
    public function __construct(private SizeService $sizeService) {}

    public function index(Request $request)
    {
        return view('catalog::sizes');
    }

    public function dataTable(Request $request)
    {
        return $this->sizeService->getSizeDataTable($request);
    }

    public function store(StoreSizeRequest $request)
    {
        return $this->sizeService->saveSize($request->validated());
    }

    public function show($id)
    {
        return $this->sizeService->getSizeById((int) $id);
    }

    /**
     * Return the size set attached to a size group (or null) so the Size
     * form can decide between creating a new set and updating the group's
     * existing one.
     */
    public function getByGroup($sizeGroupId)
    {
        return $this->sizeService->getSizeByGroupId((int) $sizeGroupId);
    }

    public function update(StoreSizeRequest $request, $id)
    {
        return $this->sizeService->saveSize($request->validated() + ['size_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->sizeService->deleteSize((int) $id);
    }
}
