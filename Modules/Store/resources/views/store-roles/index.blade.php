<x-app-layout>
    <x-entity-crud
        id="store-roles"
        createPermission="store-roles"
        title="Store Roles"
        icon="fa-solid fa-user-shield"
        :columns="['Name','Permissions','Created At','Action']"
        :dtColumns="[
            ['data' => 'name'],
            ['data' => 'permissions_count'],
            ['data' => 'created_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        ajaxUrl="{{ route('store-roles.dataTable') }}"
        storeUrl="{{ route('store-roles.store') }}"
        updateUrl="{{ route('store-roles.update', ':id') }}"
        showUrl="{{ route('store-roles.show', ':id') }}"
        destroyUrl="{{ route('store-roles.destroy', ':id') }}"
        drawerTitle="Store Role"
        dataKey="data"
        idField="role_id"
    >
        <div class="mb-4">
            <x-form-input label="Role Name" name="name" id="store_role_name" required />
        </div>
        <div class="mb-4">
            <x-form-input label="Description" name="description" id="store_role_description" />
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Permissions</label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-80 overflow-y-auto">
                @foreach ($groupedPermissions as $group => $permissions)
                    <div class="col-span-full font-semibold text-xs uppercase text-gray-500 mt-2">{{ $group }}</div>
                    @foreach ($permissions as $permission)
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="store-role-permission">
                            <span>{{ $permission->name }}</span>
                        </label>
                    @endforeach
                @endforeach
            </div>
        </div>
    </x-entity-crud>

    @push('scripts')
    <script>
        Crud.register('storeroles', 'fill', function (data) {
            $('#store_role_name').val(data.name);
            $('#store_role_description').val(data.description || '');
            $('.store-role-permission').prop('checked', false);
            (data.permissions || []).forEach(function (permission) {
                $('.store-role-permission[value="' + permission.id + '"]').prop('checked', true);
            });
        });
    </script>
    @endpush
</x-app-layout>
