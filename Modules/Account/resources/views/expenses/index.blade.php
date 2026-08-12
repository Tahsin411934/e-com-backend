<x-app-layout>
    <x-entity-crud
        id="accountexpense"
        title="Expenses"
        icon="fa-solid fa-money-bill-wave"
        :columns="['Expense No','Account','Category','Amount','Date','Vendor','Reference','Action']"
        :dtColumns="[
            ['data' => 'expense_no'],
            ['data' => 'account_name'],
            ['data' => 'category_name'],
            ['data' => 'amount'],
            ['data' => 'expense_date'],
            ['data' => 'vendor_name'],
            ['data' => 'reference_no'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        ajaxUrl="{{ route('account-expenses.dataTable') }}"
        storeUrl="{{ route('account-expenses.store') }}"
        updateUrl="{{ route('account-expenses.update', ':id') }}"
        showUrl="{{ route('account-expenses.show', ':id') }}"
        destroyUrl="{{ route('account-expenses.destroy', ':id') }}"
        drawerTitle="Expense"
        dataKey="expense"
        idField="expense_id"
    >
        <div class="mb-4">
            <x-form-select label="Pay From" name="account_id" id="accountexpense_account_id" required>
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}">{{ $account->name }} - ৳{{ number_format((float) $account->current_balance, 2) }}</option>
                @endforeach
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-select label="Category" name="category_id" id="accountexpense_category_id" required>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </x-form-select>
        </div>
        <div class="mb-4"><x-form-input label="Amount" name="amount" id="accountexpense_amount" type="number" step="0.01" required /></div>
        <div class="mb-4"><x-form-input label="Currency" name="currency_code" id="accountexpense_currency_code" value="BDT" /></div>
        <div class="mb-4"><x-form-input label="Expense Date" name="expense_date" id="accountexpense_expense_date" type="date" value="{{ now()->toDateString() }}" required /></div>
        <div class="mb-4"><x-form-input label="Vendor Name" name="vendor_name" id="accountexpense_vendor_name" /></div>
        <div class="mb-4"><x-form-input label="Reference No" name="reference_no" id="accountexpense_reference_no" /></div>
        <div class="mb-4"><x-form-textarea label="Note" name="note" id="accountexpense_note" /></div>
    </x-entity-crud>

    @push('scripts')
    <script>
        window.fillAccountexpenseForm = function(data) {
            $('#accountexpense_account_id').val(data.account_id);
            $('#accountexpense_category_id').val(data.category_id);
            $('#accountexpense_amount').val(data.amount);
            $('#accountexpense_currency_code').val(data.currency_code);
            $('#accountexpense_expense_date').val(data.expense_date);
            $('#accountexpense_vendor_name').val(data.vendor_name);
            $('#accountexpense_reference_no').val(data.reference_no);
            $('#accountexpense_note').val(data.note);
        };
    </script>
    @endpush
</x-app-layout>
