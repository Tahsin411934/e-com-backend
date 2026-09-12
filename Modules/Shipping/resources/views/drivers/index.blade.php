<x-app-layout>
    <x-entity-crud
        id="delivery-driver"
        createPermission="delivery-drivers"
        title="Delivery Drivers"
        icon="fa-solid fa-id-badge"
        :columns="['Code','Name','Phone','Store','Zone','Vehicle','Status','Last Seen','Action']"
        :dtColumns="[
            ['data' => 'employee_code'],
            ['data' => 'name'],
            ['data' => 'phone'],
            ['data' => 'store_name'],
            ['data' => 'zone_name'],
            ['data' => 'vehicle_type'],
            ['data' => 'status'],
            ['data' => 'last_seen_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        ajaxUrl="{{ route('delivery-drivers.dataTable') }}"
        storeUrl="{{ route('delivery-drivers.store') }}"
        updateUrl="{{ route('delivery-drivers.update', ':id') }}"
        showUrl="{{ route('delivery-drivers.show', ':id') }}"
        destroyUrl="{{ route('delivery-drivers.destroy', ':id') }}"
        drawerTitle="Delivery Driver"
        dataKey="data"
        idField="driver_id"
        :order="[[1, 'asc']]"
    >
        <div class="grid grid-cols-2 gap-4 mb-4">
            <x-form-input label="Employee Code" name="employee_code" id="driver_employee_code" placeholder="DRV-001" required />
            <x-form-select label="Linked User" name="user_id" id="driver_user_id">
                @foreach($users ?? [] as $user)
                    <option value="{{ $user->id }}">{{ $user->email }}</option>
                @endforeach
            </x-form-select>
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <x-form-input label="Name" name="name" id="driver_name" placeholder="Driver name" required />
            <x-form-input label="Phone" name="phone" id="driver_phone" placeholder="+8801..." required />
        </div>
        <div class="mb-4">
            <x-form-input label="Email" name="email" id="driver_email" type="email" placeholder="driver@example.com" />
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <x-form-select label="Store" name="store_id" id="driver_store_id">
                @foreach($stores ?? [] as $store)
                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                @endforeach
            </x-form-select>
            <x-form-select label="Delivery Zone" name="delivery_zone_id" id="driver_delivery_zone_id">
                @foreach($zones ?? [] as $zone)
                    <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                @endforeach
            </x-form-select>
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <x-form-select label="Vehicle Type" name="vehicle_type" id="driver_vehicle_type" required>
                <option value="walk">Walk</option>
                <option value="bike">Bike</option>
                <option value="motorbike">Motorbike</option>
                <option value="car">Car</option>
                <option value="van">Van</option>
                <option value="truck">Truck</option>
            </x-form-select>
            <x-form-select label="Status" name="status" id="driver_status" required>
                <option value="available">Available</option>
                <option value="busy">Busy</option>
                <option value="offline">Offline</option>
                <option value="inactive">Inactive</option>
            </x-form-select>
        </div>
        <div class="grid grid-cols-3 gap-4 mb-4">
            <x-form-input label="Vehicle Plate" name="vehicle_plate" id="driver_vehicle_plate" placeholder="DHA-1234" />
            <x-form-input label="License Number" name="license_number" id="driver_license_number" placeholder="License" />
            <x-form-input label="Capacity KG" name="capacity_kg" id="driver_capacity_kg" type="number" step="0.01" placeholder="25.00" />
        </div>
        <div class="grid grid-cols-3 gap-4 mb-4">
            <x-form-input label="Latitude" name="current_latitude" id="driver_current_latitude" type="number" step="0.0000001" placeholder="23.7806" />
            <x-form-input label="Longitude" name="current_longitude" id="driver_current_longitude" type="number" step="0.0000001" placeholder="90.4074" />
            <x-form-input label="Last Seen" name="last_seen_at" id="driver_last_seen_at" type="datetime-local" />
        </div>
    </x-entity-crud>

    @push('scripts')
    <script>
Crud.register('deliverydriver', 'fill', function (data) {
            $('#driver_user_id').val(data.user_id || '');
            $('#driver_store_id').val(data.store_id || '');
            $('#driver_delivery_zone_id').val(data.delivery_zone_id || '');
            $('#driver_employee_code').val(data.employee_code);
            $('#driver_name').val(data.name);
            $('#driver_phone').val(data.phone);
            $('#driver_email').val(data.email || '');
            $('#driver_license_number').val(data.license_number || '');
            $('#driver_vehicle_type').val(data.vehicle_type);
            $('#driver_vehicle_plate').val(data.vehicle_plate || '');
            $('#driver_capacity_kg').val(data.capacity_kg || '');
            $('#driver_status').val(data.status);
            $('#driver_current_latitude').val(data.current_latitude || '');
            $('#driver_current_longitude').val(data.current_longitude || '');
            $('#driver_last_seen_at').val(data.last_seen_at ? data.last_seen_at.substring(0, 16) : '');
        });
    </script>
    @endpush
</x-app-layout>
