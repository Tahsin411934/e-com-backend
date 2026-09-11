<x-app-layout>
    <x-entity-crud
        id="delivery-zone"
        createPermission="delivery-zones"
        createPermission="delivery-zones"
        createPermission="delivery-zones"
        createPermission="delivery-zones"
        createPermission="delivery-zones"
        createPermission="delivery-zones"
        createPermission="delivery-zones"
        createPermission="delivery-zones"
        createPermission="delivery-zones"
        createPermission="delivery-zones"
        createPermission="delivery-zones"
        createPermission="delivery-zones"
        createPermission="delivery-zones"
        createPermission="delivery-zones"
        createPermission="delivery-zones"
        createPermission="delivery-zones"
        createPermission="delivery-zones"
        createPermission="delivery-zones"
        createPermission="delivery-zones"
        createPermission="delivery-zones"
        createPermission="delivery-zones"
        createPermission="delivery-zones"
        createPermission="delivery-zones"
        createPermission="delivery-zones"
        createPermission="delivery-zones"
        createPermission="delivery-zones"
        createPermission="delivery-zones"
        createPermission="delivery-zones"
        createPermission="delivery-zones"
        createPermission="delivery-zones"
        createPermission="delivery-zones"
        createPermission="delivery-zones"
        createPermission="delivery-zones"
        createPermission="delivery-zones"
        createPermission="delivery-zones"
        title="Delivery Zones"
        icon="fa-solid fa-map-location-dot"
        :columns="['Name','Code','Store','City','Country','Base Fee','Per KM','ETA','Status','Action']"
        :dtColumns="[
            ['data' => 'name'],
            ['data' => 'code'],
            ['data' => 'store_name'],
            ['data' => 'city'],
            ['data' => 'country_name'],
            ['data' => 'base_fee'],
            ['data' => 'per_km_fee'],
            ['data' => 'eta_window'],
            ['data' => 'status'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        ajaxUrl="{{ route('delivery-zones.dataTable') }}"
        storeUrl="{{ route('delivery-zones.store') }}"
        updateUrl="{{ route('delivery-zones.update', ':id') }}"
        showUrl="{{ route('delivery-zones.show', ':id') }}"
        destroyUrl="{{ route('delivery-zones.destroy', ':id') }}"
        drawerTitle="Delivery Zone"
        dataKey="data"
        idField="zone_id"
        :order="[[0, 'asc']]"
    >
        <div class="grid grid-cols-2 gap-4 mb-4">
            <x-form-input label="Name" name="name" id="zone_name" placeholder="Dhaka Metro" required />
            <x-form-input label="Code" name="code" id="zone_code" placeholder="DHK-METRO" required />
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <x-form-select label="Store" name="store_id" id="zone_store_id">
                @foreach($stores ?? [] as $store)
                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                @endforeach
            </x-form-select>
            <x-form-select label="Country" name="country_id" id="zone_country_id">
                @foreach($countries ?? [] as $country)
                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                @endforeach
            </x-form-select>
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <x-form-input label="City" name="city" id="zone_city" placeholder="Dhaka" />
            <x-form-input label="State" name="state" id="zone_state" placeholder="Dhaka Division" />
        </div>
        <div class="mb-4">
            <x-form-textarea label="Postal Codes" name="postal_codes" id="zone_postal_codes" placeholder="1205, 1212, 1216" rows="2" />
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <x-form-input label="Base Fee" name="base_fee" id="zone_base_fee" type="number" step="0.01" placeholder="80.00" required />
            <x-form-input label="Per KM Fee" name="per_km_fee" id="zone_per_km_fee" type="number" step="0.01" placeholder="12.00" required />
        </div>
        <div class="grid grid-cols-3 gap-4 mb-4">
            <x-form-input label="Free Shipping Min" name="free_shipping_min" id="zone_free_shipping_min" type="number" step="0.01" placeholder="1500.00" />
            <x-form-input label="Min Days" name="estimated_min_days" id="zone_estimated_min_days" type="number" placeholder="1" required />
            <x-form-input label="Max Days" name="estimated_max_days" id="zone_estimated_max_days" type="number" placeholder="3" required />
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <x-form-input label="Max Distance KM" name="max_delivery_distance_km" id="zone_max_delivery_distance_km" type="number" step="0.01" placeholder="20.00" />
            <x-form-select label="Status" name="status" id="zone_status" required>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </x-form-select>
        </div>
    </x-entity-crud>

    @push('scripts')
    <script>
        window.fillDeliveryzoneForm = function(data) {
            $('#zone_store_id').val(data.store_id || '');
            $('#zone_name').val(data.name);
            $('#zone_code').val(data.code);
            $('#zone_city').val(data.city || '');
            $('#zone_state').val(data.state || '');
            $('#zone_country_id').val(data.country_id || '');
            $('#zone_postal_codes').val(Array.isArray(data.postal_codes) ? data.postal_codes.join(', ') : (data.postal_codes || ''));
            $('#zone_base_fee').val(data.base_fee);
            $('#zone_per_km_fee').val(data.per_km_fee);
            $('#zone_free_shipping_min').val(data.free_shipping_min || '');
            $('#zone_max_delivery_distance_km').val(data.max_delivery_distance_km || '');
            $('#zone_estimated_min_days').val(data.estimated_min_days);
            $('#zone_estimated_max_days').val(data.estimated_max_days);
            $('#zone_status').val(data.status);
        };
    </script>
    @endpush
</x-app-layout>
