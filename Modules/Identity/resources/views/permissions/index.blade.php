<x-app-layout>
    <x-entity-crud
        id="permission"
        createPermission="permissions"
        createPermission="permissions"
        createPermission="permissions"
        createPermission="permissions"
        createPermission="permissions"
        createPermission="permissions"
        createPermission="permissions"
        createPermission="permissions"
        createPermission="permissions"
        createPermission="permissions"
        createPermission="permissions"
        createPermission="permissions"
        createPermission="permissions"
        createPermission="permissions"
        createPermission="permissions"
        createPermission="permissions"
        createPermission="permissions"
        createPermission="permissions"
        createPermission="permissions"
        createPermission="permissions"
        createPermission="permissions"
        createPermission="permissions"
        createPermission="permissions"
        createPermission="permissions"
        createPermission="permissions"
        createPermission="permissions"
        createPermission="permissions"
        createPermission="permissions"
        createPermission="permissions"
        createPermission="permissions"
        createPermission="permissions"
        createPermission="permissions"
        createPermission="permissions"
        createPermission="permissions"
        createPermission="permissions"
        createPermission="permissions"
        title="Permissions"
        icon="fa-solid fa-key"
        :columns="['Name','Description','Created At','Action']"
        :dtColumns="[
            ['data' => 'name'],
            ['data' => 'description'],
            ['data' => 'created_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        ajaxUrl="{{ route('permissions.dataTable') }}"
        storeUrl="{{ route('permissions.store') }}"
        updateUrl="{{ route('permissions.update', ':id') }}"
        showUrl="{{ route('permissions.show', ':id') }}"
        destroyUrl="{{ route('permissions.destroy', ':id') }}"
        drawerTitle="Permission"
        dataKey="data"
        idField="permission_id"
        :order="[[2, 'desc']]"
    >
        <div class="mb-4">
            <x-form-input label="Name" name="name" id="permission_name" placeholder="Permission Name" required />
        </div>
        <div class="mb-4">
            <x-form-input label="Description" name="description" id="permission_description" placeholder="Permission Description" />
        </div>
    </x-entity-crud>

    @push('scripts')
    <script>
        window.fillPermissionForm = function(data) {
            $('#permission_name').val(data.name);
            $('#permission_description').val(data.description);
        };
    </script>
    @endpush
</x-app-layout>