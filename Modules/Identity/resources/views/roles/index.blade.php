<x-app-layout>
    <x-entity-crud
        id="role"
        createPermission="roles"
        title="Roles"
        icon="fa-solid fa-shield-halved"
        :columns="['Name','Description','Permissions','Created At','Action']"
        :dtColumns="[
            ['data' => 'name'],
            ['data' => 'description'],
            ['data' => 'permissions_count'],
            ['data' => 'created_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        ajaxUrl="{{ route('roles.dataTable') }}"
        storeUrl="{{ route('roles.store') }}"
        updateUrl="{{ route('roles.update', ':id') }}"
        showUrl="{{ route('roles.show', ':id') }}"
        destroyUrl="{{ route('roles.destroy', ':id') }}"
        drawerTitle="Role"
        dataKey="data"
        idField="role_id"
        :order="[[3, 'desc']]"
    >
        <div class="mb-4">
            <x-form-input label="Name" name="name" id="role_name" placeholder="Role Name" required />
        </div>
        <div class="mb-4">
            <x-form-input label="Description" name="description" id="role_description" placeholder="Role Description" />
        </div>

        {{-- Permissions Section --}}
        <div class="mb-4">
            <label class="font-semibold text-sm text-slate-700 block mb-2">Permissions</label>
            <div class="border border-slate-200 rounded-lg p-3 max-h-64 overflow-y-auto bg-slate-50">
                @foreach($groupedPermissions as $moduleName => $permissions)
                    <div class="mb-3">
                        <div class="flex items-center gap-2 mb-1.5">
                            <input type="checkbox" 
                                   id="perm_group_{{ Str::slug($moduleName) }}" 
                                   class="role-perm-group rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                   data-group="{{ Str::slug($moduleName) }}">
                            <label for="perm_group_{{ Str::slug($moduleName) }}" class="text-sm font-bold text-slate-700 cursor-pointer">
                                {{ $moduleName }}
                            </label>
                        </div>
                        <div class="ml-5 grid grid-cols-2 gap-1">
                            @foreach($permissions as $perm)
                                <label class="flex items-center gap-1.5 text-xs text-slate-600 cursor-pointer hover:text-slate-800">
                                    <input type="checkbox" 
                                           name="permissions[]" 
                                           value="{{ $perm->id }}" 
                                           class="role-perm-{{ Str::slug($moduleName) }} rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    {{ $perm->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </x-entity-crud>

    @push('scripts')
    <script>
        // Group select/deselect functionality
        $(document).on('change', '.role-perm-group', function() {
            var group = $(this).data('group');
            var isChecked = $(this).is(':checked');
            $('.role-perm-' + group).prop('checked', isChecked);
        });

        // Update group checkbox when individual permissions change
        $(document).on('change', '[name="permissions[]"]', function() {
            var classList = $(this).attr('class').split(/\s+/);
            var groupClass = classList.find(c => c.startsWith('role-perm-') && c !== 'role-perm-group');
            if (groupClass) {
                var group = groupClass.replace('role-perm-', '');
                var total = $('.role-perm-' + group).length;
                var checked = $('.role-perm-' + group + ':checked').length;
                $('#perm_group_' + group).prop('checked', total === checked);
            }
        });

        window.fillRoleForm = function(data) {
            $('#role_name').val(data.name);
            $('#role_description').val(data.description);
            
            // Clear all permission checkboxes
            $('input[name="permissions[]"]').prop('checked', false);
            $('.role-perm-group').prop('checked', false);
            
            // Check the role's permissions
            if (data.permissions && data.permissions.length > 0) {
                data.permissions.forEach(function(perm) {
                    $('input[name="permissions[]"][value="' + perm.id + '"]').prop('checked', true);
                });
                
                // Update group checkboxes
                $('.role-perm-group').each(function() {
                    var group = $(this).data('group');
                    var total = $('.role-perm-' + group).length;
                    var checked = $('.role-perm-' + group + ':checked').length;
                    $(this).prop('checked', total === checked && total > 0);
                });
            }
        };
    </script>
    @endpush
</x-app-layout>