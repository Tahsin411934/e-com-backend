<?php

namespace Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Order\Services\OrderService;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function index()
    {
        return view('order::orders.index');
    }

    public function dataTable(Request $request)
    {
        return $this->orderService->getOrderDataTable($request);
    }

    public function store(Request $request)
    {
        return $this->orderService->saveOrder($request->all());
    }

    public function show($id)
    {
        return $this->orderService->getOrderById((int) $id);
    }

    public function update(Request $request, int $id)
    {
        return $this->orderService->saveOrder($request->all() + ['order_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->orderService->deleteOrder((int) $id);
    }
}
