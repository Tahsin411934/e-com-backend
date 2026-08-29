<?php

namespace Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Catalog\Http\Requests\StoreUnitRequest;
use Modules\Catalog\Http\Requests\UpdateUnitRequest;
use Modules\Catalog\Services\UnitService;

class UnitController extends Controller
{
    public function __construct(private UnitService $unitService) {}

    public function index(Request $request)
    {
        return view('catalog::units');
    }

    public function dataTable(Request $request)
    {
        return $this->unitService->getUnitDataTable($request);
    }

    public function store(StoreUnitRequest $request)
    {
        return $this->unitService->saveUnit($request->validated());
    }

    public function show($id)
    {
        return $this->unitService->getUnitById((int) $id);
    }

    public function update(UpdateUnitRequest $request, $id)
    {
        return $this->unitService->saveUnit($request->validated() + ['unit_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->unitService->deleteUnit((int) $id);
    }
}
