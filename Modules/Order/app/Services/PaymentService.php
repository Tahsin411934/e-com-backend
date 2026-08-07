<?php

namespace Modules\Order\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                    'id' => $payment->id,
                    'edit' => 'paymentEdit',
                    'delete' => 'paymentDelete',
                ])->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function savePayment(array $data): array
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

                return [
                    'status' => 'success',
                    'message' => $message,
                    'payment' => $payment->fresh(),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error saving payment: ' . $e->getMessage(),
            ];
        }
    }

    public function getPaymentById(int $id): array
    {
        try {
            $payment = Payment::with('order')->findOrFail($id);
            return [
                'status' => 'success',
                'payment' => $payment,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Payment not found.',
            ];
        }
    }

    public function deletePayment(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $payment = Payment::findOrFail($id);
                $payment->delete();
                return [
                    'status' => 'success',
                    'message' => 'Payment deleted successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error deleting payment: ' . $e->getMessage(),
            ];
        }
    }
}