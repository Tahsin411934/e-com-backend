<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Order\Models\Order;
use Modules\Shipping\Models\Shipment;

class CustomerOrderApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->where('user_id', $request->user()->id)
            ->withCount('items')
            ->with(['deliveries', 'shipments'])
            ->latest('placed_at')
            ->latest('id')
            ->get()
            ->map(fn (Order $order) => $this->summary($order));

        return response()->json(['status' => 'success', 'orders' => $orders]);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        $order->load([
            'items',
            'deliveries',
            'shipments.events' => fn ($query) => $query->orderBy('occurred_at'),
        ]);

        return response()->json([
            'status' => 'success',
            'order' => array_merge($this->summary($order), [
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->product_name,
                    'variant_name' => $item->variant_name,
                    'sku' => $item->sku,
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'line_total' => (float) $item->line_total,
                ]),
                'delivery' => $order->deliveries->first()?->only([
                    'status', 'delivery_address', 'delivery_city', 'delivery_phone', 'delivery_notes',
                    'assigned_at', 'picked_at', 'delivered_at', 'cancelled_at',
                ]),
                'timeline' => $this->timeline($order),
            ]),
        ]);
    }

    private function summary(Order $order): array
    {
        $shipment = $order->shipments->sortByDesc('created_at')->first();

        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $this->currentShippingStatus($order, $shipment),
            'order_status' => $order->status,
            'placed_at' => $order->placed_at?->toIso8601String() ?? $order->created_at?->toIso8601String(),
            'items_count' => $order->items_count ?? $order->items->count(),
            'grand_total' => (float) $order->grand_total,
            'currency_code' => $order->currency_code,
            'tracking_number' => $shipment?->tracking_number,
        ];
    }

    private function timeline(Order $order): array
    {
        $shipment = $order->shipments->sortByDesc('created_at')->first();
        $delivery = $order->deliveries->first();
        $events = $shipment?->events ?? collect();
        $statusRank = [
            'pending' => 0, 'confirmed' => 1, 'processing' => 2, 'packed' => 3,
            'ready' => 4, 'ready_for_pickup' => 4, 'out_for_delivery' => 5,
            'completed' => 6, 'delivered' => 6,
        ];
        $steps = [
            ['key' => 'pending', 'title' => 'Order placed', 'description' => 'Your order has been received.'],
            ['key' => 'confirmed', 'title' => 'Order confirmed', 'description' => 'Your order has been confirmed.'],
            ['key' => 'processing', 'title' => 'Preparing your order', 'description' => 'Items are being prepared.'],
            ['key' => 'packed', 'title' => 'Packed', 'description' => 'Your parcel has been packed.'],
            ['key' => 'ready_for_pickup', 'title' => 'Ready for pickup', 'description' => 'The parcel is ready for the delivery partner.'],
            ['key' => 'out_for_delivery', 'title' => 'Out for delivery', 'description' => 'Your parcel is on its way.'],
            ['key' => 'delivered', 'title' => 'Delivered', 'description' => 'Your parcel has been delivered.'],
        ];
        $currentStatus = $this->currentShippingStatus($order, $shipment);
        $currentRank = $statusRank[$currentStatus] ?? 0;

        return collect($steps)->map(function (array $step, int $index) use ($events, $order, $delivery, $currentRank) {
            $event = $events->firstWhere('status', $step['key']);
            $occurredAt = $event?->occurred_at;

            if ($step['key'] === 'pending') {
                $occurredAt ??= $order->placed_at ?? $order->created_at;
            }

            if ($step['key'] === 'confirmed') {
                $occurredAt ??= $delivery?->assigned_at;
            }
            if ($step['key'] === 'out_for_delivery') {
                $occurredAt ??= $delivery?->picked_at;
            }
            if ($step['key'] === 'delivered') {
                $occurredAt ??= $delivery?->delivered_at;
            }

            return [
                ...$step,
                'state' => $currentRank > $index ? 'completed' : ($currentRank === $index ? 'current' : 'upcoming'),
                'occurred_at' => $occurredAt?->toIso8601String(),
            ];
        })->values();
    }

    private function currentShippingStatus(Order $order, ?Shipment $shipment): string
    {
        if ($shipment) {
            return $shipment->status;
        }

        return match ($order->deliveries->first()?->status) {
            'assigned' => 'confirmed',
            'picked' => 'out_for_delivery',
            'delivered' => 'delivered',
            default => $order->status,
        };
    }
}
