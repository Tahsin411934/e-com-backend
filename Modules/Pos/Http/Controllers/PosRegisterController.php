<?php

namespace Modules\Pos\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Pos\Http\Requests\PosRegisterRequest;
use Modules\Pos\Services\PosRegisterService;
use Modules\Store\Models\Store;

class PosRegisterController extends Controller
{
    public function __construct(private PosRegisterService $registerService) {}

    public function index()
    {
        $stores = Store::where('status', 'active')->orderBy('name')->get();

        return view('pos::registers.index', compact('stores'));
    }

    public function dataTable(Request $request)
    {
        return $this->registerService->getRegisterDataTable($request);
    }

    public function store(PosRegisterRequest $request)
    {
        return $this->registerService->saveRegister($request->validated());
    }

    public function show($id)
    {
        return $this->registerService->getRegisterById((int) $id);
    }

    public function update(PosRegisterRequest $request, $id)
    {
        return $this->registerService->saveRegister($request->validated() + ['register_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->registerService->deleteRegister((int) $id);
    }
}
