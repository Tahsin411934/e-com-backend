<?php

namespace Modules\Pos\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Identity\Models\User;
use Modules\Pos\Http\Requests\PosShiftRequest;
use Modules\Pos\Services\PosRegisterService;
use Modules\Pos\Services\PosShiftService;

class PosShiftController extends Controller
{
    public function __construct(private PosShiftService $shiftService, private PosRegisterService $registerService) {}

    public function index()
    {
        $registers = $this->registerService->getAllActiveRegisters();
        $users = User::orderBy('first_name')->orderBy('last_name')->get(['id', 'first_name', 'last_name'])->map(function ($user) {
            return ['id' => $user->id, 'name' => $user->name];
        });

        return view('pos::shifts.index', compact('registers', 'users'));
    }

    public function dataTable(Request $request)
    {
        return $this->shiftService->getShiftDataTable($request);
    }

    public function store(PosShiftRequest $request)
    {
        return $this->shiftService->saveShift($request->validated());
    }

    public function show($id)
    {
        return $this->shiftService->getShiftById((int) $id);
    }

    public function update(PosShiftRequest $request, $id)
    {
        return $this->shiftService->saveShift($request->validated() + ['shift_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->shiftService->deleteShift((int) $id);
    }

    public function closeShift(Request $request, $id)
    {
        return $this->shiftService->closeShift((int) $id, $request->only(['declared_cash', 'notes']));
    }
}
