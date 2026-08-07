<x-app-layout>
    <x-entity-crud
        id="store"
        title="Stores"
        icon="fa-solid fa-store"
        :columns="['Name','Slug','Email','Phone','Status','Currency','Created At','Action']"
        :dtColumns="[
            ['data' => 'name'],
            ['data' => 'slug'],
            ['data' => 'email'],
            ['data' => 'phone'],
            ['data' => 'status'],
            ['data' => 'currency_code'],
            ['data' => 'created_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        ajaxUrl="{{ route('stores.dataTable') }}"
        storeUrl="{{ route('stores.store') }}"
        updateUrl="{{ route('stores.update', ':id') }}"
        showUrl="{{ route('stores.show', ':id') }}"
        destroyUrl="{{ route('stores.destroy', ':id') }}"
        drawerTitle="Store"
        dataKey="store"
        idField="store_id"
        :order="[[6, 'desc']]"
    >
        <div class="mb-4">
            <x-form-input label="Name" name="name" id="store_name" placeholder="Store Name" required />
        </div>
        <div class="mb-4">
            <x-form-input label="Slug" name="slug" id="store_slug" placeholder="Store Slug" required />
        </div>
        <div class="mb-4">
            <x-form-input label="Email" name="email" id="store_email" placeholder="store@example.com" type="email" />
        </div>
        <div class="mb-4">
            <x-form-input label="Phone" name="phone" id="store_phone" placeholder="+1234567890" />
        </div>
        <div class="mb-4">
            <x-form-select label="Status" name="status" id="store_status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="maintenance">Maintenance</option>
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-input label="Currency Code" name="currency_code" id="store_currency_code" placeholder="USD" maxlength="3" />
        </div>
        <div class="mb-4">
            <x-form-input label="Timezone" name="timezone" id="store_timezone" placeholder="UTC" />
        </div>
    </x-entity-crud>

    @push('scripts')
    <script>
        window.fillStoreForm = function(data) {
            $('#store_name').val(data.name);
            $('#store_slug').val(data.slug);
            $('#store_email').val(data.email);
            $('#store_phone').val(data.phone);
            $('#store_status').val(data.status);
            $('#store_currency_code').val(data.currency_code);
            $('#store_timezone').val(data.timezone);
        };

        $(document).ready(function() {
            $('#store_name').on('input', function() {
                if ($('#store_hid').val() === '') {
                    let slug = $(this).val().toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
                    $('#store_slug').val(slug);
                }
            });
        });
    </script>
    @endpush
</x-app-layout>