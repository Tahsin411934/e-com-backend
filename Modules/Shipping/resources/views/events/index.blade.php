<x-app-layout>
    <x-entity-crud
        id="shipment-event"
        title="Shipment Events"
        icon="fa-solid fa-timeline"
        :columns="['Tracking','Type','Status','Title','Driver','Created By','Occurred At','Action']"
        :dtColumns="[
            ['data' => 'tracking_number'],
            ['data' => 'event_type'],
            ['data' => 'status'],
            ['data' => 'title'],
            ['data' => 'driver_name'],
            ['data' => 'created_by_name'],
            ['data' => 'occurred_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        ajaxUrl="{{ route('shipment-events.dataTable') }}"
        storeUrl="{{ route('shipment-events.store') }}"
        updateUrl="{{ route('shipment-events.update', ':id') }}"
        showUrl="{{ route('shipment-events.show', ':id') }}"
        destroyUrl="{{ route('shipment-events.destroy', ':id') }}"
        drawerTitle="Shipment Event"
        dataKey="data"
        idField="event_id"
        :order="[[6, 'desc']]"
    >
        <div class="mb-4">
            <x-form-select label="Shipment" name="shipment_id" id="event_shipment_id" required>
                @foreach($shipments ?? [] as $shipment)
                    <option value="{{ $shipment->id }}">{{ $shipment->tracking_number }}</option>
                @endforeach
            </x-form-select>
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <x-form-select label="Driver" name="driver_id" id="event_driver_id">
                @foreach($drivers ?? [] as $driver)
                    <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                @endforeach
            </x-form-select>
            <x-form-select label="Created By" name="created_by" id="event_created_by">
                @foreach($users ?? [] as $user)
                    <option value="{{ $user->id }}">{{ $user->email }}</option>
                @endforeach
            </x-form-select>
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <x-form-select label="Event Type" name="event_type" id="event_event_type" required>
                <option value="status_update">Status Update</option>
                <option value="assignment">Assignment</option>
                <option value="pickup">Pickup</option>
                <option value="location">Location</option>
                <option value="delivery_attempt">Delivery Attempt</option>
                <option value="exception">Exception</option>
                <option value="note">Note</option>
            </x-form-select>
            <x-form-select label="Status" name="status" id="event_status">
                <option value="pending">Pending</option>
                <option value="packed">Packed</option>
                <option value="ready_for_pickup">Ready For Pickup</option>
                <option value="out_for_delivery">Out For Delivery</option>
                <option value="delivered">Delivered</option>
                <option value="failed">Failed</option>
                <option value="returned">Returned</option>
                <option value="cancelled">Cancelled</option>
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-input label="Title" name="title" id="event_title" placeholder="Package picked up" required />
        </div>
        <div class="mb-4">
            <x-form-textarea label="Description" name="description" id="event_description" placeholder="Operational note" rows="3" />
        </div>
        <div class="grid grid-cols-3 gap-4 mb-4">
            <x-form-input label="Latitude" name="latitude" id="event_latitude" type="number" step="0.0000001" placeholder="23.7806" />
            <x-form-input label="Longitude" name="longitude" id="event_longitude" type="number" step="0.0000001" placeholder="90.4074" />
            <x-form-input label="Occurred At" name="occurred_at" id="event_occurred_at" type="datetime-local" required />
        </div>
    </x-entity-crud>

    @push('scripts')
    <script>
        window.fillShipmenteventForm = function(data) {
            $('#event_shipment_id').val(data.shipment_id);
            $('#event_driver_id').val(data.driver_id || '');
            $('#event_created_by').val(data.created_by || '');
            $('#event_event_type').val(data.event_type);
            $('#event_status').val(data.status || '');
            $('#event_title').val(data.title);
            $('#event_description').val(data.description || '');
            $('#event_latitude').val(data.latitude || '');
            $('#event_longitude').val(data.longitude || '');
            $('#event_occurred_at').val(data.occurred_at ? data.occurred_at.substring(0, 16) : '');
        };
    </script>
    @endpush
</x-app-layout>
