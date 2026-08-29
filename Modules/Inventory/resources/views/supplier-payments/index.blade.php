<x-app-layout>
    <x-entity-crud
        id="supplierPayment"
        title="Supplier Payments"
        icon="fa-solid fa-money-bill-transfer"
        :columns="['Payment No','Supplier','PO Number','Account','Method','Amount','Date','Action']"
        :dtColumns="[
            ['data' => 'payment_no'],
            ['data' => 'supplier_name'],
            ['data' => 'po_number'],
            ['data' => 'account_name'],
            ['data' => 'payment_method'],
            ['data' => 'amount'],
            ['data' => 'payment_date'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        ajaxUrl="{{ route('supplier-payments.dataTable') }}"
        storeUrl="{{ route('supplier-payments.store') }}"
        showUrl="#"
        destroyUrl="{{ route('supplier-payments.destroy', ':id') }}"
        drawerTitle="Supplier Payment"
        dataKey="data"
        idField="payment_id"
        :order="[[6, 'desc']]"
    >
        <div class="mb-4">
            <x-form-select label="Supplier *" name="supplier_id" id="supplierPayment_supplier_id" required>
                <option value="">Select Supplier</option>
                @foreach($suppliers ?? [] as $supplier)
                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                @endforeach
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-select label="Purchase Order" name="purchase_order_id" id="supplierPayment_purchase_order_id">
                <option value="">Select PO (optional)</option>
                @foreach($purchaseOrders ?? [] as $po)
                    <option value="{{ $po->id }}" data-supplier-id="{{ $po->supplier_id }}" data-store-id="{{ $po->store_id }}">{{ $po->po_number }}</option>
                @endforeach
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-select label="Store" name="store_id" id="supplierPayment_store_id">
                <option value="">Select Store</option>
                @foreach($stores ?? [] as $store)
                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                @endforeach
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-select label="Payment Account *" name="account_id" id="supplierPayment_account_id" required>
                <option value="">Select Account</option>
                @foreach($accounts ?? [] as $account)
                    <option value="{{ $account->id }}">{{ $account->name }} - ৳{{ number_format((float) $account->current_balance, 2) }}</option>
                @endforeach
            </x-form-select>
            <p class="mt-1 text-xs text-gray-400">The paid amount is deducted from this account's balance & a posted transaction is created automatically.</p>
        </div>
        <div class="mb-4"><x-form-input label="Amount *" name="amount" id="supplierPayment_amount" type="number" step="0.01" min="0" required /></div>
        <div class="mb-4"><x-form-input label="Payment Date *" name="payment_date" id="supplierPayment_payment_date" type="date" value="{{ date('Y-m-d') }}" required /></div>
        <div class="mb-4">
            <x-form-select label="Payment Method" name="payment_method" id="supplierPayment_payment_method">
                <option value="cash">Cash</option>
                <option value="bank">Bank Transfer</option>
                <option value="mobile_banking">Mobile Banking</option>
                <option value="card">Card</option>
                <option value="gateway">Gateway</option>
                <option value="other">Other</option>
            </x-form-select>
        </div>
        <div class="mb-4"><x-form-input label="Reference No" name="reference_no" id="supplierPayment_reference_no" /></div>
        <div class="mb-4"><x-form-textarea label="Note" name="note" id="supplierPayment_note" rows="2" /></div>
    </x-entity-crud>

    @push('scripts')
    <script>
        // Auto-fill supplier & store from selected PO.
        $('#supplierPayment_purchase_order_id').on('change', function() {
            var opt = $(this).find(':selected');
            if (opt.data('supplier-id')) $('#supplierPayment_supplier_id').val(opt.data('supplier-id'));
            if (opt.data('store-id')) $('#supplierPayment_store_id').val(opt.data('store-id'));
        });
    </script>
    @endpush
</x-app-layout>