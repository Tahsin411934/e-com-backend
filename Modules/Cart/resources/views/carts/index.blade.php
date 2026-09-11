<x-app-layout>
    <x-entity-crud
        id="cart"
        createPermission="carts"
        title="Carts"
        icon="fa-solid fa-shopping-cart"
        :columns="['ID','User','Session','Store','Items','Total','Status','Created','Action']"
        :dtColumns="[
            ['data' => 'id'],
            ['data' => 'user_email'],
            ['data' => 'session_id'],
            ['data' => 'store_name'],
            ['data' => 'items_count'],
            ['data' => 'total'],
            ['data' => 'status'],
            ['data' => 'created_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        ajaxUrl="{{ route('cart.dataTable') }}"
        storeUrl="{{ route('cart.store') }}"
        updateUrl="{{ route('cart.update', ':id') }}"
        showUrl="{{ route('cart.show', ':id') }}"
        destroyUrl="{{ route('cart.destroy', ':id') }}"
        drawerTitle="Cart"
        dataKey="data"
        idField="cart_id"
        :order="[[0, 'desc']]"
    >
        <div class="mb-4">
            <x-form-input label="Session ID" name="session_id" id="cart_session_id" placeholder="Session ID" />
        </div>
        <div class="mb-4">
            <x-form-select label="Status" name="status" id="cart_status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="abandoned">Abandoned</option>
                <option value="completed">Completed</option>
            </x-form-select>
        </div>
    </x-entity-crud>

    @push('scripts')
    <script>
        window.fillCartForm = function(data) {
            $('#cart_session_id').val(data.session_id || '');
            $('#cart_status').val(data.status);
        };
    </script>
    @endpush
</x-app-layout>