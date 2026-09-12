<x-app-layout>
@php
    $supplierStoreFilter = count($stores ?? []) ? [
        'store_id' => [
            'label' => 'Store',
            'options' => '<option value="">All Stores</option>'
                . collect($stores)->map(fn ($s) => '<option value="'.$s->id.'">'.e($s->name).'</option>')->implode(''),
        ],
    ] : [];
@endphp
    <x-entity-crud
        id="supplier"
        createPermission="suppliers"
        title="Suppliers"
        icon="fa-solid fa-truck"
        :columns="['Store','Name','Email','Phone','Contact Person','City','Country','Status','Created At','Action']"
        :dtColumns="[
            ['data' => 'store_name', 'name' => 'store_name'],
            ['data' => 'name'],
            ['data' => 'email'],
            ['data' => 'phone'],
            ['data' => 'contact_person'],
            ['data' => 'city'],
            ['data' => 'country'],
            ['data' => 'status'],
            ['data' => 'created_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        ajaxUrl="{{ route('suppliers.dataTable') }}"
        storeUrl="{{ route('suppliers.store') }}"
        updateUrl="{{ route('suppliers.update', ':id') }}"
        showUrl="{{ route('suppliers.show', ':id') }}"
        destroyUrl="{{ route('suppliers.destroy', ':id') }}"
        :filters="$supplierStoreFilter"
        drawerTitle="Supplier"
        dataKey="data"
        idField="supplier_id"
        :order="[[8, 'desc']]"
    >
        <div class="mb-4">
            @if($canAssignStore ?? false)
                <x-form-select label="Store" name="store_id" id="supplier_store_id">
                    <option value="" disabled selected>Select a store</option>
                    @foreach($stores ?? [] as $store)
                        <option value="{{ $store['id'] }}">{{ $store['name'] }}</option>
                    @endforeach
                </x-form-select>
            @else
                <x-form-select label="Store" name="store_id_display" id="supplier_store_id_display" :searchable="false">
                    <option value="">{{ $currentStore?->name ?? 'No Store' }}</option>
                </x-form-select>
                <input type="hidden" name="store_id" id="supplier_store_id" value="{{ $currentStore?->id ?? '' }}">
            @endif
        </div>
        <div class="mb-4">
            <x-form-input label="Name" name="name" id="supplier_name" placeholder="Supplier Name" required />
        </div>
        <div class="mb-4">
            <x-form-input label="Email" name="email" id="supplier_email" placeholder="supplier@example.com" type="email" />
        </div>
        <div class="mb-4">
            <x-form-input label="Phone" name="phone" id="supplier_phone" placeholder="Phone Number" />
        </div>
        <div class="mb-4">
            <x-form-input label="Contact Person" name="contact_person" id="supplier_contact_person" placeholder="Contact Person" />
        </div>
        <div class="mb-4">
            <x-form-input label="Address" name="address" id="supplier_address" placeholder="Address" />
        </div>
        <div class="mb-4">
            <x-form-input label="City" name="city" id="supplier_city" placeholder="City" />
        </div>
        <div class="mb-4">
            <x-form-input label="Country" name="country" id="supplier_country" placeholder="Country" />
        </div>
        <div class="mb-4">
            <x-form-input label="Tax Number" name="tax_number" id="supplier_tax_number" placeholder="Tax Number" />
        </div>
        <div class="mb-4">
            <x-form-input label="Payment Terms" name="payment_terms" id="supplier_payment_terms" placeholder="Payment Terms" />
        </div>
        <div class="mb-4">
            <x-form-input label="Notes" name="notes" id="supplier_notes" placeholder="Notes" />
        </div>
        <div class="mb-4">
            <x-form-select label="Status" name="status" id="supplier_status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </x-form-select>
        </div>
    </x-entity-crud>

    @push('scripts')
    <script>
Crud.register('supplier', 'fill', function (data) {
            $('#supplier_store_id').val(data.store_id);
            $('#supplier_name').val(data.name);
            $('#supplier_email').val(data.email);
            $('#supplier_phone').val(data.phone);
            $('#supplier_contact_person').val(data.contact_person);
            $('#supplier_address').val(data.address);
            $('#supplier_city').val(data.city);
            $('#supplier_country').val(data.country);
            $('#supplier_tax_number').val(data.tax_number);
            $('#supplier_payment_terms').val(data.payment_terms);
            $('#supplier_notes').val(data.notes);
            $('#supplier_status').val(data.status);
        });
    </script>
    @endpush
</x-app-layout>
