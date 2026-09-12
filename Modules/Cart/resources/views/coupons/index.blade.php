<x-app-layout>
    <x-entity-crud
        id="coupon"
        createPermission="coupons"
        title="Coupons"
        icon="fa-solid fa-ticket"
        :columns="['Code','Type','Value','Min Order','Usage','Starts','Ends','Status','Action']"
        :dtColumns="[
            ['data' => 'code'],
            ['data' => 'discount_type'],
            ['data' => 'discount_value'],
            ['data' => 'minimum_order_amount'],
            ['data' => 'used_count'],
            ['data' => 'starts_at'],
            ['data' => 'ends_at'],
            ['data' => 'status'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        ajaxUrl="{{ route('coupons.dataTable') }}"
        storeUrl="{{ route('coupons.store') }}"
        updateUrl="{{ route('coupons.update', ':id') }}"
        showUrl="{{ route('coupons.show', ':id') }}"
        destroyUrl="{{ route('coupons.destroy', ':id') }}"
        drawerTitle="Coupon"
        dataKey="data"
        idField="coupon_id"
    >
        <div class="mb-4">
            <x-form-input label="Coupon Code" name="code" id="coupon_code" placeholder="e.g. SUMMER20" required />
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <x-form-select label="Discount Type" name="discount_type" id="coupon_discount_type">
                <option value="percentage">Percentage</option>
                <option value="fixed_amount">Fixed Amount</option>
                <option value="free_shipping">Free Shipping</option>
            </x-form-select>
            <x-form-input label="Discount Value" name="discount_value" id="coupon_discount_value" type="number" step="0.01" placeholder="0.00" required />
        </div>
        <div class="mb-4">
            <x-form-input label="Minimum Order Amount" name="minimum_order_amount" id="coupon_minimum_order_amount" type="number" step="0.01" value="0" placeholder="0.00" />
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <x-form-input label="Usage Limit" name="usage_limit" id="coupon_usage_limit" type="number" placeholder="Unlimited" />
            <x-form-input label="Per User Limit" name="usage_limit_per_user" id="coupon_usage_limit_per_user" type="number" placeholder="Unlimited" />
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <x-form-input label="Starts At" name="starts_at" id="coupon_starts_at" type="datetime-local" />
            <x-form-input label="Ends At" name="ends_at" id="coupon_ends_at" type="datetime-local" />
        </div>
        <div class="mb-4">
            <x-form-select label="Status" name="status" id="coupon_status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </x-form-select>
        </div>
    </x-entity-crud>

    @push('scripts')
    <script>
Crud.register('coupon', 'fill', function (data) {
            $('#coupon_code').val(data.code);
            $('#coupon_discount_type').val(data.discount_type);
            $('#coupon_discount_value').val(data.discount_value);
            $('#coupon_minimum_order_amount').val(data.minimum_order_amount || 0);
            $('#coupon_usage_limit').val(data.usage_limit || '');
            $('#coupon_usage_limit_per_user').val(data.usage_limit_per_user || '');
            $('#coupon_starts_at').val(data.starts_at ? data.starts_at.replace(' ', 'T').substring(0, 16) : '');
            $('#coupon_ends_at').val(data.ends_at ? data.ends_at.replace(' ', 'T').substring(0, 16) : '');
            $('#coupon_status').val(data.status);
        });
    </script>
    @endpush
</x-app-layout>
