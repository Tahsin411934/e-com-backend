<x-app-layout>
    <x-entity-crud
        id="store"
        createPermission="stores"
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
        dataKey="data"
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

        <!-- Owner Login Credentials (created together with the store) -->
        <div id="store_owner_section" class="border-t border-gray-200 dark:border-gray-700 my-4 pt-4">
            <p class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-3">
                <i class="fas fa-user-shield mr-1 text-primary"></i> Owner Login Credentials
                <span class="block text-xs font-normal text-gray-400 mt-0.5">A user account with the Store Owner role is created automatically.</span>
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <x-form-input label="Owner First Name" name="owner_first_name" id="store_owner_first_name" placeholder="First Name" required />
                <x-form-input label="Owner Last Name" name="owner_last_name" id="store_owner_last_name" placeholder="Last Name" required />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <x-form-input label="Password" name="password" id="store_owner_password" placeholder="Min 8 characters" type="password" required />
                <x-form-input label="Confirm Password" name="password_confirmation" id="store_owner_password_confirmation" placeholder="Confirm password" type="password" required />
            </div>
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
Crud.register('store', 'fill', function (data) {
            $('#store_name').val(data.name);
            $('#store_slug').val(data.slug);
            $('#store_email').val(data.email);
            $('#store_phone').val(data.phone);
            $('#store_status').val(data.status);
            $('#store_currency_code').val(data.currency_code);
            $('#store_timezone').val(data.timezone);
        });

        // Owner credential fields are only used when ADDING a new store.
        // They are hidden (and not required) while editing an existing store.
        (function() {
            var originalOpenStoreDrawer = Crud.get('store', 'open');
            Crud.register('store', 'open', function(mode) {
                originalOpenStoreDrawer(mode);
                var isEdit = mode === 'edit';
                $('#store_owner_section').toggleClass('hidden', isEdit);
                $('#store_owner_first_name, #store_owner_last_name, #store_owner_password, #store_owner_password_confirmation')
                    .prop('required', !isEdit);
            });
        })();

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
