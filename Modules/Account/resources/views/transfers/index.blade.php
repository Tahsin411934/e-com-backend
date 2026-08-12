<x-app-layout>
    <x-entity-crud
        id="accounttransfer"
        title="Transfers"
        icon="fa-solid fa-right-left"
        :columns="['Transfer No','From','To','Amount','Fee','Transferred At','Reference','Action']"
        :dtColumns="[
            ['data' => 'transfer_no'],
            ['data' => 'from_account'],
            ['data' => 'to_account'],
            ['data' => 'amount'],
            ['data' => 'transfer_fee'],
            ['data' => 'transferred_at'],
            ['data' => 'reference_no'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        ajaxUrl="{{ route('account-transfers.dataTable') }}"
        storeUrl="{{ route('account-transfers.store') }}"
        updateUrl="#"
        showUrl="#"
        destroyUrl="#"
        drawerTitle="Transfer"
        dataKey="transfer"
        idField="transfer_id"
    >
        <div class="mb-4">
            <x-form-select label="From Account" name="from_account_id" id="accounttransfer_from_account_id" required>
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}">{{ $account->name }} - ৳{{ number_format((float) $account->current_balance, 2) }}</option>
                @endforeach
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-select label="To Account" name="to_account_id" id="accounttransfer_to_account_id" required>
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}">{{ $account->name }} - ৳{{ number_format((float) $account->current_balance, 2) }}</option>
                @endforeach
            </x-form-select>
        </div>
        <div class="mb-4"><x-form-input label="Amount" name="amount" id="accounttransfer_amount" type="number" step="0.01" required /></div>
        <div class="mb-4"><x-form-input label="Transfer Fee" name="transfer_fee" id="accounttransfer_transfer_fee" type="number" step="0.01" value="0" /></div>
        <div class="mb-4"><x-form-input label="Currency" name="currency_code" id="accounttransfer_currency_code" value="BDT" /></div>
        <div class="mb-4"><x-form-input label="Transferred At" name="transferred_at" id="accounttransfer_transferred_at" type="datetime-local" value="{{ now()->format('Y-m-d\TH:i') }}" required /></div>
        <div class="mb-4"><x-form-input label="Reference No" name="reference_no" id="accounttransfer_reference_no" /></div>
        <div class="mb-4"><x-form-textarea label="Note" name="note" id="accounttransfer_note" /></div>
    </x-entity-crud>
</x-app-layout>
