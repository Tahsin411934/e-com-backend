<x-app-layout>
    <div class="p-4">
        <div class="flex flex-col md:flex-row md:items-end gap-4 mb-5 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <div class="w-full md:w-auto flex items-end">
                <button id="resetAppSettingFilters"
                    class="px-4 py-2 text-sm font-medium text-white bg-gray-700 hover:bg-gray-800 rounded-lg transition active:scale-95">
                    Reset
                </button>
            </div>
        </div>

        <x-entity-crud
            id="app-setting"
            title="App Settings"
            icon="fa-solid fa-cogs"
            :columns="['Scope Type','Scope ID','Key','Value','Public','Created At','Action']"
            :dtColumns="[
                ['data' => 'scope_type'],
                ['data' => 'scope_id'],
                ['data' => 'setting_key'],
                ['data' => 'setting_value'],
                ['data' => 'is_public'],
                ['data' => 'created_at'],
                ['data' => 'action', 'orderable' => false, 'searchable' => false],
            ]"
            ajaxUrl="{{ route('app-settings.dataTable') }}"
            storeUrl="{{ route('app-settings.store') }}"
            updateUrl="{{ route('app-settings.update', ':id') }}"
            showUrl="{{ route('app-settings.show', ':id') }}"
            destroyUrl="{{ route('app-settings.destroy', ':id') }}"
            drawerTitle="Setting"
            dataKey="setting"
            idField="setting_id"
            :order="[[5, 'desc']]"
        >
            <div class="mb-4">
                <x-form-select label="Scope Type" name="scope_type" id="setting_scope_type" required>
                    <option value="global">Global</option>
                    <option value="store">Store</option>
                    <option value="user">User</option>
                </x-form-select>
            </div>
            <div class="mb-4">
                <x-form-input label="Scope ID" name="scope_id" id="setting_scope_id" placeholder="0" type="number" min="0" />
            </div>
            <div class="mb-4">
                <x-form-input label="Setting Key" name="setting_key" id="setting_key" placeholder="module.key" required />
            </div>
            <div class="mb-4">
                <label for="setting_value" class="block text-sm font-medium text-gray-700 mb-2">Setting Value (JSON)</label>
                <textarea name="setting_value" id="setting_value" rows="4"
                    class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder='{"enabled": true}' required></textarea>
            </div>
            <div class="mb-4">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="is_public" id="setting_is_public" value="1" class="rounded border-gray-300">
                    <span class="text-sm text-gray-700">Public</span>
                </label>
            </div>
        </x-entity-crud>
    </div>

    @push('scripts')
    <script>
        window.fillAppSettingForm = function(data) {
            $('#setting_scope_type').val(data.scope_type);
            $('#setting_scope_id').val(data.scope_id);
            $('#setting_key').val(data.setting_key);
            $('#setting_value').val(JSON.stringify(data.setting_value, null, 2));
            if (data.is_public) $('#setting_is_public').prop('checked', true);
        };
    </script>
    @endpush
</x-app-layout>