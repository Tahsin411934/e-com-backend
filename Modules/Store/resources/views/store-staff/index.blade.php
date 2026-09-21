<x-app-layout>
    @php
        $storeFilterOptions = '<option value="">All Stores</option>' . implode('', array_map(function ($s) {
            return '<option value="' . e($s['id']) . '">' . e($s['name']) . '</option>';
        }, $stores));
    @endphp

    <x-entity-crud
        id="store-staff"
        createPermission="store-staff"
        title="Store Staff"
        icon="fa-solid fa-users"
        :columns="['Store','User','Staff Code','Status','Hired At','Created At','Action']"
        :dtColumns="[
            ['data' => 'store_name'],
            ['data' => 'user_name'],
            ['data' => 'staff_code'],
            ['data' => 'status'],
            ['data' => 'hired_at'],
            ['data' => 'created_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        ajaxUrl="{{ route('store-staff.dataTable') }}"
        storeUrl="{{ route('store-staff.store') }}"
        updateUrl="{{ route('store-staff.update', ':id') }}"
        showUrl="{{ route('store-staff.show', ':id') }}"
        destroyUrl="{{ route('store-staff.destroy', ':id') }}"
        drawerTitle="Staff"
        dataKey="data"
        idField="staff_id"
        :filters="[
            'store_id' => [
                'label' => 'Store',
                'options' => $storeFilterOptions,
            ],
        ]"
        :order="[[5, 'desc']]"
    >
        <div class="mb-4">
            <x-form-select label="Store" name="store_id" id="staff_store_id" required>
                <option value="">Select Store</option>
                @foreach ($stores as $store)
                    <option value="{{ $store['id'] }}">{{ $store['name'] }}</option>
                @endforeach
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-input label="Existing User ID (optional)" name="user_id" id="staff_user_id" placeholder="Use existing user account" />
        </div>
        <div class="mb-4">
            <p class="text-xs text-gray-500 mb-2">Leave User ID empty to create a new login account.</p>
            <x-form-input label="First Name" name="first_name" id="staff_first_name" placeholder="New staff first name" />
        </div>
        <div class="mb-4">
            <x-form-input label="Last Name" name="last_name" id="staff_last_name" placeholder="New staff last name" />
        </div>
        <div class="mb-4">
            <x-form-input label="Email" name="email" id="staff_email" type="email" placeholder="New staff email" />
        </div>
        <div class="mb-4">
            <x-form-input label="Password" name="password" id="staff_password" type="password" placeholder="Minimum 8 characters" />
        </div>
        <div class="mb-4">
            <x-form-input label="Staff Code" name="staff_code" id="staff_code" placeholder="Optional staff code" />
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Store Roles</label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                @foreach ($roles as $role)
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" class="staff-role-checkbox">
                        <span>{{ $role->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>
        <div class="mb-4">
            <x-form-select label="Status" name="status" id="staff_status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="terminated">Terminated</option>
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-input label="Hired At" name="hired_at" id="staff_hired_at" type="date" />
        </div>
    </x-entity-crud>

    @push('scripts')
    <script>
Crud.register('storestaff', 'fill', function (data) {
            $('#staff_store_id').val(data.store_id);
            $('#staff_user_id').val(data.user_id);
            $('#staff_first_name, #staff_last_name, #staff_email, #staff_password').val('');
            $('#staff_code').val(data.staff_code);
            $('.staff-role-checkbox').prop('checked', false);
            (data.roles || []).forEach(function (role) {
                $('.staff-role-checkbox[value="' + role.id + '"]').prop('checked', true);
            });
            $('#staff_status').val(data.status);
            if (data.hired_at) $('#staff_hired_at').val(data.hired_at);
        });
    </script>
    @endpush
</x-app-layout>
