<x-app-layout>
    <div class="p-4">
        <div class="flex flex-col md:flex-row md:items-end gap-4 mb-5 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <div class="flex flex-col w-full md:w-1/4">
                <x-form-select label="Country" id="filter_address_country" class="dt-filter-addressTable">
                    <option value="">All Countries</option>
                    @foreach ($countries as $country)
                        <option value="{{ $country['id'] }}">{{ $country['name'] }}</option>
                    @endforeach
                </x-form-select>
            </div>
            <div class="flex flex-col w-full md:w-1/4">
                <x-form-select label="Store" id="filter_address_store" class="dt-filter-addressTable">
                    <option value="">All Stores</option>
                    @foreach ($stores as $store)
                        <option value="{{ $store['id'] }}">{{ $store['name'] }}</option>
                    @endforeach
                </x-form-select>
            </div>
            <div class="w-full md:w-auto flex items-end">
                <button id="resetAddressFilters"
                    class="px-4 py-2 text-sm font-medium text-white bg-gray-700 hover:bg-gray-800 rounded-lg transition active:scale-95">
                    Reset
                </button>
            </div>
        </div>

        <x-entity-crud
            id="address"
            title="Addresses"
            icon="fa-solid fa-address-book"
            :columns="['Label','Contact','Address','City','Country','Default','Created At','Action']"
            :dtColumns="[
                ['data' => 'label'],
                ['data' => 'contact_name'],
                ['data' => 'address_line1'],
                ['data' => 'city'],
                ['data' => 'country_name'],
                ['data' => 'is_default'],
                ['data' => 'created_at'],
                ['data' => 'action', 'orderable' => false, 'searchable' => false],
            ]"
            ajaxUrl="{{ route('addresses.dataTable') }}"
            storeUrl="{{ route('addresses.store') }}"
            updateUrl="{{ route('addresses.update', ':id') }}"
            showUrl="{{ route('addresses.show', ':id') }}"
            destroyUrl="{{ route('addresses.destroy', ':id') }}"
            drawerTitle="Address"
            dataKey="address"
            idField="address_id"
            :order="[[6, 'desc']]"
        >
            <div class="mb-4">
                <x-form-input label="Label" name="label" id="address_label" placeholder="Home, Office, etc." />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <x-form-input label="Contact Name" name="contact_name" id="address_contact_name" placeholder="John Doe" />
                </div>
                <div class="mb-4">
                    <x-form-input label="Contact Phone" name="contact_phone" id="address_contact_phone" placeholder="+1234567890" />
                </div>
            </div>
            <div class="mb-4">
                <x-form-input label="Address Line 1" name="address_line1" id="address_line1" placeholder="123 Main St" required />
            </div>
            <div class="mb-4">
                <x-form-input label="Address Line 2" name="address_line2" id="address_line2" placeholder="Apt 4B" />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="mb-4">
                    <x-form-input label="City" name="city" id="address_city" placeholder="New York" required />
                </div>
                <div class="mb-4">
                    <x-form-input label="State" name="state" id="address_state" placeholder="NY" />
                </div>
                <div class="mb-4">
                    <x-form-input label="Postal Code" name="postal_code" id="address_postal_code" placeholder="10001" />
                </div>
            </div>
            <div class="mb-4">
                <x-form-select label="Country" name="country_id" id="address_country_id" required>
                    <option value="">Select Country</option>
                    @foreach ($countries as $country)
                        <option value="{{ $country['id'] }}">{{ $country['name'] }}</option>
                    @endforeach
                </x-form-select>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <x-form-input label="Latitude" name="latitude" id="address_latitude" placeholder="40.7128" type="number" step="any" />
                </div>
                <div class="mb-4">
                    <x-form-input label="Longitude" name="longitude" id="address_longitude" placeholder="-74.0060" type="number" step="any" />
                </div>
            </div>
            <div class="mb-4">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="is_default" id="address_is_default" value="1" class="rounded border-gray-300">
                    <span class="text-sm text-gray-700">Set as default address</span>
                </label>
            </div>
            <div class="mb-4">
                <x-form-select label="Store (optional)" name="store_id" id="address_store_id">
                    <option value="">No Store</option>
                    @foreach ($stores as $store)
                        <option value="{{ $store['id'] }}">{{ $store['name'] }}</option>
                    @endforeach
                </x-form-select>
            </div>
        </x-entity-crud>
    </div>

    @push('scripts')
    <script>
        window.fillAddressForm = function(data) {
            $('#address_label').val(data.label);
            $('#address_contact_name').val(data.contact_name);
            $('#address_contact_phone').val(data.contact_phone);
            $('#address_line1').val(data.address_line1);
            $('#address_line2').val(data.address_line2);
            $('#address_city').val(data.city);
            $('#address_state').val(data.state);
            $('#address_postal_code').val(data.postal_code);
            $('#address_country_id').val(data.country_id);
            $('#address_latitude').val(data.latitude);
            $('#address_longitude').val(data.longitude);
            if (data.is_default) $('#address_is_default').prop('checked', true);
            if (data.store_id) $('#address_store_id').val(data.store_id);
        };
    </script>
    @endpush
</x-app-layout>