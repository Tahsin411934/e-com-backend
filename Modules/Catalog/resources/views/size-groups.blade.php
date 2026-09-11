<x-app-layout>
    <x-confirm-delete />

    <div class="p-4">
        <x-entity-crud
            id="sizeGroup"
            createPermission="size-groups"
            title="Size Groups"
            icon="fa-solid fa-layer-group"
            :columns="['Name','Status','Created At','Action']"
            :dtColumns="[
                ['data' => 'name'],
                ['data' => 'status'],
                ['data' => 'created_at'],
                ['data' => 'action', 'orderable' => false, 'searchable' => false],
            ]"
            ajaxUrl="{{ route('size-groups.dataTable') }}"
            storeUrl="{{ route('size-groups.store') }}"
            updateUrl="{{ route('size-groups.update', ':id') }}"
            showUrl="{{ route('size-groups.show', ':id') }}"
            destroyUrl="{{ route('size-groups.destroy', ':id') }}"
            drawerTitle="Size Group"
            dataKey="data"
            idField="size_group_id"
            :order="[[2, 'desc']]"
        >
            <div class="mb-4">
                <x-form-input label="Group Name" name="name" id="size_group_name_input" placeholder="e.g. Clothing Size" required />
            </div>
            <div class="mb-4">
                <x-form-select label="Status" name="status" id="size_group_status">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </x-form-select>
            </div>
        </x-entity-crud>
    </div>

    @push('scripts')
<script>
    window.fillSizeGroupForm = function(data) {
        $('#size_group_name_input').val(data.name);
        $('#size_group_status').val(data.status);
    };
</script>
    @endpush
</x-app-layout>
