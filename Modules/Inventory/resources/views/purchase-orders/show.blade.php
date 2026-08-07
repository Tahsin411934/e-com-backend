<x-app-layout>
    <div class="max-w-5xl mx-auto py-6 px-4">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-file-invoice mr-2 text-primary"></i>
                Purchase Order: {{ $purchase_order->po_number }}
            </h1>
            <a href="{{ route('purchase-orders.index') }}" class="text-sm text-primary hover:text-primary-hover">
                <i class="fas fa-arrow-left mr-1"></i>Back to List
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-sm font-semibold text-gray-500 mb-3">Order Details</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">PO Number:</span><span class="font-medium">{{ $purchase_order->po_number }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Supplier:</span><span class="font-medium">{{ $purchase_order->supplier->name ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Store:</span><span class="font-medium">{{ $purchase_order->store->name ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Order Date:</span><span class="font-medium">{{ $purchase_order->order_date?->format('d M Y') ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Expected Delivery:</span><span class="font-medium">{{ $purchase_order->expected_delivery_date?->format('d M Y') ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Received Date:</span><span class="font-medium">{{ $purchase_order->received_date?->format('d M Y') ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Created By:</span><span class="font-medium">{{ $purchase_order->creator->name ?? '-' }}</span></div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-sm font-semibold text-gray-500 mb-3">Status & Totals</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Status:</span>
                        @php
                            $statusColors = ['draft'=>'bg-gray-100 text-gray-700','ordered'=>'bg-primary-light text-primary','partially_received'=>'bg-yellow-100 text-yellow-700','received'=>'bg-green-100 text-green-700','cancelled'=>'bg-red-100 text-red-700'];
                        @endphp
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusColors[$purchase_order->status] ?? '' }}">{{ ucfirst(str_replace('_', ' ', $purchase_order->status)) }}</span>
                    </div>
                    <div class="flex justify-between"><span class="text-gray-500">Payment Status:</span>
                        @php
                            $payColors = ['unpaid'=>'bg-red-100 text-red-700','partial'=>'bg-yellow-100 text-yellow-700','paid'=>'bg-green-100 text-green-700'];
                        @endphp
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $payColors[$purchase_order->payment_status] ?? '' }}">{{ ucfirst(str_replace('_', ' ', $purchase_order->payment_status)) }}</span>
                    </div>
                    <div class="flex justify-between"><span class="text-gray-500">Subtotal:</span><span class="font-medium">${{ number_format($purchase_order->total_amount - $purchase_order->shipping_cost - $purchase_order->tax_amount + $purchase_order->discount_amount, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Shipping:</span><span class="font-medium">${{ number_format($purchase_order->shipping_cost, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Tax:</span><span class="font-medium">${{ number_format($purchase_order->tax_amount, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Discount:</span><span class="font-medium">-${{ number_format($purchase_order->discount_amount, 2) }}</span></div>
                    <div class="flex justify-between border-t pt-2"><span class="text-gray-700 font-semibold">Total:</span><span class="font-bold text-lg">${{ number_format($purchase_order->total_amount, 2) }}</span></div>
                </div>
            </div>
        </div>

        @if($purchase_order->notes)
            <div class="bg-white rounded-lg shadow p-4 mb-6">
                <h3 class="text-sm font-semibold text-gray-500 mb-2">Notes</h3>
                <p class="text-sm text-gray-700">{{ $purchase_order->notes }}</p>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <h3 class="text-sm font-semibold text-gray-500 mb-3">Items</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2 px-3 text-gray-500 font-medium">#</th>
                            <th class="text-left py-2 px-3 text-gray-500 font-medium">Variant</th>
                            <th class="text-right py-2 px-3 text-gray-500 font-medium">Qty</th>
                            <th class="text-right py-2 px-3 text-gray-500 font-medium">Received</th>
                            <th class="text-right py-2 px-3 text-gray-500 font-medium">Unit Cost</th>
                            <th class="text-right py-2 px-3 text-gray-500 font-medium">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchase_order->items as $index => $item)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-2 px-3">{{ $index + 1 }}</td>
                                <td class="py-2 px-3">{{ $item->variant->name ?? 'N/A' }}</td>
                                <td class="py-2 px-3 text-right">{{ $item->quantity }}</td>
                                <td class="py-2 px-3 text-right">{{ $item->received_quantity }}</td>
                                <td class="py-2 px-3 text-right">${{ number_format($item->unit_cost, 2) }}</td>
                                <td class="py-2 px-3 text-right">${{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if(in_array($purchase_order->status, ['draft', 'ordered', 'partially_received']))
            <div class="flex gap-2">
                @if($purchase_order->status === 'draft')
                    <form action="{{ route('purchase-orders.update-status', $purchase_order->id) }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="status" value="ordered">
                        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm hover:bg-primary">Mark as Ordered</button>
                    </form>
                @endif
                @if(in_array($purchase_order->status, ['ordered', 'partially_received']))
                    <form action="{{ route('purchase-orders.update-status', $purchase_order->id) }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="status" value="received">
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700">Mark as Received</button>
                    </form>
                @endif
                @if(in_array($purchase_order->status, ['draft', 'ordered']))
                    <form action="{{ route('purchase-orders.update-status', $purchase_order->id) }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="status" value="cancelled">
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">Cancel Order</button>
                    </form>
                @endif
                @if($purchase_order->payment_status !== 'paid' && $purchase_order->status !== 'cancelled')
                    <form action="{{ route('purchase-orders.update-status', $purchase_order->id) }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="payment_status" value="paid">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">Mark as Paid</button>
                    </form>
                @endif
            </div>
        @endif
    </div>
</x-app-layout>