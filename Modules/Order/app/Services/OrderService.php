<?php

namespace Modules\Order\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Order\Models\Order;
use Modules\Store\Support\CurrentStore;
use Yajra\DataTables\DataTables;

class OrderService
{
    public function getOrderDataTable(Request $request)
    {
        $query = Order::forCurrentStore()->with(['user', 'store'])->orderByDesc('created_at');

        if ($request->store_id) {
            $query->where('store_id', $request->store_id);
        }

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

    public function saveOrder(array $data): JsonResponse
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
                    if (! isset($data['order_number'])) {
                        $data['order_number'] = 'ORD-'.strtoupper(uniqid());
                    }
                    $order = Order::create($data);
                    $message = 'Order created successfully.';
                }

                return ApiResponse::success($order->fresh(), $message);
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving order: '.$e->getMessage(), 500);
        }
    }

    public function getOrderById(int $id): JsonResponse
    {
        try {
            $order = Order::forCurrentStore()->with(['user', 'store', 'items', 'payments', 'refunds'])->findOrFail($id);

            return ApiResponse::success($order);
        } catch (\Exception $e) {
            return ApiResponse::notFound('Order not found.');
        }
    }

    public function deleteOrder(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $order = Order::forCurrentStore()->findOrFail($id);
                $order->delete();

                return ApiResponse::success(null, 'Order deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting order: '.$e->getMessage(), 500);
        }
    }
}
