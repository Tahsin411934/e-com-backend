<x-app-layout>
    <div class="p-4">
        <div class="flex flex-col md:flex-row md:items-end gap-4 mb-5 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <div class="flex flex-col w-full md:w-1/3">
                <x-form-select label="Brand" id="filter_brand" class="dt-filter-productTable">
                    <option value="">All Brands</option>
                    @foreach ($brands as $brand)
                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                    @endforeach
                </x-form-select>
            </div>
            <div class="flex flex-col w-full md:w-1/3">
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

        <x-data-table id="productTable" title="Product Catalog" icon="fa-solid fa-boxes" buttonLink="{{ route('products.create') }}" buttonText="Add New Product" :columns="['Brand','SKU','Name','Type','Status','Visibility','Created At','Action']" :ajaxUrl="route('products.dataTable')" :dtColumns="[
            ['data' => 'brand.name'],
            ['data' => 'slug'],
            ['data' => 'name'],
            ['data' => 'product_type'],
            ['data' => 'status'],
            ['data' => 'visibility'],
            ['data' => 'created_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]" :filters="[
            'brand_id' => '#filter_brand',
            'category_id' => '#filter_category',
        ]" :exportButtons="true" />
    </div>

    <x-confirm-delete />

    @push('scripts')
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
                let tableId = '#productTable';
                confirmAndDelete(deleteUrl, tableId);
            }

            function productDuplicate(id) {
                let showUrl = "{{ route('products.show', ':id') }}".replace(':id', id);
                let duplicateUrl = "{{ route('products.duplicate', ':id') }}".replace(':id', id);

                $.get(showUrl, function(res) {
                    if (res.status !== 'success' || !res.product) {
                        Swal.fire('Error', res.message || 'Product not found.', 'error');
                        return;
                    }

                    let p = res.product;
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

            $(document).ready(function() {
                $('#resetFilters').on('click', function() {
                    $('#filter_brand').val('');
                    $('#filter_category').val('');
                    $('#productTable').DataTable().ajax.reload();
                });
            });
        </script>
    @endpush
</x-app-layout>