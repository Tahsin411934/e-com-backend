<x-app-layout>
    <div class="max-w-5xl mx-auto py-6 px-4">
        @if(session('success'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first() }}</div>
        @endif
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
                            $statusColors = ['draft'=>'bg-gray-100 text-gray-700','ordered'=>'bg-primary-light text-primary','partially_received'=>'bg-yellow-100 text-yellow-700','received'=>'bg-green-100 text-green-700','returned'=>'bg-orange-100 text-orange-700','cancelled'=>'bg-red-100 text-red-700'];
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

        @php($canReceiveItems = in_array($purchase_order->status, ['ordered', 'partially_received'], true) && auth()->user()->hasPermission('purchase-orders.edit'))
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="mb-3 flex flex-wrap items-start justify-between gap-2">
                <div>
                    <h3 class="text-sm font-semibold text-gray-700">Purchase items</h3>
                    <p class="mt-1 text-xs text-gray-500">Receive the quantity that arrived. Received units are added to inventory immediately.</p>
                </div>
                @if($purchase_order->status === 'partially_received')
                    <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-800">{{ $purchase_order->items->sum(fn($item) => max(0, $item->quantity - $item->received_quantity)) }} unit(s) still expected</span>
                @endif
            </div>
            @if($canReceiveItems)
                <form action="{{ route('purchase-orders.receive-items', $purchase_order->id) }}" method="POST" id="receiveItemsForm">
                    @csrf
            @endif
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2 px-3 text-gray-500 font-medium">#</th>
                            <th class="text-left py-2 px-3 text-gray-500 font-medium">Variant</th>
                            <th class="text-right py-2 px-3 text-gray-500 font-medium">Qty</th>
                            <th class="text-right py-2 px-3 text-gray-500 font-medium">Received</th>
                            @if($canReceiveItems)
                                <th class="text-right py-2 px-3 text-gray-500 font-medium">Remaining</th>
                                <th class="text-right py-2 px-3 text-gray-500 font-medium">Receive now</th>
                            @endif
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
                                @if($canReceiveItems)
                                    @php($remainingQuantity = max(0, $item->quantity - $item->received_quantity))
                                    <td class="py-2 px-3 text-right">{{ $remainingQuantity }}</td>
                                    <td class="py-2 px-3 text-right">
                                        <input type="number" name="received_quantities[{{ $item->id }}]" min="0" max="{{ $remainingQuantity }}" value="0" inputmode="numeric" class="w-24 rounded-md border border-gray-300 px-2 py-1.5 text-right focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20" aria-label="Receive quantity for {{ $item->variant->name ?? 'item' }}" {{ $remainingQuantity < 1 ? 'disabled' : '' }}>
                                    </td>
                                @endif
                                <td class="py-2 px-3 text-right">${{ number_format($item->unit_cost, 2) }}</td>
                                <td class="py-2 px-3 text-right">${{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($canReceiveItems)
                <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 pt-4">
                    <p class="text-xs text-gray-500">Set quantities to 0 for items that have not arrived yet. You can receive the remaining units later.</p>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-700">
                        <i class="fas fa-box-open"></i> Receive selected quantities
                    </button>
                </div>
                </form>
            @endif
        </div>

        @if(in_array($purchase_order->status, ['draft', 'ordered'], true) && auth()->user()->hasPermission('purchase-orders.edit'))
            <div class="flex gap-2">
                @if($purchase_order->status === 'draft')
                    <form action="{{ route('purchase-orders.update-status', $purchase_order->id) }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="status" value="ordered">
                        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm hover:bg-primary">Mark as Ordered</button>
                    </form>
                @endif
                @if(in_array($purchase_order->status, ['draft', 'ordered']))
                    <form action="{{ route('purchase-orders.update-status', $purchase_order->id) }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="status" value="cancelled">
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">Cancel Order</button>
                    </form>
                @endif
            </div>
        @endif

        @if(in_array($purchase_order->status, ['received', 'partially_received']) && auth()->user()->hasPermission('purchase-returns.create'))
            <div class="mt-4 flex gap-2">
                <a href="{{ route('purchase-returns.create', ['purchase_order_id' => $purchase_order->id]) }}"
                    class="px-4 py-2 bg-orange-500 text-white rounded-lg text-sm hover:bg-orange-600 inline-flex items-center">
                    <i class="fas fa-undo-alt mr-1"></i>Create Purchase Return
                </a>
            </div>
        @endif

        @if($purchase_order->status === 'returned')
            <div class="mt-4 bg-orange-50 border border-orange-200 text-orange-700 rounded-lg p-4 text-sm flex items-center gap-2">
                <i class="fas fa-undo-alt"></i>
                <span>This purchase order has been returned. View its <a href="{{ route('purchase-returns.index') }}" class="font-semibold underline">Purchase Return record</a>.</span>
            </div>
        @endif

        <!-- Payment Section -->
        <div class="mt-6 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-money-bill-wave text-emerald-600"></i> Payments
                </h2>
                @if($purchase_order->payment_status !== 'paid' && auth()->user()->hasPermission('supplier-payments.create'))
                    <button type="button" class="js-payment-drawer-open px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition">
                        <i class="fas fa-plus mr-1"></i> Add Payment
                    </button>
                @endif
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="rounded-lg bg-gray-50 border border-gray-200 p-4">
                    <div class="text-xs font-semibold text-gray-500 uppercase">Total</div>
                    <div class="mt-1 text-xl font-bold text-gray-900">৳{{ number_format((float) $purchase_order->total_amount, 2) }}</div>
                </div>
                <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-4">
                    <div class="text-xs font-semibold text-emerald-600 uppercase">Paid</div>
                    <div class="mt-1 text-xl font-bold text-emerald-700">৳{{ number_format((float) $purchase_order->paid_amount, 2) }}</div>
                </div>
                <div class="rounded-lg bg-rose-50 border border-rose-200 p-4">
                    <div class="text-xs font-semibold text-rose-600 uppercase">Due</div>
                    <div class="mt-1 text-xl font-bold text-rose-700">৳{{ number_format(max(0, (float) $purchase_order->total_amount - (float) $purchase_order->paid_amount), 2) }}</div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500">
                        <tr>
                            <th class="text-left px-4 py-3">Payment No</th>
                            <th class="text-left px-4 py-3">Date</th>
                            <th class="text-left px-4 py-3">Account</th>
                            <th class="text-left px-4 py-3">Method</th>
                            <th class="text-right px-4 py-3">Amount</th>
                            <th class="text-left px-4 py-3">Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr class="border-t">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $payment->payment_no }}</td>
                                <td class="px-4 py-3">{{ $payment->payment_date?->format('d M Y') ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $payment->account?->name ?? '-' }}</td>
                                <td class="px-4 py-3">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                                <td class="px-4 py-3 text-right font-bold text-emerald-700">৳{{ number_format((float) $payment->amount, 2) }}</td>
                                <td class="px-4 py-3">{{ $payment->reference_no ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-400">No payments recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Payment Drawer -->
    <div id="paymentDrawer" class="fixed inset-0 z-50 hidden">
        <div class="js-payment-drawer-close absolute inset-0 bg-black/40"></div>
        <div class="absolute right-0 top-0 h-full w-96 bg-white shadow-2xl max-w-[100vw] overflow-y-auto">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800">Record Payment</h3>
                <button type="button" class="js-payment-drawer-close text-gray-400 hover:text-gray-600 text-xl">&times;</button>
            </div>
            <form id="paymentForm" class="p-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount <span class="text-rose-500">*</span></label>
                    <input type="number" name="amount" id="pay_amount" step="0.01" min="0" required class="w-full border border-slate-300 rounded-md p-2 text-sm">
                    <p class="text-xs text-gray-400 mt-1">Due: ৳{{ number_format(max(0, (float) $purchase_order->total_amount - (float) $purchase_order->paid_amount), 2) }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Account <span class="text-rose-500">*</span></label>
                    <x-select2-dropdown
                        name="account_id"
                        id="pay_account_id"
                        :route="route('ajax.dropdown-search', 'accounts')"
                        placeholder="Select Account"
                        required
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="payment_date" id="pay_date" value="{{ date('Y-m-d') }}" class="w-full rounded-lg border border-slate-300 p-2 text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Method</label>
                    <select name="payment_method" id="pay_method" data-local-select2 class="w-full rounded-lg border border-slate-300 p-2 text-sm">
                        <option value="cash">Cash</option>
                        <option value="bank">Bank Transfer</option>
                        <option value="mobile_banking">Mobile Banking</option>
                        <option value="card">Card</option>
                        <option value="gateway">Gateway</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reference No</label>
                    <input type="text" name="reference_no" id="pay_reference_no" class="w-full rounded-lg border border-slate-300 p-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Note</label>
                    <textarea name="note" id="pay_note" rows="2" class="w-full rounded-lg border border-slate-300 p-2 text-sm"></textarea>
                </div>
                <div class="pt-2">
                    <button type="submit" class="w-full px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700">
                        <i class="fas fa-check mr-1"></i> Save Payment
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        (function () {
            var drawer = document.getElementById('paymentDrawer');

            $(document).on('click', '.js-payment-drawer-open', function () {
                drawer.classList.remove('hidden');
            });

            $(document).on('click', '.js-payment-drawer-close', function () {
                drawer.classList.add('hidden');
            });
        })();

        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            var form = this;
            var btn = form.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

            var data = new FormData(form);
            data.append('supplier_id', '{{ $purchase_order->supplier_id }}');
            data.append('purchase_order_id', '{{ $purchase_order->id }}');
            data.append('store_id', '{{ $purchase_order->store_id }}');

            fetch('{{ route("supplier-payments.store") }}', {
                method: 'POST',
                body: data,
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(res => res.json())
            .then(res => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check mr-1"></i> Save Payment';
                if (res.status === 'success') {
                    Toastify({
                        text: res.message,
                        duration: 3000,
                        gravity: 'bottom',
                        position: 'right',
                        style: { background: 'linear-gradient(135deg, #16a34a, #4ade80)' }
                    }).showToast();
                    setTimeout(() => location.reload(), 1200);
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check mr-1"></i> Save Payment';
                Swal.fire('Error', 'Server error occurred', 'error');
            });
        });
    </script>
    @endpush
</x-app-layout>
