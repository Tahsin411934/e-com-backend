<?php

namespace Modules\Order\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Account\Services\AccountTransactionService;
use Modules\Order\Models\Payment;
use Yajra\DataTables\DataTables;

class PaymentService
{
    public function getPaymentDataTable(Request $request)
    {
        $query = Payment::with('order')->orderByDesc('created_at');

        return DataTables::of($query)
            ->addColumn('order_number', function (Payment $payment) {
                return $payment->order?->order_number ?? '-';
            })
            ->editColumn('method', function (Payment $payment) {
                return str_replace('_', ' ', ucfirst($payment->method));
            })
            ->editColumn('status', function (Payment $payment) {
                return ucfirst($payment->status);
            })
            ->editColumn('amount', function (Payment $payment) {
                return number_format($payment->amount, 2);
            })
            ->editColumn('paid_at', function (Payment $payment) {
                return $payment->paid_at ? $payment->paid_at->format('d M Y H:i') : '-';
            })
            ->editColumn('created_at', function (Payment $payment) {
                return $payment->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (Payment $payment) {
                return view('components.action-buttons', [
                'permission' => 'payments',
                'entityLabel' => 'Payment',
                    'id' => $payment->id,
                    'edit' => 'paymentEdit',
                    'delete' => 'paymentDelete',
                ])->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function savePayment(array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($data) {
                $paymentId = $data['payment_id'] ?? null;
                unset($data['payment_id']);

                if ($paymentId) {
                    $payment = Payment::findOrFail($paymentId);
                    $payment->update($data);
                    $message = 'Payment updated successfully.';
                } else {
                    $payment = Payment::create($data);
                    $message = 'Payment created successfully.';
                }

                if (Schema::hasTable('account_transactions')) {
                    app(AccountTransactionService::class)->postPayment($payment->fresh('order.items.variant'));
                }

                return ApiResponse::success($payment->fresh(), $message);
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving payment: '.$e->getMessage(), 500);
        }
    }

    public function getPaymentById(int $id): JsonResponse
    {
        try {
            $payment = Payment::with('order')->findOrFail($id);

            return ApiResponse::success($payment);
        } catch (\Exception $e) {
            return ApiResponse::notFound('Payment not found.');
        }
    }

    public function deletePayment(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $payment = Payment::findOrFail($id);
                $payment->delete();

                return ApiResponse::success(null, 'Payment deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting payment: '.$e->getMessage(), 500);
        }
    }
}
