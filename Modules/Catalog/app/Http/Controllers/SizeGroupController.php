<?php

namespace Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Catalog\Http\Requests\StoreSizeGroupRequest;
use Modules\Catalog\Http\Requests\UpdateSizeGroupRequest;
use Modules\Catalog\Services\SizeGroupService;

class SizeGroupController extends Controller
{
    public function __construct(private SizeGroupService $sizeGroupService) {}

    public function index(Request $request)
    {
        return view('catalog::size-groups');
    }

    public function dataTable(Request $request)
    {
        return $this->sizeGroupService->getSizeGroupDataTable($request);
    }

    public function store(StoreSizeGroupRequest $request)
    {
        return $this->sizeGroupService->saveSizeGroup($request->validated());
    }

    public function show($id)
    {
        return $this->sizeGroupService->getSizeGroupById((int) $id);
    }

    public function update(UpdateSizeGroupRequest $request, $id)
    {
        return $this->sizeGroupService->saveSizeGroup($request->validated() + ['size_group_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->sizeGroupService->deleteSizeGroup((int) $id);
    }
}
