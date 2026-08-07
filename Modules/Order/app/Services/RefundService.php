<?php

namespace Modules\Order\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Order\Models\Refund;
use Yajra\DataTables\DataTables;

class RefundService
{
    public function getRefundDataTable(Request $request)
    {
        $query = Refund::with(['order', 'payment'])->orderByDesc('created_at');

        return DataTables::of($query)
            ->addColumn('order_number', function (Refund $refund) {
                return $refund->order?->order_number ?? '-';
            })
            ->addColumn('payment_method', function (Refund $refund) {
                return $refund->payment?->method ?? '-';
            })
            ->editColumn('amount', function (Refund $refund) {
                return number_format($refund->amount, 2);
            })
            ->editColumn('status', function (Refund $refund) {
                return ucfirst($refund->status);
            })
            ->editColumn('created_at', function (Refund $refund) {
                return $refund->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (Refund $refund) {
                return view('components.action-buttons', [
                    'id' => $refund->id,
                    'edit' => 'refundEdit',
                    'delete' => 'refundDelete',
                ])->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function saveRefund(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                $refundId = $data['refund_id'] ?? null;
                unset($data['refund_id']);

                if ($refundId) {
                    $refund = Refund::findOrFail($refundId);
                    $refund->update($data);
                    $message = 'Refund updated successfully.';
                } else {
                    $refund = Refund::create($data);
                    $message = 'Refund created successfully.';
                }

                return [
                    'status' => 'success',
                    'message' => $message,
                    'refund' => $refund->fresh(),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error saving refund: ' . $e->getMessage(),
            ];
        }
    }

    public function getRefundById(int $id): array
    {
        try {
            $refund = Refund::with(['order', 'payment'])->findOrFail($id);
            return [
                'status' => 'success',
                'refund' => $refund,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Refund not found.',
            ];
        }
    }

    public function deleteRefund(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $refund = Refund::findOrFail($id);
                $refund->delete();
                return [
                    'status' => 'success',
                    'message' => 'Refund deleted successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error deleting refund: ' . $e->getMessage(),
            ];
        }
    }
}