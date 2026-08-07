<x-app-layout>
    <x-entity-crud
        id="store-staff"
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
        dataKey="staff"
        idField="staff_id"
        :filters="[
            'Store' => '<option value=\"\">All Stores</option>' . implode('', array_map(fn($s) => '<option value=\"' . $s['id'] . '\">' . e($s['name']) . '</option>', $stores))
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
            <x-form-input label="User ID" name="user_id" id="staff_user_id" placeholder="User ID" required />
        </div>
        <div class="mb-4">
            <x-form-input label="Staff Code" name="staff_code" id="staff_code" placeholder="Optional staff code" />
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
        window.fillStoreStaffForm = function(data) {
            $('#staff_store_id').val(data.store_id);
            $('#staff_user_id').val(data.user_id);
            $('#staff_code').val(data.staff_code);
            $('#staff_status').val(data.status);
            if (data.hired_at) $('#staff_hired_at').val(data.hired_at);
        };
    </script>
    @endpush
</x-app-layout>