<x-app-layout>
    <x-entity-crud
        id="user"
        title="Users"
        icon="fa-solid fa-users"
        :columns="['Full Name','Email','Phone','Role','Status','Created At','Action']"
        :dtColumns="[
            ['data' => 'full_name'],
            ['data' => 'email'],
            ['data' => 'phone'],
            ['data' => 'role_name'],
            ['data' => 'status'],
            ['data' => 'created_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        ajaxUrl="{{ route('users.dataTable') }}"
        storeUrl="{{ route('users.store') }}"
        updateUrl="{{ route('users.update', ':id') }}"
        showUrl="{{ route('users.show', ':id') }}"
        destroyUrl="{{ route('users.destroy', ':id') }}"
        drawerTitle="User"
        dataKey="data"
        idField="user_id"
        :order="[[5, 'desc']]"
    >
        <div class="mb-4">
            <x-form-input label="First Name" name="first_name" id="user_first_name" placeholder="First Name" />
        </div>
        <div class="mb-4">
            <x-form-input label="Last Name" name="last_name" id="user_last_name" placeholder="Last Name" />
        </div>
        <div class="mb-4">
            <x-form-input label="Email" name="email" id="user_email" placeholder="user@example.com" type="email" required />
        </div>
        <div class="mb-4">
            <x-form-input label="Phone" name="phone" id="user_phone" placeholder="+1234567890" />
        </div>
        <div class="mb-4">
            <x-form-input label="Password" name="password_hash" id="user_password_hash" type="password" placeholder="Enter password" required />
        </div>
        <div class="mb-4">
            <x-form-select label="Role" name="role_id" id="user_role_id" required>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                @endforeach
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-select label="Status" name="status" id="user_status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="blocked">Blocked</option>
                <option value="deleted">Deleted</option>
            </x-form-select>
        </div>
    </x-entity-crud>

    @push('scripts')
    <script>
        window.fillUserForm = function(data) {
            $('#user_first_name').val(data.first_name);
            $('#user_last_name').val(data.last_name);
            $('#user_email').val(data.email);
            $('#user_phone').val(data.phone);
            $('#user_password_hash').val('');
            $('#user_status').val(data.status);
            // Set role
            if (data.roles && data.roles.length > 0) {
                $('#user_role_id').val(data.roles[0].id);
            }
        };
    </script>
    @endpush
</x-app-layout>