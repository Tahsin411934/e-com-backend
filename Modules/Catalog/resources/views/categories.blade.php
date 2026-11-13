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
        :columns="['Store','Image','Name','Parent','Status','Created At','Action','']"
        :dtColumns="[
            ['data' => 'store_name', 'name' => 'store_name'],
            ['data' => 'image_preview', 'orderable' => false, 'searchable' => false],
            ['data' => 'name'],
            ['data' => 'parent'],
            ['data' => 'status'],
            ['data' => 'created_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
            ['data' => 'sort_order', 'visible' => false, 'searchable' => false, 'exportable' => false],
        ]"
        :order="[[7, 'asc']]"
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
            <x-form-select label="Show on Central Website?" name="is_central_category" id="category_is_central_category">
                <option value="0">No</option><option value="1">Yes</option>
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-textarea label="Description" name="description" id="category_description" placeholder="Category description" rows="4" />
        </div>
    </x-entity-crud>

    @push('scripts')
    @if($canReorderCategories)
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
    @endif
    <script>
        (function() {
            if (!@json($canReorderCategories)) return;
            const tableElement = document.getElementById('categoryTable');
            if (!tableElement || typeof Sortable === 'undefined') return;

            let savePending = false;
            let searchWarningShown = false;
            function canDrag(table) {
                const searchInput = tableElement.closest('.reusable-data-table')?.querySelector('.dataTables_filter input');
                const searching = !!(searchInput?.value || '').trim();
                if (!searching) searchWarningShown = false;
                return !savePending && !searching;
            }

            function initializeCategorySorting() {
                const table = $('#categoryTable').DataTable();
                const tbody = tableElement.querySelector('tbody');
                if (!tbody) return;
                if (tbody.__categorySortable) tbody.__categorySortable.destroy();

                tbody.querySelectorAll('tr').forEach(row => {
                    const firstCell = row.querySelector('td');
                    if (!firstCell || firstCell.querySelector('.category-drag-handle')) return;
                    const handle = document.createElement('button');
                    handle.type = 'button';
                    handle.className = 'category-drag-handle mr-2 inline-flex cursor-grab items-center text-gray-400 hover:text-primary active:cursor-grabbing';
                    handle.title = 'Drag to reorder categories';
                    handle.setAttribute('aria-label', 'Drag to reorder category');
                    handle.innerHTML = '<i class="fa-solid fa-grip-vertical" aria-hidden="true"></i>';
                    firstCell.prepend(handle);
                });

                tbody.__categorySortable = Sortable.create(tbody, {
                    handle: '.category-drag-handle',
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    onMove: function() {
                        if (!canDrag(table)) {
                            if (!searchWarningShown) {
                                searchWarningShown = true;
                                Toastify({ text: 'Clear the search before reordering categories.', duration: 2500, gravity: 'bottom', position: 'right', style: { background: '#b45309' } }).showToast();
                            }
                            return false;
                        }
                        return true;
                    },
                    onEnd: function(event) {
                        if (!canDrag(table)) {
                            table.draw(false);
                            return;
                        }

                        const page = table.page.info();
                        const categoryIds = Array.from(tbody.querySelectorAll('tr'))
                            .map(row => table.row(row).data()?.id)
                            .filter(Boolean);
                        if (!categoryIds.length) return;

                        savePending = true;
                        const storeFilter = document.getElementById('filter_category_store_id');
                        $.ajax({
                            url: @json(route('categories.reorder')),
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                category_ids: categoryIds,
                                start: page.start,
                                store_id: storeFilter?.value || '',
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                Toastify({ text: response.message || 'Category order saved.', duration: 2500, gravity: 'bottom', position: 'right', style: { background: '#059669' } }).showToast();
                                table.ajax.reload(null, false);
                            },
                            error: function(xhr) {
                                Toastify({ text: xhr.responseJSON?.message || 'Unable to save category order.', duration: 3500, gravity: 'bottom', position: 'right', style: { background: '#dc2626' } }).showToast();
                                table.ajax.reload(null, false);
                            },
                            complete: function() { savePending = false; }
                        });
                    }
                });
            }

            $(document).on('draw.dt', '#categoryTable', initializeCategorySorting);
            $(document).ready(initializeCategorySorting);
        })();

        Crud.register('category', 'fill', function(data) {
            $('#category_store_id').val(data.store_id);
            $('#category_parent_id').val(data.parent_id || '');
            $('#category_name').val(data.name);
            $('#category_slug').val(data.slug);
            $('#category_sort_order').val(data.sort_order || '');
        $('#category_status').val(data.status);
        $('#category_is_central_category').val(data.is_central_category ? '1' : '0');
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
