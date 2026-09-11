<x-app-layout>
    <x-entity-crud
        id="pos-sale"
        createPermission="pos-sales"
        createPermission="pos-sales"
        createPermission="pos-sales"
        createPermission="pos-sales"
        createPermission="pos-sales"
        createPermission="pos-sales"
        createPermission="pos-sales"
        createPermission="pos-sales"
        createPermission="pos-sales"
        createPermission="pos-sales"
        createPermission="pos-sales"
        createPermission="pos-sales"
        createPermission="pos-sales"
        createPermission="pos-sales"
        createPermission="pos-sales"
        createPermission="pos-sales"
        createPermission="pos-sales"
        createPermission="pos-sales"
        createPermission="pos-sales"
        createPermission="pos-sales"
        createPermission="pos-sales"
        createPermission="pos-sales"
        createPermission="pos-sales"
        createPermission="pos-sales"
        createPermission="pos-sales"
        createPermission="pos-sales"
        createPermission="pos-sales"
        createPermission="pos-sales"
        createPermission="pos-sales"
        createPermission="pos-sales"
        createPermission="pos-sales"
        createPermission="pos-sales"
        createPermission="pos-sales"
        createPermission="pos-sales"
        createPermission="pos-sales"
        createPermission="pos-sales"
        title="POS Sales"
        icon="fa-solid fa-receipt"
        :columns="['Receipt #','Register','Store','User','Total','Payment','Status','Date','Action']"
        :dtColumns="[
            ['data' => 'receipt_number'],
            ['data' => 'register_name'],
            ['data' => 'store_name'],
            ['data' => 'user_name'],
            ['data' => 'total'],
            ['data' => 'payment_status'],
            ['data' => 'status'],
            ['data' => 'created_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        ajaxUrl="{{ route('pos-sales.dataTable') }}"
        storeUrl="{{ route('pos-sales.store') }}"
        updateUrl="{{ route('pos-sales.update', ':id') }}"
        showUrl="{{ route('pos-sales.show', ':id') }}"
        destroyUrl="{{ route('pos-sales.destroy', ':id') }}"
        drawerTitle="POS Sale"
        dataKey="data"
        idField="sale_id"
        :order="[[7, 'desc']]"
    >
        <div class="mb-4">
            <x-form-select label="Register" name="register_id" id="sale_register_id">
                @foreach($registers ?? [] as $register)
                    <option value="{{ $register['id'] }}">{{ $register['name'] }}</option>
                @endforeach
                <option value="" disabled selected>Select a register</option>
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-select label="User" name="user_id" id="sale_user_id">
                @foreach($users ?? [] as $user)
                    <option value="{{ $user['id'] }}">{{ $user['name'] }}</option>
                @endforeach
                <option value="" disabled selected>Select user</option>
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-input label="Subtotal" name="subtotal" id="sale_subtotal" type="number" step="0.01" placeholder="0.00" />
        </div>
        <div class="mb-4">
            <x-form-input label="Tax Amount" name="tax_amount" id="sale_tax_amount" type="number" step="0.01" placeholder="0.00" />
        </div>
        <div class="mb-4">
            <x-form-input label="Discount Amount" name="discount_amount" id="sale_discount_amount" type="number" step="0.01" placeholder="0.00" />
        </div>
        <div class="mb-4">
            <x-form-input label="Total" name="total" id="sale_total" type="number" step="0.01" placeholder="0.00" />
        </div>
        <div class="mb-4">
            <x-form-input label="Cash Amount" name="cash_amount" id="sale_cash_amount" type="number" step="0.01" placeholder="0.00" />
        </div>
        <div class="mb-4">
            <x-form-input label="Card Amount" name="card_amount" id="sale_card_amount" type="number" step="0.01" placeholder="0.00" />
        </div>
        <div class="mb-4">
            <x-form-input label="Other Amount" name="other_amount" id="sale_other_amount" type="number" step="0.01" placeholder="0.00" />
        </div>
        <div class="mb-4">
            <x-form-select label="Payment Status" name="payment_status" id="sale_payment_status">
                <option value="paid">Paid</option>
                <option value="partial">Partial</option>
                <option value="pending">Pending</option>
                <option value="refunded">Refunded</option>
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-select label="Status" name="status" id="sale_status">
                <option value="completed">Completed</option>
                <option value="voided">Voided</option>
                <option value="refunded">Refunded</option>
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-input label="Notes" name="notes" id="sale_notes" placeholder="Optional notes" />
        </div>
    </x-entity-crud>

    @push('scripts')
    <script>
        window.fillPosSaleForm = function(data) {
            $('#sale_register_id').val(data.register_id);
            $('#sale_user_id').val(data.user_id);
            $('#sale_subtotal').val(data.subtotal);
            $('#sale_tax_amount').val(data.tax_amount);
            $('#sale_discount_amount').val(data.discount_amount);
            $('#sale_total').val(data.total);
            $('#sale_cash_amount').val(data.cash_amount);
            $('#sale_card_amount').val(data.card_amount);
            $('#sale_other_amount').val(data.other_amount);
            $('#sale_payment_status').val(data.payment_status);
            $('#sale_status').val(data.status);
            $('#sale_notes').val(data.notes);
        };
    </script>
    @endpush
</x-app-layout>