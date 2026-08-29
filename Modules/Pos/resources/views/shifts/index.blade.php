<x-app-layout>
    <x-entity-crud
        id="pos-shift"
        title="POS Shifts"
        icon="fa-solid fa-clock"
        :columns="['Register','Store','User','Opened At','Closed At','Opening Bal.','Total Sales','Status','Action']"
        :dtColumns="[
            ['data' => 'register_name'],
            ['data' => 'store_name'],
            ['data' => 'user_name'],
            ['data' => 'opened_at'],
            ['data' => 'closed_at'],
            ['data' => 'opening_balance'],
            ['data' => 'total_sales'],
            ['data' => 'status'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        ajaxUrl="{{ route('pos-shifts.dataTable') }}"
        storeUrl="{{ route('pos-shifts.store') }}"
        updateUrl="{{ route('pos-shifts.update', ':id') }}"
        showUrl="{{ route('pos-shifts.show', ':id') }}"
        destroyUrl="{{ route('pos-shifts.destroy', ':id') }}"
        drawerTitle="POS Shift"
        dataKey="data"
        idField="shift_id"
        :order="[[3, 'desc']]"
    >
        <div class="mb-4">
            <x-form-select label="Register" name="register_id" id="shift_register_id">
                @foreach($registers ?? [] as $register)
                    <option value="{{ $register['id'] }}">{{ $register['name'] }} ({{ $register['store']['name'] ?? '' }})</option>
                @endforeach
                <option value="" disabled selected>Select a register</option>
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-select label="User" name="user_id" id="shift_user_id">
                @foreach($users ?? [] as $user)
                    <option value="{{ $user['id'] }}">{{ $user['name'] }}</option>
                @endforeach
                <option value="" disabled selected>Select user</option>
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-input label="Opening Balance" name="opening_balance" id="shift_opening_balance" type="number" step="0.01" placeholder="0.00" />
        </div>
        <div class="mb-4">
            <x-form-input label="Notes" name="notes" id="shift_notes" placeholder="Optional notes" />
        </div>
    </x-entity-crud>

    @push('scripts')
    <script>
        window.fillPosShiftForm = function(data) {
            $('#shift_register_id').val(data.register_id);
            $('#shift_user_id').val(data.user_id);
            $('#shift_opening_balance').val(data.opening_balance);
            $('#shift_notes').val(data.notes);
        };
    </script>
    @endpush
</x-app-layout>