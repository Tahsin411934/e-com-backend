<x-app-layout>
    <div class="p-4">
        <div class="flex flex-col md:flex-row md:items-end gap-4 mb-5 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <div class="flex flex-col w-full md:w-1/4">
                <x-form-select label="Store" id="filter_store" class="dt-filter-productTable">
                    <option value="">All Stores</option>
                    @foreach ($stores as $store)
                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                    @endforeach
                </x-form-select>
            </div>
            <div class="flex flex-col w-full md:w-1/4">
                <x-form-select label="Brand" id="filter_brand" class="dt-filter-productTable">
                    <option value="">All Brands</option>
                    @foreach ($brands as $brand)
                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                    @endforeach
                </x-form-select>
            </div>
            <div class="flex flex-col w-full md:w-1/4">
                <x-form-select label="Category" id="filter_category" class="dt-filter-productTable">
                    <option value="">All Categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </x-form-select>
            </div>
            <div class="w-full md:w-auto flex items-end">
                <button id="resetFilters" class="px-4 py-2 text-sm font-medium text-white bg-gray-700 hover:bg-gray-800 rounded-lg transition active:scale-95">
                    Reset
                </button>
            </div>
        </div>

        <x-data-table id="productTable" title="Product Catalog" icon="fa-solid fa-boxes" createPermission="products" buttonLink="{{ route('products.create') }}" buttonText="Add New Product" :columns="['Sort','Select','Store','Brand','SKU','Name','Type','Status','Visibility','Created At','Action']" :ajaxUrl="route('products.dataTable')" :dtColumns="[
            ['data' => null, 'orderable' => false, 'searchable' => false, 'defaultContent' => '', 'exportable' => false],
            ['data' => 'selection', 'orderable' => false, 'searchable' => false, 'exportable' => false],
            ['data' => 'store_name', 'name' => 'store_name'],
            ['data' => 'brand.name'],
            ['data' => 'slug'],
            ['data' => 'name'],
            ['data' => 'product_type'],
            ['data' => 'status'],
            ['data' => 'visibility'],
            ['data' => 'created_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]" :filters="[
            'store_id' => '#filter_store',
            'brand_id' => '#filter_brand',
            'category_id' => '#filter_category',
        ]" :exportButtons="true" />

        @if(auth()->user()->hasPermission('products.delete'))
            <div id="productBulkActions" class="flex flex-wrap items-center gap-3 mt-3 p-4 bg-white border border-gray-200 rounded-xl shadow-sm" aria-busy="false">
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" id="selectPageProducts" class="rounded border-gray-300 text-red-600 focus:ring-red-500"> Select all on this page</label>
                <button type="button" id="deleteSelectedProducts" disabled class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition disabled:opacity-40 disabled:cursor-not-allowed"><i class="fa fa-trash mr-1" aria-hidden="true"></i> <span id="bulkDeleteLabel">Delete Selected</span> (<span id="selectedProductCount" aria-live="polite">0</span>)</button>
                <button type="button" id="clearProductSelection" disabled class="text-sm text-gray-600 hover:text-gray-900 underline disabled:opacity-40 disabled:cursor-not-allowed">Clear selection</button>
                <span class="text-xs text-gray-500">Selections stay across pages. Up to 500 products per batch.</span>
            </div>
        @endif
    </div>

    <x-confirm-delete />

    @push('scripts')
        <!-- Sortable.js for drag-and-drop -->
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
        
        <script>
            function htmlEscape(str) {
                return $('<span>').text(str || '').html();
            }

            function productEdit(id) {
                let editUrl = "{{ route('products.edit', ':id') }}".replace(':id', id);
                window.location.href = editUrl;
            }

            function productDelete(id) {
                let deleteUrl = "{{ route('products.destroy', ':id') }}".replace(':id', id);
                let table = $('#productTable').DataTable();
                Crud.get('shared', 'delete-url')(deleteUrl, table);
            }

            function productDuplicate(id) {
                let showUrl = "{{ route('products.show', ':id') }}".replace(':id', id);
                let duplicateUrl = "{{ route('products.duplicate', ':id') }}".replace(':id', id);

                $.get(showUrl, function(res) {
                    if (res.status !== 'success' || !res.data) {
                        Swal.fire('Error', res.message || 'Product not found.', 'error');
                        return;
                    }

                    let p = res.data;
                    let defaultPrice = (p.variants && p.variants.length > 0)
                        ? (parseFloat(p.variants[0].sale_price) || '')
                        : '';

                    Swal.fire({
                        title: 'Duplicate Product',
                        html:
                            '<input id="dupName" class="swal2-input" placeholder="Product Name" value="' + htmlEscape('Copy of ' + p.name) + '" autofocus>' +
                            '<input id="dupPrice" class="swal2-input" type="number" step="0.01" min="0" placeholder="New Price (optional)" value="' + htmlEscape(defaultPrice) + '">',
                        showCancelButton: true,
                        confirmButtonText: '<i class="fa fa-copy mr-1"></i> Duplicate',
                        cancelButtonText: 'Cancel',
                        focusConfirm: false,
                        preConfirm: function() {
                            let name = document.getElementById('dupName').value.trim();
                            if (!name) {
                                Swal.showValidationMessage('Product name is required.');
                                return false;
                            }
                            return {
                                name: name,
                                price: document.getElementById('dupPrice').value.trim()
                            };
                        }
                    }).then(function(result) {
                        if (!result.isConfirmed) return;

                        $.post(duplicateUrl, result.value, function(res) {
                            if (res.status === 'success') {
                                Toastify({
                                    text: res.message || 'Product duplicated successfully!',
                                    duration: 3000,
                                    gravity: 'bottom',
                                    position: 'right',
                                    style: { background: 'linear-gradient(135deg, #d97706, #f59e0b)' }
                                }).showToast();
                                $('#productTable').DataTable().ajax.reload(null, false);
                            } else {
                                Swal.fire('Error', res.message || 'Failed to duplicate product.', 'error');
                            }
                        }).fail(function(xhr) {
                            let msg = 'Server communication error.';
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            Swal.fire('Error', msg, 'error');
                        });
                    });
                }).fail(function() {
                    Swal.fire('Error', 'Server communication error.', 'error');
                });
            }

            Crud.register('product', 'edit', productEdit);
            Crud.register('product', 'delete', productDelete);
            Crud.register('product', 'duplicate', productDuplicate);

            $(document).ready(function() {
                $('#resetFilters').on('click', function() {
                    // change.select2 repaints the Select2 UI without firing
                    // the DataTable's own change→reload handler (single reload below)
                    $('#filter_store').val('').trigger('change.select2');
                    $('#filter_brand').val('').trigger('change.select2');
                    $('#filter_category').val('').trigger('change.select2');
                    $('#productTable').DataTable().ajax.reload();
                });

                // Initialize DataTable and set up drag-and-drop
                let table = $('#productTable').DataTable();
                initProductDragDrop(table);
                initProductBulkDelete(table);

                // Reinitialize drag-and-drop after each table reload
                $('#productTable').on('draw.dt', function() {
                    initProductDragDrop(table);
                });
            });

            function initProductBulkDelete(table) {
                const selected = new Set();
                let deleting = false;
                const button = $('#deleteSelectedProducts');
                const pageCheckbox = $('#selectPageProducts');
                const clearButton = $('#clearProductSelection');

                function syncSelection() {
                    const boxes = $('#productTable tbody .product-select');
                    boxes.each(function() {
                        this.checked = selected.has(String(this.value));
                        this.disabled = deleting;
                    });
                    const checked = boxes.filter(':checked').length;
                    pageCheckbox.prop('checked', boxes.length > 0 && checked === boxes.length)
                        .prop('indeterminate', checked > 0 && checked < boxes.length).prop('disabled', deleting);
                    $('#selectedProductCount').text(selected.size);
                    button.prop('disabled', deleting || selected.size === 0);
                    clearButton.prop('disabled', deleting || selected.size === 0);
                    $('#productBulkActions').attr('aria-busy', String(deleting));
                    $('#bulkDeleteLabel').text(deleting ? 'Deleting...' : 'Delete Selected');
                }

                $('#productTable').on('draw.dt', syncSelection);
                clearButton.on('click', function() {
                    if (deleting) return;
                    selected.clear();
                    syncSelection();
                });
                $('#productTable').on('change', '.product-select', function() {
                    if (this.checked) {
                        if (selected.size < 500) selected.add(String(this.value));
                    } else selected.delete(String(this.value));
                    syncSelection();
                });
                pageCheckbox.on('change', function() {
                    const checked = this.checked;
                    $('#productTable tbody .product-select').each(function() {
                        if (checked) {
                            if (selected.size < 500) selected.add(String(this.value));
                        } else selected.delete(String(this.value));
                    });
                    syncSelection();
                });
                button.on('click', async function() {
                    if (deleting || !selected.size) return;
                    const productIds = Array.from(selected);
                    deleting = true;
                    syncSelection();
                    $('#bulkDeleteLabel').text('Confirm deletion');
                    const result = await Swal.fire({
                        title: 'Delete selected products?',
                        text: productIds.length + ' selected products will be deleted. Selections across pages are included.',
                        icon: 'warning', showCancelButton: true,
                        confirmButtonText: 'Yes, delete all selected', confirmButtonColor: '#dc2626'
                    });
                    if (!result.isConfirmed) {
                        deleting = false;
                        syncSelection();
                        return;
                    }
                    syncSelection();
                    $.ajax({
                        url: "{{ route('products.bulk-destroy') }}", type: 'POST', dataType: 'json',
                        data: { product_ids: productIds, _token: $('meta[name="csrf-token"]').attr('content') },
                        success: function(res) {
                            if (res.status !== 'success') {
                                Swal.fire('Error', res.message || 'Failed to delete products.', 'error');
                                return;
                            }
                            selected.clear();
                            table.ajax.reload(null, true);
                            Swal.fire('Deleted', res.message, 'success');
                        },
                        error: function(xhr) {
                            Swal.fire('Error', xhr.responseJSON?.message || 'Failed to delete products.', 'error');
                        },
                        complete: function() { deleting = false; syncSelection(); }
                    });
                });
                syncSelection();
            }

            function initProductDragDrop(table) {
                let tbody = document.querySelector('#productTable tbody');
                if (!tbody) return;

                // Destroy existing Sortable instance if it exists
                if (tbody.__sortableInstance) {
                    tbody.__sortableInstance.destroy();
                }

                // Add a dedicated handle cell so the rest of the row remains
                // available for links, text selection and actions.
                tbody.querySelectorAll('tr').forEach(function(row) {
                    let handleCell = row.querySelector('td');
                    if (!handleCell || handleCell.querySelector('.drag-handle')) return;

                    let handle = document.createElement('button');
                    handle.type = 'button';
                    handle.className = 'drag-handle mr-2 inline-flex items-center justify-center text-gray-400 hover:text-primary cursor-grab active:cursor-grabbing';
                    handle.title = 'Drag to reorder';
                    handle.setAttribute('aria-label', 'Drag to reorder product');
                    handle.innerHTML = '<i class="fa-solid fa-grip-vertical" aria-hidden="true"></i>';
                    handleCell.appendChild(handle);
                });

                // Create new Sortable instance
                let sortable = Sortable.create(tbody, {
                    handle: '.drag-handle',
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    dragClass: 'sortable-drag',
                    onEnd: function(evt) {
                        // Collect the new order of product IDs from the table
                        let rows = document.querySelectorAll('#productTable tbody tr');
                        let productIds = [];
                        
                        rows.forEach(function(row) {
                            // Extract product ID from the first cell or hidden data attribute
                            let cells = row.querySelectorAll('td');
                            if (cells.length > 0) {
                                // Assuming the first column is the brand, we need to get the product ID
                                // Since product ID isn't visible, we need to get it from the DataTables API
                                let data = table.row(row).data();
                                if (data && data.id) {
                                    productIds.push(data.id);
                                }
                            }
                        });

                        // Send the new order to the backend
                        if (productIds.length > 0) {
                            sendProductReorder(productIds);
                        }
                    }
                });

                // Store the instance for later cleanup
                tbody.__sortableInstance = sortable;
            }

            function sendProductReorder(productIds) {
                // Show loading state
                Toastify({
                    text: 'Saving product order...',
                    duration: 2000,
                    gravity: 'bottom',
                    position: 'right',
                    style: { background: 'linear-gradient(135deg, #3b82f6, #1d4ed8)' }
                }).showToast();

                $.ajax({
                    url: "{{ route('products.reorder') }}",
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        product_ids: productIds,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        if (res.status === 'success') {
                            Toastify({
                                text: res.message || 'Products reordered successfully!',
                                duration: 3000,
                                gravity: 'bottom',
                                position: 'right',
                                style: { background: 'linear-gradient(135deg, #10b981, #059669)' }
                            }).showToast();
                        } else {
                            Toastify({
                                text: res.message || 'Failed to reorder products.',
                                duration: 3000,
                                gravity: 'bottom',
                                position: 'right',
                                style: { background: 'linear-gradient(135deg, #ef4444, #dc2626)' }
                            }).showToast();
                        }
                    },
                    error: function(xhr) {
                        let msg = 'Server error while saving order.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Toastify({
                            text: msg,
                            duration: 3000,
                            gravity: 'bottom',
                            position: 'right',
                            style: { background: 'linear-gradient(135deg, #ef4444, #dc2626)' }
                        }).showToast();
                    }
                });
            }
        </script>

        <style>
            /* Drag-and-drop visual styles */
            #productTable tbody tr {
                user-select: text;
            }

            #productTable tbody .drag-handle {
                border: 0;
                background: transparent;
                padding: 0.25rem;
                line-height: 1;
            }

            #productTable tbody tr.sortable-ghost {
                opacity: 0.4;
                background-color: #f3f4f6 !important;
            }

            #productTable tbody tr.sortable-drag {
                opacity: 1;
                background-color: #dbeafe !important;
                box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
                border: 2px solid #3b82f6 !important;
            }
        </style>
    @endpush
</x-app-layout>
