<x-app-layout>
    <x-entity-crud
        id="payment"
        title="Payments"
        icon="fa-solid fa-credit-card"
        :columns="['Order','Provider','Method','Status','Amount','Paid At','Created','Action']"
        :dtColumns="[
            ['data' => 'order_number'],
            ['data' => 'provider'],
            ['data' => 'method'],
            ['data' => 'status'],
            ['data' => 'amount'],
            ['data' => 'paid_at'],
            ['data' => 'created_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        ajaxUrl="{{ route('payments.dataTable') }}"
        storeUrl="{{ route('payments.store') }}"
        updateUrl="{{ route('payments.update', ':id') }}"
        showUrl="{{ route('payments.show', ':id') }}"
        destroyUrl="{{ route('payments.destroy', ':id') }}"
        drawerTitle="Payment"
        dataKey="data"
        idField="payment_id"
        :order="[[5, 'desc']]"
    >
        <div class="mb-4">
            <x-form-input label="Order ID" name="order_id" id="payment_order_id" type="number" placeholder="Order ID" required />
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <x-form-input label="Provider" name="provider" id="payment_provider" placeholder="e.g. Stripe" required />
            <x-form-input label="Provider Payment ID" name="provider_payment_id" id="payment_provider_payment_id" placeholder="txn_xxx" />
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <x-form-select label="Method" name="method" id="payment_method">
                <option value="card">Card</option>
                <option value="cash">Cash</option>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="wallet">Wallet</option>
                <option value="cod">COD</option>
                <option value="gift_card">Gift Card</option>
                <option value="other">Other</option>
            </x-form-select>
            <x-form-select label="Status" name="status" id="payment_status">
                <option value="pending">Pending</option>
                <option value="authorized">Authorized</option>
                <option value="captured">Captured</option>
                <option value="failed">Failed</option>
                <option value="cancelled">Cancelled</option>
                <option value="refunded">Refunded</option>
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-input label="Amount" name="amount" id="payment_amount" type="number" step="0.01" placeholder="0.00" required />
        </div>
    </x-entity-crud>

    @push('scripts')
    <script>
        window.fillPaymentForm = function(data) {
            $('#payment_order_id').val(data.order_id);
            $('#payment_provider').val(data.provider);
            $('#payment_provider_payment_id').val(data.provider_payment_id || '');
            $('#payment_method').val(data.method);
            $('#payment_status').val(data.status);
            $('#payment_amount').val(data.amount);
        };
    </script>
    @endpush
</x-app-layout>