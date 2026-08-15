<x-app-layout>
    <x-entity-crud
        id="accountinvestment"
        title="Investments"
        icon="fa-solid fa-chart-line"
        :columns="['Investment No','Title','Type','Account','Amount','Date','Expected Return','Actual Return','Status','Action']"
        :dtColumns="[
            ['data' => 'investment_no'],
            ['data' => 'title'],
            ['data' => 'investment_type'],
            ['data' => 'account_name'],
            ['data' => 'amount'],
            ['data' => 'investment_date'],
            ['data' => 'expected_return'],
            ['data' => 'actual_return'],
            ['data' => 'status'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        ajaxUrl="{{ route('account-investments.dataTable') }}"
        storeUrl="{{ route('account-investments.store') }}"
        updateUrl="{{ route('account-investments.update', ':id') }}"
        showUrl="{{ route('account-investments.show', ':id') }}"
        destroyUrl="{{ route('account-investments.destroy', ':id') }}"
        drawerTitle="Investment"
        dataKey="investment"
        idField="investment_id"
    >
        <div class="mb-4">
            <x-form-select label="Account" name="account_id" id="accountinvestment_account_id" required>
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}">{{ $account->name }} - ৳{{ number_format((float) $account->current_balance, 2) }}</option>
                @endforeach
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-select label="Investment Type" name="investment_type" id="accountinvestment_investment_type" required>
                @foreach($investmentTypes as $type)
                    <option value="{{ $type->slug }}" {{ old('investment_type') == $type->slug ? 'selected' : '' }}>
                        {{ $type->name }}
                    </option>
                @endforeach
            </x-form-select>
            <p class="mt-1 text-xs text-gray-400">
                Types are managed dynamically under
                <a href="{{ route('account-categories.index') }}" class="text-primary underline hover:text-primary-dark">Account &rsaquo; Categories</a>
                (choose Type = Investment).
            </p>
        </div>
        <div class="mb-4"><x-form-input label="Amount" name="amount" id="accountinvestment_amount" type="number" step="0.01" required /></div>
        <div class="mb-4"><x-form-input label="Currency" name="currency_code" id="accountinvestment_currency_code" value="BDT" /></div>
        <div class="mb-4"><x-form-input label="Title" name="title" id="accountinvestment_title" required /></div>
        <div class="mb-4"><x-form-input label="Investment Date" name="investment_date" id="accountinvestment_investment_date" type="date" value="{{ now()->toDateString() }}" required /></div>
        <div class="mb-4"><x-form-input label="Expected Return" name="expected_return" id="accountinvestment_expected_return" type="number" step="0.01" value="0" /></div>
        <div class="mb-4"><x-form-input label="Actual Return" name="actual_return" id="accountinvestment_actual_return" type="number" step="0.01" value="0" /></div>
        <div class="mb-4"><x-form-select label="Status" name="status" id="accountinvestment_status">
            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
            <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
            <option value="failed" {{ old('status') == 'failed' ? 'selected' : '' }}>Failed</option>
        </x-form-select></div>
        <div class="mb-4"><x-form-input label="Partner Name" name="partner_name" id="accountinvestment_partner_name" /></div>
        <div class="mb-4"><x-form-input label="Reference No" name="reference_no" id="accountinvestment_reference_no" /></div>
        <div class="mb-4"><x-form-input label="Note" name="note" id="accountinvestment_note" /></div>
    </x-entity-crud>

    @push('scripts')
    <script>
        window.fillAccountinvestmentForm = function(data) {
            $('#accountinvestment_account_id').val(data.account_id);
            $('#accountinvestment_investment_type').val(data.investment_type);
            $('#accountinvestment_amount').val(data.amount);
            $('#accountinvestment_currency_code').val(data.currency_code);
            $('#accountinvestment_title').val(data.title);
            $('#accountinvestment_investment_date').val(data.investment_date);
            $('#accountinvestment_expected_return').val(data.expected_return);
            $('#accountinvestment_actual_return').val(data.actual_return);
            $('#accountinvestment_status').val(data.status);
            $('#accountinvestment_partner_name').val(data.partner_name);
            $('#accountinvestment_reference_no').val(data.reference_no);
            $('#accountinvestment_note').val(data.note);
        };
    </script>
    @endpush
</x-app-layout>