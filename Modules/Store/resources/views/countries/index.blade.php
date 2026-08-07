<x-app-layout>
    <div class="p-4">
        <div class="flex flex-col md:flex-row md:items-end gap-4 mb-5 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <div class="w-full md:w-auto flex items-end">
                <button id="resetCountryFilters"
                    class="px-4 py-2 text-sm font-medium text-white bg-gray-700 hover:bg-gray-800 rounded-lg transition active:scale-95">
                    Reset
                </button>
            </div>
        </div>

        <x-entity-crud
            id="country"
            title="Countries"
            icon="fa-solid fa-globe"
            :columns="['ISO2','Name','Action']"
            :dtColumns="[
                ['data' => 'iso2'],
                ['data' => 'name'],
                ['data' => 'action', 'orderable' => false, 'searchable' => false],
            ]"
            ajaxUrl="{{ route('countries.dataTable') }}"
            storeUrl="{{ route('countries.store') }}"
            updateUrl="{{ route('countries.update', ':id') }}"
            showUrl="{{ route('countries.show', ':id') }}"
            destroyUrl="{{ route('countries.destroy', ':id') }}"
            drawerTitle="Country"
            dataKey="country"
            idField="country_id"
            :order="[[1, 'asc']]"
        >
            <div class="mb-4">
                <x-form-input label="ISO2 Code" name="iso2" id="country_iso2" placeholder="US" maxlength="2" required />
            </div>
            <div class="mb-4">
                <x-form-input label="Name" name="name" id="country_name" placeholder="United States" required />
            </div>
        </x-entity-crud>
    </div>

    @push('scripts')
    <script>
        window.fillCountryForm = function(data) {
            $('#country_iso2').val(data.iso2);
            $('#country_name').val(data.name);
        };
    </script>
    @endpush
</x-app-layout>