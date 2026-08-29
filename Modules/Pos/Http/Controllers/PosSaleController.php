<?php

namespace Modules\Pos\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Identity\Models\User;
use Modules\Pos\Http\Requests\PosSaleRequest;
use Modules\Pos\Services\PosRegisterService;
use Modules\Pos\Services\PosSaleService;
use Modules\Pos\Services\PosShiftService;

class PosSaleController extends Controller
{
    public function __construct(private PosSaleService $saleService, private PosRegisterService $registerService, private PosShiftService $shiftService) {}

    public function index()
    {
        $registers = $this->registerService->getAllActiveRegisters();
        $users = User::orderBy('first_name')->orderBy('last_name')->get(['id', 'first_name', 'last_name'])->map(function ($user) {
            return ['id' => $user->id, 'name' => $user->name];
        });

        return view('pos::sales.index', compact('registers', 'users'));
    }

    public function dataTable(Request $request)
    {
        return $this->saleService->getSaleDataTable($request);
    }

    public function store(PosSaleRequest $request)
    {
        return $this->saleService->saveSale($request->validated());
    }

    public function show($id)
    {
        return $this->saleService->getSaleById($id);
    }

    public function update(PosSaleRequest $request, $id)
    {
        $data = $request->validated();
        $data['sale_id'] = $id;

        return $this->saleService->saveSale($data);
    }

    public function destroy($id)
    {
        return $this->saleService->deleteSale($id);
    }

    public function voidSale($id)
    {
        return $this->saleService->voidSale($id);
    }
}
