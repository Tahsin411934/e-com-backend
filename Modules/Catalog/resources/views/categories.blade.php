<x-app-layout>
    @php
        $categoryStoreFilter = count($stores ?? []) ? [
            'store_id' => [
                'label' => 'Store',
                'options' => '<option value="">All Stores</option>'
                    . collect($stores)->map(fn ($store) => '<option value="'.$store->id.'">'.e($store->name).'</option>')->implode(''),
            ],
        ] : [];
    @endphp
    <x-entity-crud
        id="category"
        createPermission="categories"
        title="Category Catalog"
        icon="fa-solid fa-tags"
        :columns="['Store','Image','Name','Parent','Status','Created At','Action']"
        :dtColumns="[
            ['data' => 'store_name', 'name' => 'store_name'],
            ['data' => 'image_preview', 'orderable' => false, 'searchable' => false],
            ['data' => 'name'],
            ['data' => 'parent'],
            ['data' => 'status'],
            ['data' => 'created_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        :filters="$categoryStoreFilter"
        ajaxUrl="{{ route('categories.dataTable') }}"
        storeUrl="{{ route('categories.store') }}"
        updateUrl="{{ route('categories.update', ':id') }}"
        showUrl="{{ route('categories.show', ':id') }}"
        destroyUrl="{{ route('categories.destroy', ':id') }}"
        drawerTitle="Category"
        dataKey="data"
        idField="category_id"
    >
        <div class="mb-4">
            @if($canAssignStore ?? false)
                <x-form-select label="Store" name="store_id" id="category_store_id" placeholder="Platform (No Store)">
                    @foreach($stores ?? [] as $store)
                        <option value="{{ $store['id'] ?? $store->id }}">{{ $store['name'] ?? $store->name }}</option>
                    @endforeach
                </x-form-select>
            @else
                <x-form-select label="Store" name="store_id_display" id="category_store_id_display" :searchable="false">
                    <option value="">{{ $currentStore?->name ?? 'No Store' }}</option>
                </x-form-select>
                <input type="hidden" name="store_id" id="category_store_id" value="{{ $currentStore?->id ?? '' }}">
            @endif
        </div>
        <div class="mb-4">
            <x-form-select label="Parent Category" name="parent_id" id="category_parent_id">
                <option value="">None</option>
                @foreach ($parents as $parent)
                    <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                @endforeach
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-input label="Name" name="name" id="category_name" placeholder="Category Name" required />
        </div>
        <div class="mb-4">
            <x-form-input label="Slug" name="slug" id="category_slug" placeholder="Category Slug" required />
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1" for="category_image">Category Image</label>
            <input type="file" name="image" id="category_image" accept="image/*"
                class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
            <div id="categoryImagePreview" class="hidden mt-2">
                <img src="" alt="Category preview" class="w-32 h-20 object-cover rounded border" />
            </div>
        </div>
        <div class="mb-4">
            <x-form-input label="Sort Order" name="sort_order" id="category_sort_order" type="number" placeholder="Sort Order" />
        </div>
        <div class="mb-4">
            <x-form-select label="Status" name="status" id="category_status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-textarea label="Description" name="description" id="category_description" placeholder="Category description" rows="4" />
        </div>
    </x-entity-crud>

    @push('scripts')
    <script>
        Crud.register('category', 'fill', function(data) {
            $('#category_store_id').val(data.store_id);
            $('#category_parent_id').val(data.parent_id || '');
            $('#category_name').val(data.name);
            $('#category_slug').val(data.slug);
            $('#category_sort_order').val(data.sort_order || '');
            $('#category_status').val(data.status);
            $('#category_description').val(data.description || '');

            // Show image preview if available
            if (data.category_image_url) {
                $('#categoryImagePreview img').attr('src', data.category_image_url);
                $('#categoryImagePreview').removeClass('hidden');
            } else {
                $('#categoryImagePreview').addClass('hidden');
            }
        });

        $(document).ready(function() {
            $('#category_name').on('input', function() {
                if ($('#category_hid').val() === '') {
                    let slug = $(this).val().toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
                    $('#category_slug').val(slug);
                }
            });

            // Preview image on file select
            $('#category_image').on('change', function() {
                var file = this.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#categoryImagePreview img').attr('src', e.target.result);
                        $('#categoryImagePreview').removeClass('hidden');
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
