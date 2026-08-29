<?php

namespace Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Order\Services\PaymentService;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function index()
    {
        return view('order::payments.index');
    }

    public function dataTable(Request $request)
    {
        return $this->paymentService->getPaymentDataTable($request);
    }

    public function store(Request $request)
    {
        return $this->paymentService->savePayment($request->all());
    }

    public function show($id)
    {
        return $this->paymentService->getPaymentById((int) $id);
    }

    public function update(Request $request, int $id)
    {
        return $this->paymentService->savePayment($request->all() + ['payment_id' => $id]);
    }

    public function destroy($id)
    {
        return $this->paymentService->deletePayment((int) $id);
    }
}
