<x-app-layout>
    <x-data-table
        id="customerTable"
        title="Customers"
        icon="fa-solid fa-user-group"
        :columns="['Customer','Email','Phone','Store','Status','Last Login','Created At']"
        :dtColumns="[
            ['data' => 'full_name'],
            ['data' => 'email'],
            ['data' => 'phone'],
            ['data' => 'store_name'],
            ['data' => 'status'],
            ['data' => 'last_login_at'],
            ['data' => 'created_at'],
        ]"
        ajaxUrl="{{ route('customers.dataTable') }}"
        :order="[[6, 'desc']]"
    />
</x-app-layout>
