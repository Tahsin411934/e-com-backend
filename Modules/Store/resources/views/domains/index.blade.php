<x-app-layout>
    <x-data-table
        id="storeDomainsTable"
        title="Store Domains"
        icon="fa-solid fa-globe"
        :columns="['Store','Domain','Type','Primary','SSL Status','Verified At','Added At']"
        :dtColumns="[
            ['data' => 'store_name', 'name' => 'store.name'],
            ['data' => 'domain'],
            ['data' => 'type'],
            ['data' => 'is_primary'],
            ['data' => 'ssl_status'],
            ['data' => 'verified_at'],
            ['data' => 'created_at'],
        ]"
        ajaxUrl="{{ route('store-domains.dataTable') }}"
        :exportButtons="true"
        :order="[[6, 'desc']]"
    />
</x-app-layout>
