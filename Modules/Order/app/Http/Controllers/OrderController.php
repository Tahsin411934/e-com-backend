<?php

namespace Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Order\Http\Requests\OrderRequest;
use Modules\Order\Services\OrderService;
use Modules\Store\Models\Store;
use Modules\Store\Support\CurrentStore;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function index()
    {
        $actor = auth()->user();
        $canAssignStore = (bool) ($actor && ($actor->hasRole('Super Admin') || $actor->hasRole('Admin')));

        $stores = $canAssignStore
            ? Store::where('status', 'active')->orderBy('name')->get()
            : collect();
        $currentStore = CurrentStore::store();

        return view('order::orders.index', compact('stores', 'canAssignStore', 'currentStore'));
    }

    public function dataTable(Request $request)
    {
        return $this->orderService->getOrderDataTable($request);
    }

    public function store(OrderRequest $request)
    {
        return $this->orderService->saveOrder($request->validated());
    }

    public function show($id)
    {
        return $this->orderService->getOrderById((int) $id);
    }

    public function update(OrderRequest $request, int $id)
    {
        return $this->orderService->saveOrder($request->validated() + ['order_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->orderService->deleteOrder((int) $id);
    }
}
