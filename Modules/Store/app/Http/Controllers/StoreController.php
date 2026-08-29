<?php

namespace Modules\Store\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Store\Http\Requests\StoreRequest;
use Modules\Store\Services\StoreService;

class StoreController extends Controller
{
    public function __construct(private StoreService $storeService) {}

    public function index()
    {
        return view('store::stores.index');
    }

    public function dataTable(Request $request)
    {
        return $this->storeService->getStoreDataTable($request);
    }

    public function store(StoreRequest $request)
    {
        return $this->storeService->saveStore($request->validated());
    }

    public function show($id)
    {
        return $this->storeService->getStoreById((int) $id);
    }

    public function update(StoreRequest $request, $id)
    {
        return $this->storeService->saveStore($request->validated() + ['store_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->storeService->deleteStore((int) $id);
    }
}
