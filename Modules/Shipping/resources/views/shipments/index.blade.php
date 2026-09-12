<x-app-layout>
    <x-entity-crud
        id="shipment"
        createPermission="shipments"
        title="Shipments"
        icon="fa-solid fa-truck-fast"
        :columns="['Tracking','Order','Store','Zone','Driver','Service','Status','Cost','Scheduled','Action']"
        :dtColumns="[
            ['data' => 'tracking_number'],
            ['data' => 'order_number'],
            ['data' => 'store_name'],
            ['data' => 'zone_name'],
            ['data' => 'driver_name'],
            ['data' => 'service_level'],
            ['data' => 'status'],
            ['data' => 'shipping_cost'],
            ['data' => 'scheduled_delivery_date'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        ajaxUrl="{{ route('shipments.dataTable') }}"
        storeUrl="{{ route('shipments.store') }}"
        updateUrl="{{ route('shipments.update', ':id') }}"
        showUrl="{{ route('shipments.show', ':id') }}"
        destroyUrl="{{ route('shipments.destroy', ':id') }}"
        drawerTitle="Shipment"
        dataKey="data"
        idField="shipment_id"
        :order="[[0, 'desc']]"
    >
        <div class="grid grid-cols-2 gap-4 mb-4">
            <x-form-input label="Tracking Number" name="tracking_number" id="shipment_tracking_number" placeholder="Auto generated when blank" />
            <x-form-input label="Carrier Name" name="carrier_name" id="shipment_carrier_name" placeholder="In-house delivery" />
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <x-form-select label="Order" name="order_id" id="shipment_order_id">
                @foreach($orders ?? [] as $order)
                    <option value="{{ $order->id }}">{{ $order->order_number }}</option>
                @endforeach
            </x-form-select>
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <x-form-select label="Store" name="store_id" id="shipment_store_id">
                @foreach($stores ?? [] as $store)
                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                @endforeach
            </x-form-select>
            <x-form-select label="Shipping Address" name="shipping_address_id" id="shipment_shipping_address_id">
                @foreach($addresses ?? [] as $address)
                    <option value="{{ $address->id }}">{{ $address->contact_name ?: $address->label }} - {{ $address->city }}</option>
                @endforeach
            </x-form-select>
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <x-form-select label="Delivery Zone" name="delivery_zone_id" id="shipment_delivery_zone_id">
                @foreach($zones ?? [] as $zone)
                    <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                @endforeach
            </x-form-select>
            <x-form-select label="Driver" name="driver_id" id="shipment_driver_id">
                @foreach($drivers ?? [] as $driver)
                    <option value="{{ $driver->id }}">{{ $driver->name }} ({{ ucfirst($driver->status) }})</option>
                @endforeach
            </x-form-select>
        </div>
        <div class="grid grid-cols-3 gap-4 mb-4">
            <x-form-select label="Service Level" name="service_level" id="shipment_service_level" required>
                <option value="standard">Standard</option>
                <option value="express">Express</option>
                <option value="same_day">Same Day</option>
                <option value="pickup">Pickup</option>
            </x-form-select>
            <x-form-select label="Delivery Type" name="delivery_type" id="shipment_delivery_type" required>
                <option value="home_delivery">Home Delivery</option>
                <option value="store_pickup">Store Pickup</option>
                <option value="third_party">Third Party</option>
            </x-form-select>
            <x-form-select label="Status" name="status" id="shipment_status" required>
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
        <div class="grid grid-cols-3 gap-4 mb-4">
            <x-form-input label="Shipping Cost" name="shipping_cost" id="shipment_shipping_cost" type="number" step="0.01" placeholder="80.00" required />
            <x-form-input label="Weight KG" name="package_weight_kg" id="shipment_package_weight_kg" type="number" step="0.01" placeholder="1.50" />
            <x-form-input label="Package Count" name="package_count" id="shipment_package_count" type="number" placeholder="1" required />
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <x-form-input label="Recipient Name" name="recipient_name" id="shipment_recipient_name" placeholder="Customer name" />
            <x-form-input label="Recipient Phone" name="recipient_phone" id="shipment_recipient_phone" placeholder="+8801..." />
        </div>
        <div class="mb-4">
            <x-form-textarea label="Delivery Instructions" name="delivery_instructions" id="shipment_delivery_instructions" placeholder="Gate code, handoff notes, etc." rows="2" />
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <x-form-input label="Scheduled Date" name="scheduled_delivery_date" id="shipment_scheduled_delivery_date" type="date" />
            <x-form-input label="ETA" name="eta_at" id="shipment_eta_at" type="datetime-local" />
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <x-form-input label="Shipped At" name="shipped_at" id="shipment_shipped_at" type="datetime-local" />
            <x-form-input label="Delivered At" name="delivered_at" id="shipment_delivered_at" type="datetime-local" />
        </div>
    </x-entity-crud>

    @push('scripts')
    <script>
Crud.register('shipment', 'fill', function (data) {
            $('#shipment_order_id').val(data.order_id || '');
            $('#shipment_store_id').val(data.store_id || '');
            $('#shipment_delivery_zone_id').val(data.delivery_zone_id || '');
            $('#shipment_driver_id').val(data.driver_id || '');
            $('#shipment_shipping_address_id').val(data.shipping_address_id || '');
            $('#shipment_tracking_number').val(data.tracking_number);
            $('#shipment_carrier_name').val(data.carrier_name || '');
            $('#shipment_service_level').val(data.service_level);
            $('#shipment_delivery_type').val(data.delivery_type);
            $('#shipment_status').val(data.status);
            $('#shipment_shipping_cost').val(data.shipping_cost);
            $('#shipment_package_weight_kg').val(data.package_weight_kg || '');
            $('#shipment_package_count').val(data.package_count);
            $('#shipment_recipient_name').val(data.recipient_name || '');
            $('#shipment_recipient_phone').val(data.recipient_phone || '');
            $('#shipment_delivery_instructions').val(data.delivery_instructions || '');
            $('#shipment_scheduled_delivery_date').val(data.scheduled_delivery_date ? data.scheduled_delivery_date.substring(0, 10) : '');
            $('#shipment_eta_at').val(data.eta_at ? data.eta_at.substring(0, 16) : '');
            $('#shipment_shipped_at').val(data.shipped_at ? data.shipped_at.substring(0, 16) : '');
            $('#shipment_delivered_at').val(data.delivered_at ? data.delivered_at.substring(0, 16) : '');
        });
    </script>
    @endpush
</x-app-layout>
