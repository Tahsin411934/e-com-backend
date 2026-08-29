<x-app-layout>
    <x-entity-crud
        id="pos-register"
        title="POS Registers"
        icon="fa-solid fa-cash-register"
        :columns="['Name','Code','Store','Type','Status','Created At','Action']"
        :dtColumns="[
            ['data' => 'name'],
            ['data' => 'code'],
            ['data' => 'store_name'],
            ['data' => 'type'],
            ['data' => 'status'],
            ['data' => 'created_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        ajaxUrl="{{ route('pos-registers.dataTable') }}"
        storeUrl="{{ route('pos-registers.store') }}"
        updateUrl="{{ route('pos-registers.update', ':id') }}"
        showUrl="{{ route('pos-registers.show', ':id') }}"
        destroyUrl="{{ route('pos-registers.destroy', ':id') }}"
        drawerTitle="POS Register"
        dataKey="data"
        idField="register_id"
        :order="[[5, 'desc']]"
    >
        <div class="mb-4">
            <x-form-select label="Store" name="store_id" id="register_store_id">
                @foreach($stores ?? [] as $store)
                    <option value="{{ $store['id'] }}">{{ $store['name'] }}</option>
                @endforeach
                <option value="" disabled selected>Select a store</option>
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-input label="Name" name="name" id="register_name" placeholder="Register Name" required />
        </div>
        <div class="mb-4">
            <x-form-input label="Code" name="code" id="register_code" placeholder="e.g. REG-001" required />
        </div>
        <div class="mb-4">
            <x-form-select label="Type" name="type" id="register_type">
                <option value="counter">Counter</option>
                <option value="mobile">Mobile</option>
                <option value="kiosk">Kiosk</option>
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-select label="Status" name="status" id="register_status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="offline">Offline</option>
            </x-form-select>
        </div>
    </x-entity-crud>

    @push('scripts')
    <script>
        window.fillPosRegisterForm = function(data) {
            $('#register_store_id').val(data.store_id);
            $('#register_name').val(data.name);
            $('#register_code').val(data.code);
            $('#register_type').val(data.type);
            $('#register_status').val(data.status);
        };
    </script>
    @endpush
</x-app-layout>