<x-app-layout>
    <div class="p-4">
        <x-data-table
            id="accountTransactionsTable"
            title="Transaction Ledger"
            icon="fa-solid fa-book"
            :buttonId="null"
            :columns="['Transaction No','Type','Account','Category','Debit','Credit','Date','Reference']"
            :dtColumns="[
                ['data' => 'transaction_no'],
                ['data' => 'type'],
                ['data' => 'account'],
                ['data' => 'category'],
                ['data' => 'total_debit'],
                ['data' => 'total_credit'],
                ['data' => 'transaction_date'],
                ['data' => 'reference_no'],
            ]"
            ajaxUrl="{{ route('account-transactions.dataTable') }}"
            :order="[[6, 'desc']]"
        />
    </div>
</x-app-layout>
