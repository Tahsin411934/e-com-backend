<x-app-layout>
    <x-entity-crud
        id="accountaccount"
        title="Accounts"
        icon="fa-solid fa-wallet"
        :columns="['Name','Code','Type','Currency','Opening Balance','Current Balance','Status','Created At','Action']"
        :dtColumns="[
            ['data' => 'name'],
            ['data' => 'code'],
            ['data' => 'type'],
            ['data' => 'currency_code'],
            ['data' => 'opening_balance'],
            ['data' => 'current_balance'],
            ['data' => 'is_active'],
            ['data' => 'created_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        ajaxUrl="{{ route('account-accounts.dataTable') }}"
        storeUrl="{{ route('account-accounts.store') }}"
        updateUrl="{{ route('account-accounts.update', ':id') }}"
        showUrl="{{ route('account-accounts.show', ':id') }}"
        destroyUrl="{{ route('account-accounts.destroy', ':id') }}"
        drawerTitle="Account"
        dataKey="account"
        idField="account_id"
    >
        <div class="mb-4"><x-form-input label="Name" name="name" id="accountaccount_name" required /></div>
        <div class="mb-4"><x-form-input label="Code" name="code" id="accountaccount_code" placeholder="AUTO if blank" /></div>
        <div class="mb-4">
            <x-form-select label="Type" name="type" id="accountaccount_type">
                <option value="cash">Cash</option>
                <option value="bank">Bank</option>
                <option value="mobile_banking">Mobile Banking</option>
                <option value="card">Card</option>
                <option value="gateway">Gateway</option>
                <option value="other">Other</option>
            </x-form-select>
        </div>
        <div class="mb-4"><x-form-input label="Currency" name="currency_code" id="accountaccount_currency_code" value="BDT" /></div>
        <div class="mb-4"><x-form-input label="Opening Balance" name="opening_balance" id="accountaccount_opening_balance" type="number" step="0.01" value="0" /></div>
        <div class="mb-4"><x-form-input label="Bank Name" name="bank_name" id="accountaccount_bank_name" /></div>
        <div class="mb-4"><x-form-input label="Branch Name" name="branch_name" id="accountaccount_branch_name" /></div>
        <div class="mb-4"><x-form-input label="Account Number" name="account_number" id="accountaccount_account_number" /></div>
        <div class="mb-4"><x-form-input label="Account Holder Name" name="account_holder_name" id="accountaccount_account_holder_name" /></div>
        <div class="mb-4">
            <label class="flex items-center gap-2 text-sm text-gray-700"><input type="checkbox" name="is_default" value="1" id="accountaccount_is_default"> Default account for this type</label>
        </div>
        <div class="mb-4">
            <label class="flex items-center gap-2 text-sm text-gray-700"><input type="checkbox" name="is_active" value="1" id="accountaccount_is_active" checked> Active</label>
        </div>
    </x-entity-crud>

    @push('scripts')
    <script>
        window.fillAccountaccountForm = function(data) {
            $('#accountaccount_name').val(data.name);
            $('#accountaccount_code').val(data.code);
            $('#accountaccount_type').val(data.type);
            $('#accountaccount_currency_code').val(data.currency_code);
            $('#accountaccount_opening_balance').val(data.opening_balance);
            $('#accountaccount_bank_name').val(data.bank_name);
            $('#accountaccount_branch_name').val(data.branch_name);
            $('#accountaccount_account_number').val(data.account_number);
            $('#accountaccount_account_holder_name').val(data.account_holder_name);
            $('#accountaccount_is_default').prop('checked', !!data.is_default);
            $('#accountaccount_is_active').prop('checked', !!data.is_active);
        };
    </script>
    @endpush
</x-app-layout>
