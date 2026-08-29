<x-app-layout>
    <x-entity-crud
        id="supplier"
        title="Suppliers"
        icon="fa-solid fa-truck"
        :columns="['Name','Email','Phone','Contact Person','City','Country','Status','Created At','Action']"
        :dtColumns="[
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
        drawerTitle="Supplier"
        dataKey="data"
        idField="supplier_id"
        :order="[[7, 'desc']]"
    >
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
        window.fillSupplierForm = function(data) {
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
        };
    </script>
    @endpush
</x-app-layout>