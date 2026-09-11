<x-app-layout>
@php
    $movementStoreFilter = count($stores ?? []) ? [
        'store_id' => [
            'label' => 'Store',
            'options' => '<option value="">All Stores</option>'
                . collect($stores)->map(fn ($s) => '<option value="'.$s->id.'">'.e($s->name).'</option>')->implode(''),
        ],
    ] : [];
@endphp
    <x-entity-crud
        id="inventory-movement"
        createPermission="inventory-movements"
        title="Inventory Movements"
        icon="fa-solid fa-exchange-alt"
        :columns="['Location','Store','Type','Quantity','Reference','Note','Created By','Date','Action']"
        :dtColumns="[
            ['data' => 'location_name'],
            ['data' => 'store_name', 'name' => 'store_name'],
            ['data' => 'movement_type'],
            ['data' => 'quantity'],
            ['data' => 'reference_type'],
            ['data' => 'note'],
            ['data' => 'created_by_name'],
            ['data' => 'created_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        :filters="$movementStoreFilter"
        ajaxUrl="{{ route('inventory-movements.dataTable') }}"
        storeUrl="{{ route('inventory-movements.store') }}"
        updateUrl="{{ route('inventory-movements.update', ':id') }}"
        showUrl="{{ route('inventory-movements.show', ':id') }}"
        destroyUrl="{{ route('inventory-movements.destroy', ':id') }}"
        drawerTitle="Inventory Movement"
        dataKey="data"
        idField="movement_id"
        :order="[[6, 'desc']]"
    >
        <div class="mb-4">
            <x-form-select label="Location" name="location_id" id="movement_location_id">
                @foreach($locations ?? [] as $location)
                    <option value="{{ $location['id'] }}">{{ $location['name'] }}</option>
                @endforeach
                <option value="" disabled selected>Select a location</option>
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-select label="Movement Type" name="movement_type" id="movement_type">
                <option value="purchase">Purchase</option>
                <option value="sale">Sale</option>
                <option value="return">Return</option>
                <option value="adjustment">Adjustment</option>
                <option value="transfer_in">Transfer In</option>
                <option value="transfer_out">Transfer Out</option>
                <option value="reservation">Reservation</option>
                <option value="release">Release</option>
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-input label="Quantity" name="quantity" id="movement_quantity" type="number" placeholder="0" required />
        </div>
        <div class="mb-4">
            <x-form-input label="Reference Type" name="reference_type" id="movement_reference_type" placeholder="e.g. order, purchase" />
        </div>
        <div class="mb-4">
            <x-form-input label="Note" name="note" id="movement_note" placeholder="Optional note" />
        </div>
    </x-entity-crud>

    @push('scripts')
    <script>
        window.fillInventoryMovementForm = function(data) {
            $('#movement_location_id').val(data.location_id);
            $('#movement_type').val(data.movement_type);
            $('#movement_quantity').val(data.quantity);
            $('#movement_reference_type').val(data.reference_type);
            $('#movement_note').val(data.note);
        };
    </script>
    @endpush
</x-app-layout>