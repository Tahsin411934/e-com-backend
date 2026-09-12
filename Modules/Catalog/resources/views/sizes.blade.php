<x-app-layout>
    <x-confirm-delete />

    <div class="p-4">
        <x-entity-crud
            id="size"
            createPermission="sizes"
            title="Sizes"
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
            drawerTitle="Size"
            dataKey="data"
            idField="size_id"
            :order="[[3, 'desc']]"
        >
                        {{-- Hidden: group_name is the denormalized display copy (auto-synced from select2 text). --}}
            <input type="hidden" name="group_name" id="size_group_name" />

            <div class="mb-4">
                <x-select2-dropdown
                    label="Group Name"
                    name="size_group_id"
                    id="size_group_id_picker"
                    :route="route('ajax.dropdown-search', 'size-groups')"
                    placeholder="Select a size group..."
                    allow-clear
                    required
                />
                <p class="text-xs text-gray-400 mt-1">Pick a size group — its size set loads below. Manage groups on the Size Groups page.</p>
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
        /**
         * The Group Name select2 picks a Size Group from the size_groups
         * table. When a group is chosen:
         *   - hidden size_group_id + denormalized group_name are set
         *   - the group's size set is fetched: if it exists the drawer
         *     switches to UPDATE mode (size id hidden field filled), else
         *     the form stays in CREATE mode with a fresh size list.
         */
        function syncSizeGroupSelection() {
            var picker = $('#size_group_id_picker');
            var groupId = picker.val() || '';
            var groupName = picker.find('option:selected').first().text() || '';

            $('#size_group_name').val(groupName);
            $('#size_sizes').val('');
            $('#size_status').val('active');

            if (groupId) {
                $.get('{{ route('sizes.by-group', ':id') }}'.replace(':id', groupId), function (res) {
                    if (res.status === 'success' && res.data && res.data.id) {
                        $('#size_hid').val(res.data.id);
                        $('#size_sizes').val(res.data.sizes || '');
                        $('#size_status').val(res.data.status || 'active');
                        $('#size_group_name').val(res.data.group_name || groupName);
                    }
                });
            } else {
                $('#size_hid').val('');
            }
        }

        $(document).on('change', '#size_group_id_picker', function () {
            syncSizeGroupSelection();
        });

Crud.register('size', 'fill', function (data) {
            var picker = $('#size_group_id_picker');

            // Inject the size's group so Select2 displays it
            picker.find('option').remove();
            picker.append($('<option>', { value: '', text: '' }));
            var gid = data.size_group_id || '';
            var gname = data.group_name || '';
            if (gid && gname) {
                picker.append($('<option>', { value: String(gid), text: gname }));
                picker.val(String(gid));
            } else {
                picker.val('');
            }

            $('#size_group_name').val(gname);
            $('#size_sizes').val(data.sizes);
            $('#size_status').val(data.status);

            if (window.Select2Ajax) {
                window.Select2Ajax.sync(picker.closest('form'));
            }
        });
    </script>
    @endpush
</x-app-layout>
