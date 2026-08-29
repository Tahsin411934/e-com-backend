<?php

namespace Modules\Order\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Identity\Models\User;
use Modules\Order\Models\Delivery;
use Modules\Order\Models\Order;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $query = Delivery::with(['order', 'user', 'deliveryBoy'])
            ->orderByDesc('created_at');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by delivery boy (for delivery boys to see their assignments)
        if ($request->has('delivery_boy_id')) {
            $query->where('delivery_boy_id', $request->delivery_boy_id);
        }

        // For regular users, only show their own deliveries
        if (! auth()->user()->hasRole('admin') && ! auth()->user()->hasRole('delivery_boy')) {
            $query->where('user_id', auth()->id());
        }

        $deliveries = $query->paginate(20);

        return ApiResponse::fromResult([
            'status' => 'success',
            'deliveries' => $deliveries,
        ]);
    }

    public function show($id)
    {
        $delivery = Delivery::with(['order.items', 'user', 'deliveryBoy'])->findOrFail((int) $id);

        // Check authorization
        if (! auth()->user()->hasRole('admin') &&
            ! auth()->user()->hasRole('delivery_boy') &&
            $delivery->user_id !== auth()->id()) {
            return ApiResponse::fromResult([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ], 200, 403);
        }

        return ApiResponse::fromResult([
            'status' => 'success',
            'delivery' => $delivery,
        ]);
    }

    public function assign(Request $request, int $id)
    {
        $request->validate([
            'delivery_boy_id' => 'required|integer|exists:users,id',
        ]);

        $delivery = Delivery::findOrFail($id);

        // Check if user is delivery boy
        $deliveryBoy = User::findOrFail($request->delivery_boy_id);
        if (! $deliveryBoy->hasRole('delivery_boy')) {
            return ApiResponse::fromResult([
                'status' => 'error',
                'message' => 'Selected user is not a delivery boy.',
            ], 200, 400);
        }

        $delivery->update([
            'delivery_boy_id' => $request->delivery_boy_id,
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);

        return ApiResponse::fromResult([
            'status' => 'success',
            'message' => 'Delivery boy assigned successfully.',
            'delivery' => $delivery->load('deliveryBoy'),
        ]);
    }

    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|in:pending,assigned,picked,delivered,cancelled',
        ]);

        $delivery = Delivery::findOrFail($id);

        $updateData = ['status' => $request->status];

        // Set timestamp based on status
        switch ($request->status) {
            case 'picked':
                $updateData['picked_at'] = now();
                break;
            case 'delivered':
                $updateData['delivered_at'] = now();
                // Update order status
                $delivery->order->update(['status' => 'completed']);
                break;
            case 'cancelled':
                $updateData['cancelled_at'] = now();
                break;
        }

        $delivery->update($updateData);

        return ApiResponse::fromResult([
            'status' => 'success',
            'message' => 'Delivery status updated successfully.',
            'delivery' => $delivery->load('order'),
        ]);
    }

    public function myDeliveries()
    {
        $user = auth()->user();

        $deliveries = Delivery::with(['order.items'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return ApiResponse::fromResult([
            'status' => 'success',
            'deliveries' => $deliveries,
        ]);
    }
}
