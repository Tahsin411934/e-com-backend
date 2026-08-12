<x-app-layout>
    <x-entity-crud
        id="accountcategory"
        title="Account Categories"
        icon="fa-solid fa-tags"
        :columns="['Name','Parent','Type','System','Status','Action']"
        :dtColumns="[
            ['data' => 'name'],
            ['data' => 'parent_name'],
            ['data' => 'type'],
            ['data' => 'is_system'],
            ['data' => 'is_active'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        ajaxUrl="{{ route('account-categories.dataTable') }}"
        storeUrl="{{ route('account-categories.store') }}"
        updateUrl="{{ route('account-categories.update', ':id') }}"
        showUrl="{{ route('account-categories.show', ':id') }}"
        destroyUrl="{{ route('account-categories.destroy', ':id') }}"
        drawerTitle="Category"
        dataKey="category"
        idField="category_id"
        :order="[[0, 'asc']]"
    >
        <div class="mb-4"><x-form-input label="Name" name="name" id="accountcategory_name" required /></div>
        <div class="mb-4"><x-form-input label="Slug" name="slug" id="accountcategory_slug" placeholder="AUTO if blank" /></div>
        <div class="mb-4">
            <x-form-select label="Parent Category" name="parent_id" id="accountcategory_parent_id">
                <option value="">None</option>
                @foreach($parentCategories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-select label="Type" name="type" id="accountcategory_type">
                <option value="income">Income</option>
                <option value="expense">Expense</option>
                <option value="cost_of_goods_sold">Cost of Goods Sold</option>
                <option value="asset">Asset</option>
                <option value="liability">Liability</option>
                <option value="equity">Equity</option>
            </x-form-select>
        </div>
        <div class="mb-4">
            <label class="flex items-center gap-2 text-sm text-gray-700"><input type="checkbox" name="is_active" value="1" id="accountcategory_is_active" checked> Active</label>
        </div>
    </x-entity-crud>

    @push('scripts')
    <script>
        window.fillAccountcategoryForm = function(data) {
            $('#accountcategory_name').val(data.name);
            $('#accountcategory_slug').val(data.slug);
            $('#accountcategory_parent_id').val(data.parent_id);
            $('#accountcategory_type').val(data.type);
            $('#accountcategory_is_active').prop('checked', !!data.is_active);
        };
    </script>
    @endpush
</x-app-layout>
