<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Inventory\Http\Requests\InventoryMovementRequest;
use Modules\Inventory\Models\InventoryLocation;
use Modules\Inventory\Services\InventoryMovementService;

class InventoryMovementController extends Controller
{
    public function __construct(private InventoryMovementService $movementService) {}

    public function index()
    {
        $locations = InventoryLocation::where('status', 'active')->orderBy('name')->get();

        return view('inventory::movements.index', compact('locations'));
    }

    public function dataTable(Request $request)
    {
        return $this->movementService->getMovementDataTable($request);
    }

    public function store(InventoryMovementRequest $request)
    {
        return $this->movementService->saveMovement($request->validated());
    }

    public function show($id)
    {
        return $this->movementService->getMovementById((int) $id);
    }

    public function update(InventoryMovementRequest $request, $id)
    {
        return $this->movementService->saveMovement($request->validated() + ['movement_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->movementService->deleteMovement((int) $id);
    }
}
