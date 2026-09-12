<x-app-layout>
    <x-entity-crud
        id="refund"
        createPermission="refunds"
        title="Refunds"
        icon="fa-solid fa-undo"
        :columns="['Order','Payment','Amount','Status','Reason','Created','Action']"
        :dtColumns="[
            ['data' => 'order_number'],
            ['data' => 'payment_method'],
            ['data' => 'amount'],
            ['data' => 'status'],
            ['data' => 'reason'],
            ['data' => 'created_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        ajaxUrl="{{ route('refunds.dataTable') }}"
        storeUrl="{{ route('refunds.store') }}"
        updateUrl="{{ route('refunds.update', ':id') }}"
        showUrl="{{ route('refunds.show', ':id') }}"
        destroyUrl="{{ route('refunds.destroy', ':id') }}"
        drawerTitle="Refund"
        dataKey="data"
        idField="refund_id"
        :order="[[4, 'desc']]"
    >
        <div class="mb-4">
            <x-form-input label="Order ID" name="order_id" id="refund_order_id" type="number" placeholder="Order ID" required />
        </div>
        <div class="mb-4">
            <x-form-input label="Payment ID" name="payment_id" id="refund_payment_id" type="number" placeholder="Payment ID" required />
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <x-form-input label="Amount" name="amount" id="refund_amount" type="number" step="0.01" placeholder="0.00" required />
            <x-form-select label="Status" name="status" id="refund_status">
                <option value="pending">Pending</option>
                <option value="processed">Processed</option>
                <option value="failed">Failed</option>
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-textarea label="Reason" name="reason" id="refund_reason" placeholder="Reason for refund" rows="3" />
        </div>
    </x-entity-crud>

    @push('scripts')
    <script>
Crud.register('refund', 'fill', function (data) {
            $('#refund_order_id').val(data.order_id);
            $('#refund_payment_id').val(data.payment_id);
            $('#refund_amount').val(data.amount);
            $('#refund_status').val(data.status);
            $('#refund_reason').val(data.reason || '');
        });
    </script>
    @endpush
</x-app-layout>
