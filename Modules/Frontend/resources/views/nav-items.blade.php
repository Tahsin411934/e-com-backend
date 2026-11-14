<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Navbar Items') }}
        </h2>
    </x-slot>

    @php
        // Admin sees every navbar item + a Store column/filter; Store Owner is
        // scoped server-side to their own store only.
        if ($isStoreOwner) {
            $navColumns = ['Name','Slug','URL','Icon','Sort Order','Status','Created At','Action'];
            $navDtColumns = [
                ['data' => 'name'],
                ['data' => 'slug'],
                ['data' => 'url'],
                ['data' => 'icon'],
                ['data' => 'sort_order'],
                ['data' => 'status'],
                ['data' => 'created_at'],
                ['data' => 'action', 'orderable' => false, 'searchable' => false],
            ];
            $navOrder = [[4, 'asc']];
            $navFilters = [];
        } else {
            $navColumns = ['Store','Name','Slug','URL','Icon','Sort Order','Status','Created At','Action'];
            $navDtColumns = [
                ['data' => 'store_name', 'orderable' => false, 'searchable' => false],
                ['data' => 'name'],
                ['data' => 'slug'],
                ['data' => 'url'],
                ['data' => 'icon'],
                ['data' => 'sort_order'],
                ['data' => 'status'],
                ['data' => 'created_at'],
                ['data' => 'action', 'orderable' => false, 'searchable' => false],
            ];
            $navOrder = [[5, 'asc']];
            $navFilters = ['store_id' => [
                'label' => 'Store',
                'options' => '<option value="">All Stores</option><option value="global">Global (Platform)</option>'
                    . $stores->map(fn ($store) => '<option value="'.e($store->id).'">'.e($store->name).'</option>')->implode(''),
            ]];
        }
    @endphp

    <x-entity-crud
        id="navbar_item"
        createPermission="frontend.navbar"
        title="Navbar Items"
        icon="fa-solid fa-bars"
        :columns="$navColumns"
        :dtColumns="$navDtColumns"
        :filters="$navFilters"
        ajaxUrl="{{ route('frontend.nav-items.navbar.dataTable') }}"
        storeUrl="{{ route('frontend.nav-items.navbar.store') }}"
        updateUrl="{{ route('frontend.nav-items.navbar.update', ':id') }}"
        showUrl="{{ route('frontend.nav-items.navbar.show', ':id') }}"
        destroyUrl="{{ route('frontend.nav-items.navbar.destroy', ':id') }}"
        drawerTitle="Navbar Item"
        dataKey="data"
        idField="navbar_item_id"
        :order="$navOrder"
    >
        @if ($isStoreOwner)
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Store</label>
            <div class="w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 rounded-lg px-3 py-2 text-sm text-gray-600 dark:text-gray-300">
                {{ $stores->first()?->name ?? 'No store assigned' }}
            </div>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Navbar items you create will belong to this store.</p>
        </div>
        @else
        <div class="mb-4">
            <x-form-select label="Store" name="store_id" id="navbar_item_store_id" placeholder="Global (Platform)">
                @foreach ($stores as $store)
                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                @endforeach
            </x-form-select>
        </div>
        @endif
        <div class="mb-4">
            <x-form-input label="Name" name="name" id="navbar_item_name" placeholder="Item Name" required />
        </div>
        <div class="mb-4">
            <x-form-input label="Slug" name="slug" id="navbar_item_slug" placeholder="Item Slug" required />
        </div>
        <div class="mb-4">
            <x-form-input label="URL (optional)" name="url" id="navbar_item_url" placeholder="/example" />
        </div>
        <div class="mb-4">
            <x-form-input label="Icon Class (optional)" name="icon" id="navbar_item_icon" placeholder="fa-solid fa-home" />
        </div>
        <div class="mb-4">
            <x-form-input label="Sort Order" name="sort_order" id="navbar_item_sort_order" type="number" value="0" />
        </div>
        <div class="mb-4">
            <x-form-select label="Status" name="status" id="navbar_item_status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-select label="Show on Central Website?" name="is_central_navbar_item" id="navbar_item_is_central">
                <option value="0">No</option><option value="1">Yes</option>
            </x-form-select>
        </div>
    </x-entity-crud>

    @push('scripts')
    @if($canReorderNavbarItems)
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
    @endif
    <script>
        (function() {
            if (!@json($canReorderNavbarItems)) return;

            const tableElement = document.getElementById('navbar_itemTable');
            if (!tableElement || typeof Sortable === 'undefined') return;

            const isStoreOwner = @json($isStoreOwner);
            let savePending = false;
            let searchWarningShown = false;
            let storeWarningShown = false;

            function selectedStoreScope() {
                if (isStoreOwner) return '__owned__';
                return document.getElementById('filter_navbar_item_store_id')?.value || '';
            }

            function canDrag() {
                const searchInput = tableElement.closest('.reusable-data-table')?.querySelector('.dataTables_filter input');
                const searching = !!(searchInput?.value || '').trim();
                const storeScope = selectedStoreScope();
                const missingStoreScope = !isStoreOwner && !storeScope;

                if (!searching) searchWarningShown = false;
                if (!missingStoreScope) storeWarningShown = false;
                return !savePending && !searching && !missingStoreScope;
            }

            function initializeNavbarSorting() {
                const table = $('#navbar_itemTable').DataTable();
                const tbody = tableElement.querySelector('tbody');
                if (!tbody) return;
                if (tbody.__navbarSortable) tbody.__navbarSortable.destroy();

                tbody.querySelectorAll('tr').forEach(row => {
                    const firstCell = row.querySelector('td');
                    if (!firstCell || firstCell.querySelector('.navbar-drag-handle')) return;
                    const handle = document.createElement('button');
                    handle.type = 'button';
                    handle.className = 'navbar-drag-handle mr-2 inline-flex cursor-grab items-center text-gray-400 hover:text-primary active:cursor-grabbing';
                    handle.title = 'Drag to reorder navbar items';
                    handle.setAttribute('aria-label', 'Drag to reorder navbar item');
                    handle.innerHTML = '<i class="fa-solid fa-grip-vertical" aria-hidden="true"></i>';
                    firstCell.prepend(handle);
                });

                tbody.__navbarSortable = Sortable.create(tbody, {
                    handle: '.navbar-drag-handle',
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    onMove: function() {
                        if (!canDrag()) {
                            const searchInput = tableElement.closest('.reusable-data-table')?.querySelector('.dataTables_filter input');
                            if ((searchInput?.value || '').trim() && !searchWarningShown) {
                                searchWarningShown = true;
                                Toastify({ text: 'Clear the search before reordering navbar items.', duration: 2500, gravity: 'bottom', position: 'right', style: { background: '#b45309' } }).showToast();
                            } else if (!isStoreOwner && !selectedStoreScope() && !storeWarningShown) {
                                storeWarningShown = true;
                                Toastify({ text: 'Choose a store before reordering navbar items.', duration: 3000, gravity: 'bottom', position: 'right', style: { background: '#b45309' } }).showToast();
                            }
                            return false;
                        }
                        return true;
                    },
                    onEnd: function() {
                        if (!canDrag()) {
                            table.draw(false);
                            return;
                        }

                        const page = table.page.info();
                        const navbarItemIds = Array.from(tbody.querySelectorAll('tr'))
                            .map(row => table.row(row).data()?.id)
                            .filter(Boolean);
                        if (!navbarItemIds.length) return;

                        savePending = true;
                        $.ajax({
                            url: @json(route('frontend.nav-items.navbar.reorder')),
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                navbar_item_ids: navbarItemIds,
                                start: page.start,
                                store_id: isStoreOwner ? '' : selectedStoreScope(),
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                Toastify({ text: response.message || 'Navbar order saved.', duration: 2500, gravity: 'bottom', position: 'right', style: { background: '#059669' } }).showToast();
                                table.ajax.reload(null, false);
                            },
                            error: function(xhr) {
                                Toastify({ text: xhr.responseJSON?.message || 'Unable to save navbar order.', duration: 3500, gravity: 'bottom', position: 'right', style: { background: '#dc2626' } }).showToast();
                                table.ajax.reload(null, false);
                            },
                            complete: function() { savePending = false; }
                        });
                    }
                });
            }

            $(document).on('draw.dt', '#navbar_itemTable', initializeNavbarSorting);
            $(document).ready(initializeNavbarSorting);
        })();

        // ===== Navbar Item form fill =====
Crud.register('navbaritem', 'fill', function (data) {
            $('#navbar_item_name').val(data.name);
            $('#navbar_item_slug').val(data.slug);
            $('#navbar_item_url').val(data.url || '');
            $('#navbar_item_icon').val(data.icon || '');
            $('#navbar_item_sort_order').val(data.sort_order || 0);
            $('#navbar_item_status').val(data.status);
            $('#navbar_item_is_central').val(data.is_central_navbar_item ? '1' : '0');
            $('#navbar_item_store_id').val(data.store_id || '');
        });

        $(document).ready(function() {
            // Auto-generate slug from name
            $('#navbar_item_name').on('input', function() {
                if ($('#navbar_item_id').val() === '') {
                    let slug = $(this).val().toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
                    $('#navbar_item_slug').val(slug);
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
