<?php

namespace Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Order\Services\RefundService;

class RefundController extends Controller
{
    public function __construct(private RefundService $refundService) {}

    public function index()
    {
        return view('order::refunds.index');
    }

    public function dataTable(Request $request)
    {
        return $this->refundService->getRefundDataTable($request);
    }

    public function store(Request $request)
    {
        return $this->refundService->saveRefund($request->all());
    }

    public function show($id)
    {
        return $this->refundService->getRefundById((int) $id);
    }

    public function update(Request $request, int $id)
    {
        return $this->refundService->saveRefund($request->all() + ['refund_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->refundService->deleteRefund((int) $id);
    }
}
