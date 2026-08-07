<x-app-layout>
    <x-confirm-delete />

    <div class="p-4">
        <x-entity-crud
            id="size"
            title="Size Groups"
            icon="fa-solid fa-ruler-combined"
            :columns="['Group Name','Sizes','Status','Created At','Action']"
            :dtColumns="[
                ['data' => 'group_name'],
                ['data' => 'sizes', 'orderable' => false, 'searchable' => false],
                ['data' => 'status'],
                ['data' => 'created_at'],
                ['data' => 'action', 'orderable' => false, 'searchable' => false],
            ]"
            ajaxUrl="{{ route('sizes.dataTable') }}"
            storeUrl="{{ route('sizes.store') }}"
            updateUrl="{{ route('sizes.update', ':id') }}"
            showUrl="{{ route('sizes.show', ':id') }}"
            destroyUrl="{{ route('sizes.destroy', ':id') }}"
            drawerTitle="Size Group"
            dataKey="size"
            idField="size_id"
            :order="[[3, 'desc']]"
        >
            <div class="mb-4">
                <x-form-input label="Group Name" name="group_name" id="size_group_name" placeholder="e.g. T-Shirt Size" required />
            </div>
            <div class="mb-4">
                <label for="size_sizes" class="block text-sm font-medium text-gray-700 mb-2">Sizes <span class="text-xs text-gray-400">(comma separated)</span></label>
                <input type="text" name="sizes" id="size_sizes" placeholder="e.g. S, M, L, XL, XXL"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-sm" required />
                <p class="text-xs text-gray-400 mt-1">Enter sizes separated by commas</p>
            </div>
            <div class="mb-4">
                <x-form-select label="Status" name="status" id="size_status">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </x-form-select>
            </div>
        </x-entity-crud>
    </div>

    @push('scripts')
    <script>
        window.fillSizeForm = function(data) {
            $('#size_group_name').val(data.group_name);
            $('#size_sizes').val(data.sizes);
            $('#size_status').val(data.status);
        };
    </script>
    @endpush
</x-app-layout>