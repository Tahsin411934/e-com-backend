<x-app-layout>
    <x-entity-crud
        id="order"
        title="Orders"
        icon="fa-solid fa-receipt"
        :columns="['Order #','User','Store','Status','Payment','Total','Created','Action']"
        :dtColumns="[
            ['data' => 'order_number'],
            ['data' => 'user_email'],
            ['data' => 'store_name'],
            ['data' => 'status'],
            ['data' => 'payment_status'],
            ['data' => 'grand_total'],
            ['data' => 'created_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        ajaxUrl="{{ route('orders.dataTable') }}"
        storeUrl="{{ route('orders.store') }}"
        updateUrl="{{ route('orders.update', ':id') }}"
        showUrl="{{ route('orders.show', ':id') }}"
        destroyUrl="{{ route('orders.destroy', ':id') }}"
        drawerTitle="Order"
        dataKey="data"
        idField="order_id"
        :order="[[0, 'desc']]"
    >
        <div class="mb-4">
            <x-form-input label="Order Number" name="order_number" id="order_order_number" placeholder="ORD-XXXXX" required />
        </div>
        <div class="mb-4">
            <x-form-input label="Customer Note" name="customer_note" id="order_customer_note" placeholder="Customer notes" />
        </div>
        <div class="grid grid-cols-3 gap-4 mb-4">
            <x-form-select label="Source" name="source" id="order_source">
                <option value="web">Web</option>
                <option value="mobile">Mobile</option>
                <option value="pos">POS</option>
                <option value="admin">Admin</option>
                <option value="marketplace">Marketplace</option>
            </x-form-select>
            <x-form-select label="Status" name="status" id="order_status">
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="processing">Processing</option>
                <option value="ready">Ready</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
                <option value="refunded">Refunded</option>
            </x-form-select>
            <x-form-select label="Payment Status" name="payment_status" id="order_payment_status">
                <option value="unpaid">Unpaid</option>
                <option value="authorized">Authorized</option>
                <option value="paid">Paid</option>
                <option value="partially_refunded">Partially Refunded</option>
                <option value="refunded">Refunded</option>
                <option value="failed">Failed</option>
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-select label="Fulfillment Status" name="fulfillment_status" id="order_fulfillment_status">
                <option value="unfulfilled">Unfulfilled</option>
                <option value="partial">Partial</option>
                <option value="fulfilled">Fulfilled</option>
                <option value="returned">Returned</option>
            </x-form-select>
        </div>
    </x-entity-crud>

    @push('scripts')
    <script>
        window.fillOrderForm = function(data) {
            $('#order_order_number').val(data.order_number);
            $('#order_customer_note').val(data.customer_note || '');
            $('#order_source').val(data.source);
            $('#order_status').val(data.status);
            $('#order_payment_status').val(data.payment_status);
            $('#order_fulfillment_status').val(data.fulfillment_status);
        };
    </script>
    @endpush
</x-app-layout>