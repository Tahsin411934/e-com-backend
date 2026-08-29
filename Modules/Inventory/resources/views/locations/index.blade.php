<x-app-layout>
    <x-entity-crud
        id="inventory-location"
        title="Inventory Locations"
        icon="fa-solid fa-map-marker-alt"
        :columns="['Name','Store','Type','Status','Created At','Action']"
        :dtColumns="[
            ['data' => 'name'],
            ['data' => 'store_name'],
            ['data' => 'location_type'],
            ['data' => 'status'],
            ['data' => 'created_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        ajaxUrl="{{ route('inventory-locations.dataTable') }}"
        storeUrl="{{ route('inventory-locations.store') }}"
        updateUrl="{{ route('inventory-locations.update', ':id') }}"
        showUrl="{{ route('inventory-locations.show', ':id') }}"
        destroyUrl="{{ route('inventory-locations.destroy', ':id') }}"
        drawerTitle="Inventory Location"
        dataKey="data"
        idField="location_id"
        :order="[[4, 'desc']]"
    >
        <div class="mb-4">
            <x-form-select label="Store" name="store_id" id="location_store_id">
                @foreach($stores ?? [] as $store)
                    <option value="{{ $store['id'] }}">{{ $store['name'] }}</option>
                @endforeach
                <option value="" disabled selected>Select a store</option>
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-input label="Name" name="name" id="location_name" placeholder="Location Name" required />
        </div>
        <div class="mb-4">
            <x-form-select label="Location Type" name="location_type" id="location_type">
                <option value="warehouse">Warehouse</option>
                <option value="retail">Retail</option>
                <option value="delivery_hub">Delivery Hub</option>
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-select label="Status" name="status" id="location_status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </x-form-select>
        </div>
    </x-entity-crud>

    @push('scripts')
    <script>
        window.fillInventoryLocationForm = function(data) {
            $('#location_store_id').val(data.store_id);
            $('#location_name').val(data.name);
            $('#location_type').val(data.location_type);
            $('#location_status').val(data.status);
        };
    </script>
    @endpush
</x-app-layout>