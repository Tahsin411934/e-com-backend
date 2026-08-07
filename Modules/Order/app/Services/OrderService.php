<?php

namespace Modules\Order\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Order\Models\Order;
use Yajra\DataTables\DataTables;

class OrderService
{
    public function getOrderDataTable(Request $request)
    {
        $query = Order::with(['user', 'store'])->orderByDesc('created_at');

        return DataTables::of($query)
            ->addColumn('user_email', function (Order $order) {
                return $order->user?->email ?? '-';
            })
            ->addColumn('store_name', function (Order $order) {
                return $order->store?->name ?? '-';
            })
            ->editColumn('status', function (Order $order) {
                return ucfirst($order->status);
            })
            ->editColumn('payment_status', function (Order $order) {
                return str_replace('_', ' ', ucfirst($order->payment_status));
            })
            ->editColumn('grand_total', function (Order $order) {
                return number_format($order->grand_total, 2);
            })
            ->editColumn('created_at', function (Order $order) {
                return $order->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function (Order $order) {
                return view('components.action-buttons', [
                    'id' => $order->id,
                    'edit' => 'orderEdit',
                    'delete' => 'orderDelete',
                ])->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function saveOrder(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                $orderId = $data['order_id'] ?? null;
                unset($data['order_id']);

                if ($orderId) {
                    $order = Order::findOrFail($orderId);
                    $order->update($data);
                    $message = 'Order updated successfully.';
                } else {
                    if (!isset($data['order_number'])) {
                        $data['order_number'] = 'ORD-' . strtoupper(uniqid());
                    }
                    $order = Order::create($data);
                    $message = 'Order created successfully.';
                }

                return [
                    'status' => 'success',
                    'message' => $message,
                    'order' => $order->fresh(),
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error saving order: ' . $e->getMessage(),
            ];
        }
    }

    public function getOrderById(int $id): array
    {
        try {
            $order = Order::with(['user', 'store', 'items', 'payments', 'refunds'])->findOrFail($id);
            return [
                'status' => 'success',
                'order' => $order,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Order not found.',
            ];
        }
    }

    public function deleteOrder(int $id): array
    {
        try {
            return DB::transaction(function () use ($id) {
                $order = Order::findOrFail($id);
                $order->delete();
                return [
                    'status' => 'success',
                    'message' => 'Order deleted successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error deleting order: ' . $e->getMessage(),
            ];
        }
    }
}