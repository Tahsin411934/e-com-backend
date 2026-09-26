<?php

namespace Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Order\Http\Requests\OrderRequest;
use Modules\Order\Services\OrderService;
use Modules\Store\Models\Store;
use Modules\Store\Support\CurrentStore;
use Modules\Order\Models\Order;
use Modules\Shipping\Models\Shipment;
use Modules\Shipping\Services\PackzyService;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function index()
    {
        $actor = auth()->user();
        $canAssignStore = (bool) ($actor && ($actor->hasRole('Super Admin') || $actor->hasRole('Admin')));
        $canViewDetails = (bool) ($actor && $actor->hasPermission('orders.details'));

        $stores = $canAssignStore
            ? Store::where('status', 'active')->orderBy('name')->get()
            : collect();
        $currentStore = CurrentStore::store();

        return view('order::orders.index', compact('stores', 'canAssignStore', 'canViewDetails', 'currentStore'));
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
        return $this->orderService->getOrderForEditById((int) $id);
    }

    public function details($id)
    {
        return $this->orderService->getOrderDetailsById((int) $id);
    }

    public function bookSteadfast(int $orderId, PackzyService $steadfast)
    {
        $order = Order::forCurrentStore()->with(['user', 'deliveries'])->findOrFail($orderId);

        if ($order->shipments()->where('carrier_name', 'Steadfast')->exists()) {
            return \App\Helpers\ApiResponse::error('Steadfast booking already exists for this order.', 409);
        }

        if (! config('services.packzy.api_key') || ! config('services.packzy.secret_key')) {
            return \App\Helpers\ApiResponse::error('Steadfast API credentials are not configured.', 422);
        }

        $delivery = $order->deliveries->first();
        if (! $delivery || ! $delivery->delivery_phone || ! $delivery->delivery_address || ! $delivery->delivery_city) {
            return \App\Helpers\ApiResponse::error('Order delivery name, phone, address and city are required.', 422);
        }

        $response = $steadfast->book($order);
        $consignment = data_get($response, 'consignment');
        $tracking = data_get($consignment, 'tracking_code') ?: data_get($consignment, 'consignment_id');
        if (! $tracking) {
            return \App\Helpers\ApiResponse::error('Steadfast booking failed. Check the API response/logs and order details.', 502);
        }

        $shipment = Shipment::create([
            'order_id' => $order->id,
            'store_id' => $order->store_id,
            'shipping_address_id' => $order->shipping_address_id,
            'tracking_number' => (string) $tracking,
            'carrier_name' => 'Steadfast',
            'service_level' => 'standard',
            'delivery_type' => 'third_party',
            'status' => 'pending',
            'shipping_cost' => $order->shipping_total,
            'package_count' => 1,
            'recipient_name' => $order->customer_name ?: ($order->user?->name ?? 'Customer'),
            'recipient_phone' => $delivery->delivery_phone,
            'delivery_instructions' => $order->customer_note,
        ]);

        return \App\Helpers\ApiResponse::success([
            'shipment' => $shipment,
            'consignment' => $consignment,
        ], 'Steadfast parcel created successfully.');
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
