<x-app-layout>
    <div class="p-4">
        <div class="flex items-center justify-between mb-5">
            <div>
                <nav class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                    <a href="{{ route('purchase-orders.index') }}" class="hover:text-primary transition-colors">Purchase Orders</a>
                    <i class="fas fa-chevron-right text-[10px]"></i>
                    <span class="text-gray-800 font-medium">Create New</span>
                </nav>
                <h1 class="text-2xl font-bold text-gray-800">Create Purchase Order</h1>
            </div>
            <a href="{{ route('purchase-orders.index') }}" class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>

        <form id="poForm" class="space-y-6">
            @csrf
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-info-circle text-primary"></i> Order Information
                    </h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <x-form-select label="Supplier *" name="supplier_id" id="supplier_id" required>
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </x-form-select>
                    </div>
                    <div>
                        <x-form-select label="Store *" name="store_id" id="store_id" required>
                            <option value="">Select Store</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}">{{ $store->name }}</option>
                            @endforeach
                        </x-form-select>
                    </div>
                    <div>
                        <x-form-input label="Order Date *" name="order_date" id="order_date" type="date" value="{{ date('Y-m-d') }}" required />
                    </div>
                    <div>
                        <x-form-input label="Expected Delivery" name="expected_delivery_date" id="expected_delivery_date" type="date" />
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-dollar-sign text-green-600"></i> Financial Details
                    </h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-form-input label="Shipping Cost" name="shipping_cost" id="shipping_cost" type="number" step="0.01" min="0" value="0" />
                    </div>
                    <div>
                        <x-form-input label="Tax Amount" name="tax_amount" id="tax_amount" type="number" step="0.01" min="0" value="0" />
                    </div>
                    <div>
                        <x-form-input label="Discount Amount" name="discount_amount" id="discount_amount" type="number" step="0.01" min="0" value="0" />
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-boxes text-purple-600"></i> Products
                    </h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search & Select Products</label>
                        <select id="productSelect" class="w-full" style="width:100%;" aria-label="Search products"></select>
                        <p class="text-xs text-gray-400 mt-1">Click the field above, type to search (min. 3 characters), click a product to add to the list below.</p>
                    </div>

                    <div id="poItemsHidden"></div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm" id="itemsTable">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-2 px-2 text-gray-500 font-medium">Product</th>
                                    <th class="text-left py-2 px-2 text-gray-500 font-medium">SKU</th>
                                    <th class="text-right py-2 px-2 text-gray-500 font-medium w-20">Quantity</th>
                                    <th class="text-right py-2 px-2 text-gray-500 font-medium w-24">Unit Cost</th>
                                    <th class="text-right py-2 px-2 text-gray-500 font-medium w-24">Subtotal</th>
                                    <th class="text-center py-2 px-2 text-gray-500 font-medium w-12">Action</th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <tr id="noItemsRow">
                                    <td colspan="6" class="text-center py-8 text-gray-400">
                                        <i class="fas fa-cart-plus text-3xl mb-2 block"></i>
                                        Select products from the field above to add them here.
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="border-t border-gray-200 font-semibold">
                                    <td colspan="4" class="text-right py-3 px-2 text-gray-600">Grand Total:</td>
                                    <td class="text-right py-3 px-2 text-gray-800 text-lg" id="grandTotal">$0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-sticky-note text-yellow-600"></i> Notes
                    </h2>
                </div>
                <div class="p-6">
                    <textarea name="notes" id="notes" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-sm" placeholder="Internal notes..."></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 bg-white rounded-xl border border-gray-200 shadow-sm px-6 py-4 sticky bottom-4">
                <a href="{{ route('purchase-orders.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Cancel</a>
                <button type="submit" id="saveBtn" class="px-6 py-2.5 text-sm font-medium text-white bg-primary hover:bg-primary rounded-lg transition flex items-center gap-2 shadow-sm">
                    <i class="fas fa-save"></i> <span id="submitText">Save Purchase Order</span>
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    $(document).ready(function() {
        var poItems = [];
        let formChanged = false;
        let debounceTimer;
        let saveInProgress = false;

        // Track form changes
        function markFormChanged() {
            formChanged = true;
        }

        $('#poForm :input').on('change input', function() {
            if ($(this).attr('name') !== 'items') {
                markFormChanged();
            }
        });

        // Warn before leaving with unsaved changes
        window.addEventListener('beforeunload', function(e) {
            if (formChanged && poItems.length > 0 && !saveInProgress) {
                e.preventDefault();
                e.returnValue = '';
                return '';
            }
        });

        function validateForm() {
            var isValid = true;
            var errorMessages = [];

            if (!$('#supplier_id').val()) {
                errorMessages.push('Please select a supplier');
                $('#supplier_id').addClass('border-red-500');
                isValid = false;
            } else {
                $('#supplier_id').removeClass('border-red-500');
            }

            if (!$('#store_id').val()) {
                errorMessages.push('Please select a store');
                $('#store_id').addClass('border-red-500');
                isValid = false;
            } else {
                $('#store_id').removeClass('border-red-500');
            }

            if (!$('#order_date').val()) {
                errorMessages.push('Please select an order date');
                $('#order_date').addClass('border-red-500');
                isValid = false;
            } else {
                $('#order_date').removeClass('border-red-500');
            }

            if (poItems.length === 0) {
                errorMessages.push('Please add at least one product');
                isValid = false;
            }

            if (errorMessages.length > 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validation Error',
                    html: errorMessages.join('<br>'),
                    confirmButtonColor: '#3085d6'
                });
            }

            return isValid;
        }

        function debouncedCalcTotal() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(calcTotal, 100);
        }

        function calcTotal() {
            var t = 0;
            $('.item-subtotal').each(function(){ 
                t += parseFloat($(this).text().replace('$','')) || 0; 
            });
            var s = parseFloat($('#shipping_cost').val()) || 0;
            var tx = parseFloat($('#tax_amount').val()) || 0;
            var d = parseFloat($('#discount_amount').val()) || 0;
            
            var grandTotal = t + s + tx - d;
            if (grandTotal < 0) grandTotal = 0;
            
            $('#grandTotal').text('$' + grandTotal.toFixed(2));
            
            // Change color based on total
            if (grandTotal > 10000) {
                $('#grandTotal').addClass('text-red-600').removeClass('text-gray-800');
            } else {
                $('#grandTotal').removeClass('text-red-600').addClass('text-gray-800');
            }
        }

        function updateHiddenFields() {
            $('#poItemsHidden').empty();
            $.each(poItems, function(i, item) {
                $('#poItemsHidden').append(
                    '<input type="hidden" name="items[' + i + '][variant_id]" value="' + item.id + '">' +
                    '<input type="hidden" name="items[' + i + '][quantity]" class="hq-' + i + '" value="' + (item.qt || 1) + '">' +
                    '<input type="hidden" name="items[' + i + '][unit_cost]" class="hc-' + i + '" value="' + (item.cp || 0).toFixed(2) + '">'
                );
            });
        }

        function renderTable() {
            var tb = $('#itemsBody');
            tb.empty();
            
            if (poItems.length === 0) { 
                tb.append(
                    '<tr id="noItemsRow">' +
                        '<td colspan="6" class="text-center py-8 text-gray-400">' +
                            '<i class="fas fa-cart-plus text-3xl mb-2 block"></i>' +
                            'Select products from the field above to add them here.' +
                        '</td>' +
                    '</tr>'
                );
                calcTotal(); 
                updateHiddenFields();
                return; 
            }
            
            $.each(poItems, function(i, item) {
                var c = item.cp || 0;
                var row = $('<tr>', { 
                    class: 'item-row border-b border-gray-100 hover:bg-gray-50',
                    'data-index': i
                });
                
                row.html(
                    '<td class="py-2 px-2">' +
                        '<div class="flex items-center gap-2">' +
                            '<div class="w-8 h-8 bg-gray-100 rounded flex items-center justify-center text-gray-400 text-xs">' +
                                '<i class="fas fa-box"></i>' +
                            '</div>' +
                            '<div>' +
                                '<div class="font-medium text-gray-800 text-sm">' + escapeHtml(item.nm) + '</div>' +
                            '</div>' +
                        '</div>' +
                    '</td>' +
                    '<td class="py-2 px-2">' +
                        '<span class="text-xs text-gray-500 font-mono">' + escapeHtml(item.sk) + '</span>' +
                    '</td>' +
                    '<td class="py-2 px-2">' +
                        '<input type="number" class="iqty w-20 text-right rounded border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-sm" value="' + (item.qt || 1) + '" min="1" data-index="' + i + '">' +
                    '</td>' +
                    '<td class="py-2 px-2">' +
                        '<input type="number" class="icst w-24 text-right rounded border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-sm" value="' + c.toFixed(2) + '" step="0.01" min="0" data-index="' + i + '">' +
                    '</td>' +
                    '<td class="py-2 px-2 text-right font-medium item-subtotal">$' + (c * (item.qt || 1)).toFixed(2) + '</td>' +
                    '<td class="py-2 px-2 text-center">' +
                        '<button type="button" class="ritem text-gray-400 hover:text-red-500 transition p-1" data-index="' + i + '" title="Remove item">' +
                            '<i class="fas fa-times"></i>' +
                        '</button>' +
                    '</td>'
                );
                
                tb.append(row);
            });
            
            calcTotal();
            updateHiddenFields();
        }

        // Escape HTML to prevent XSS
        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/[&<>]/g, function(m) {
                if (m === '&') return '&amp;';
                if (m === '<') return '&lt;';
                if (m === '>') return '&gt;';
                return m;
            });
        }

        // Initialize Select2 for product search
        $('#productSelect').select2({
            ajax: {
                url: '{{ route("purchase-orders.search-products") }}',
                dataType: 'json', 
                delay: 300,
                data: function(params) { 
                    return { q: params.term }; 
                },
                processResults: function(data) { 
                    return { results: data.results }; 
                },
                cache: true,
                error: function(err) {
                    console.error('Product search error:', err);
                    Toastify({
                        text: 'Error loading products. Please try again.',
                        duration: 3000,
                        gravity: 'bottom',
                        position: 'right',
                        style: { background: '#ef4444' }
                    }).showToast();
                }
            },
            placeholder: '🔍 Type product name or SKU to search (min. 3 characters)...',
            minimumInputLength: 3,
            allowClear: true,
            templateResult: formatProductResult,
            templateSelection: formatProductSelection
        }).on('select2:select', function(e) {
            var d = e.params.data;
            
            // Check for duplicate
            var existingIndex = poItems.findIndex(function(p) { return p.id === d.id; });
            
            if (existingIndex !== -1) {
                // Offer to increase quantity
                Swal.fire({
                    title: 'Product already added',
                    text: 'Do you want to increase the quantity?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, add 1 more',
                    cancelButtonText: 'No, cancel',
                    confirmButtonColor: '#3085d6'
                }).then((result) => {
                    if (result.isConfirmed) {
                        poItems[existingIndex].qt += 1;
                        renderTable();
                        markFormChanged();
                        Toastify({
                            text: 'Quantity increased to ' + poItems[existingIndex].qt,
                            duration: 2000,
                            gravity: 'bottom',
                            position: 'right',
                            style: { background: '#10b981' }
                        }).showToast();
                    }
                });
            } else {
                // Add new product
                poItems.push({ 
                    id: d.id, 
                    nm: d.product_name + ' - ' + d.variant_name, 
                    sk: d.sku, 
                    cp: parseFloat(d.cost_price) || 0, 
                    qt: 1 
                });
                renderTable();
                markFormChanged();
                Toastify({
                    text: 'Product added successfully',
                    duration: 2000,
                    gravity: 'bottom',
                    position: 'right',
                    style: { background: '#10b981' }
                }).showToast();
            }
            
            $(this).val(null).trigger('change');
        });

        function formatProductResult(d) {
            if (d.loading) return d.text;
            return $('<div class="flex items-center gap-2 py-1.5">' +
                '<div class="flex-1">' +
                    '<div class="font-medium text-sm">' + escapeHtml(d.product_name) + ' - ' + escapeHtml(d.variant_name) + '</div>' +
                    '<div class="text-xs text-gray-400">SKU: ' + escapeHtml(d.sku) + ' | Cost: $' + parseFloat(d.cost_price || 0).toFixed(2) + '</div>' +
                '</div>' +
                '<div class="text-right">' +
                    '<div class="text-sm font-semibold text-green-600">$' + parseFloat(d.sale_price || 0).toFixed(2) + '</div>' +
                '</div>' +
            '</div>');
        }

        function formatProductSelection(d) {
            return d.product_name ? d.product_name + ' - ' + d.variant_name : d.text;
        }

        // Quantity change handler
        $(document).on('input', '.iqty', function() {
            var i = $(this).data('index');
            if (!poItems[i]) return;
            
            var newQty = parseInt($(this).val()) || 1;
            if (newQty < 1) newQty = 1;
            
            poItems[i].qt = newQty;
            $(this).val(newQty);
            
            // Update hidden field
            $('.hq-' + i).val(newQty);
            
            // Update subtotal
            var c = poItems[i].cp || 0;
            $(this).closest('tr').find('.item-subtotal').text('$' + (newQty * c).toFixed(2));
            
            calcTotal();
            markFormChanged();
        });

        // Cost change handler
        $(document).on('input', '.icst', function() {
            var i = $(this).data('index');
            if (!poItems[i]) return;
            
            var newCost = parseFloat($(this).val()) || 0;
            if (newCost < 0) newCost = 0;
            
            poItems[i].cp = newCost;
            $(this).val(newCost.toFixed(2));
            
            // Update hidden field
            $('.hc-' + i).val(newCost.toFixed(2));
            
            // Update subtotal
            var q = poItems[i].qt || 1;
            $(this).closest('tr').find('.item-subtotal').text('$' + (q * newCost).toFixed(2));
            
            calcTotal();
            markFormChanged();
        });

        // Remove item handler
        $(document).on('click', '.ritem', function() {
            var i = $(this).data('index');
            if (!poItems[i]) return;
            
            Swal.fire({
                title: 'Remove Product',
                text: 'Are you sure you want to remove ' + poItems[i].nm + '?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, remove',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#d33'
            }).then((result) => {
                if (result.isConfirmed) {
                    poItems.splice(i, 1);
                    renderTable();
                    markFormChanged();
                    Toastify({
                        text: 'Product removed',
                        duration: 2000,
                        gravity: 'bottom',
                        position: 'right',
                        style: { background: '#ef4444' }
                    }).showToast();
                }
            });
        });

        // Financial fields handlers with validation
        $('#shipping_cost, #tax_amount, #discount_amount').on('input', function() {
            var val = parseFloat($(this).val()) || 0;
            if (val < 0) {
                $(this).val(0);
                val = 0;
            }
            if ($(this).attr('id') === 'discount_amount') {
                var total = 0;
                $('.item-subtotal').each(function(){ 
                    total += parseFloat($(this).text().replace('$','')) || 0; 
                });
                if (val > total) {
                    $(this).val(total);
                    val = total;
                    Toastify({
                        text: 'Discount cannot exceed subtotal',
                        duration: 2000,
                        gravity: 'bottom',
                        position: 'right',
                        style: { background: '#f59e0b' }
                    }).showToast();
                }
            }
            debouncedCalcTotal();
            markFormChanged();
        });

        // Form submission
        $('#poForm').on('submit', function(e) {
            e.preventDefault();
            
            if (saveInProgress) {
                return;
            }
            
            if (!validateForm()) {
                return;
            }
            
            saveInProgress = true;
            $('#saveBtn').prop('disabled', true).addClass('opacity-70');
            $('#submitText').html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            
            var formData = new FormData(this);
            
            $.ajax({
                url: '{{ route("purchase-orders.store") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    if (res.status === 'success') {
                        saveInProgress = false;
                        formChanged = false;
                        Toastify({
                            text: res.message || 'Purchase order created successfully!',
                            duration: 3000,
                            gravity: 'bottom',
                            position: 'right',
                            style: { background: 'linear-gradient(135deg, #16a34a, #4ade80)' }
                        }).showToast();
                        
                        setTimeout(function() { 
                            window.location.href = '{{ route("purchase-orders.index") }}'; 
                        }, 1500);
                    } else {
                        saveInProgress = false;
                        Swal.fire('Error', res.message || 'An error occurred', 'error');
                        $('#saveBtn').prop('disabled', false).removeClass('opacity-70');
                        $('#submitText').text('Save Purchase Order');
                    }
                },
                error: function(xhr) {
                    saveInProgress = false;
                    $('#saveBtn').prop('disabled', false).removeClass('opacity-70');
                    $('#submitText').text('Save Purchase Order');
                    
                    var errorMsg = 'Server error occurred';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.errors) {
                            errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                        } else if (xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                    } else if (xhr.status === 0) {
                        errorMsg = 'Network error. Please check your connection.';
                    } else if (xhr.status === 422) {
                        errorMsg = 'Validation failed. Please check all fields.';
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error ' + xhr.status,
                        html: errorMsg,
                        confirmButtonColor: '#d33'
                    });
                }
            });
        });

        // Initial calculation
        calcTotal();
        
        // Add focus/blur effects for better UX
        $('.iqty, .icst').on('focus', function() {
            $(this).addClass('border-primary ring-2 ring-primary-light');
        }).on('blur', function() {
            $(this).removeClass('border-primary ring-2 ring-primary-light');
        });
    });
    </script>
    @endpush
</x-app-layout>