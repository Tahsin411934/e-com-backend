<x-app-layout>
    <div class="p-4">
        <div class="flex items-center justify-between mb-5">
            <div>
                <nav class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                    <a href="{{ route('purchase-orders.index') }}" class="hover:text-primary transition-colors">Purchase Orders</a>
                    <i class="fas fa-chevron-right text-[10px]"></i>
                    <span class="text-gray-800 font-medium">Edit: {{ $purchase_order->po_number }}</span>
                </nav>
                <h1 class="text-2xl font-bold text-gray-800">Edit Purchase Order</h1>
            </div>
            <a href="{{ route('purchase-orders.index') }}" class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>

        <form id="poForm" class="space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="purchase_order_id" value="{{ $purchase_order->id }}">

            <!-- Header Card -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-info-circle text-primary"></i> Order Information
                    </h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <x-form-input label="PO Number" name="po_number" id="po_number" value="{{ $purchase_order->po_number }}" readonly />
                    </div>
                    <div>
                        <x-form-select label="Supplier *" name="supplier_id" id="supplier_id">
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ $purchase_order->supplier_id == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                            @endforeach
                        </x-form-select>
                    </div>
                    <div>
                        <x-form-select label="Store *" name="store_id" id="store_id">
                            <option value="">Select Store</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ $purchase_order->store_id == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                            @endforeach
                        </x-form-select>
                    </div>
                    <div>
                        <x-form-input label="Order Date *" name="order_date" id="order_date" type="date" value="{{ $purchase_order->order_date?->format('Y-m-d') }}" />
                    </div>
                    <div>
                        <x-form-input label="Expected Delivery" name="expected_delivery_date" id="expected_delivery_date" type="date" value="{{ $purchase_order->expected_delivery_date?->format('Y-m-d') }}" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <span class="px-3 py-1.5 inline-block rounded-full text-xs font-medium
                            {{ $purchase_order->status == 'draft' ? 'bg-gray-100 text-gray-700' : '' }}
                            {{ $purchase_order->status == 'ordered' ? 'bg-primary-light text-primary' : '' }}
                            {{ $purchase_order->status == 'partially_received' ? 'bg-yellow-100 text-yellow-700' : '' }}
                            {{ $purchase_order->status == 'received' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $purchase_order->status == 'cancelled' ? 'bg-red-100 text-red-700' : '' }}">
                            {{ ucfirst(str_replace('_', ' ', $purchase_order->status)) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Financial Card -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-dollar-sign text-green-600"></i> Financial Details
                    </h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-form-input label="Shipping Cost" name="shipping_cost" id="shipping_cost" type="number" step="0.01" min="0" value="{{ $purchase_order->shipping_cost ?? 0 }}" />
                    </div>
                    <div>
                        <x-form-input label="Tax Amount" name="tax_amount" id="tax_amount" type="number" step="0.01" min="0" value="{{ $purchase_order->tax_amount ?? 0 }}" />
                    </div>
                    <div>
                        <x-form-input label="Discount Amount" name="discount_amount" id="discount_amount" type="number" step="0.01" min="0" value="{{ $purchase_order->discount_amount ?? 0 }}" />
                    </div>
                </div>
            </div>

            <!-- Items Card -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-boxes text-purple-600"></i> Products
                    </h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search & Select Products</label>
                        <select id="productSelect" class="w-full" style="width:100%;"></select>
                        <p class="text-xs text-gray-400 mt-1">Type to search and select a product to add it.</p>
                    </div>

                    <!-- Hidden inputs container -->
                    <div id="poItemsHidden"></div>

                    <!-- Products Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-300 bg-gray-50">
                                    <th class="text-left py-3 px-3 text-gray-600 font-semibold">Product</th>
                                    <th class="text-left py-3 px-3 text-gray-600 font-semibold">SKU</th>
                                    <th class="text-right py-3 px-3 text-gray-600 font-semibold w-20">Qty</th>
                                    <th class="text-right py-3 px-3 text-gray-600 font-semibold w-28">Unit Cost</th>
                                    <th class="text-right py-3 px-3 text-gray-600 font-semibold w-28">Subtotal</th>
                                    <th class="text-center py-3 px-3 text-gray-600 font-semibold w-16">Action</th>
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody">
                            </tbody>
                        </table>
                    </div>

                    <!-- No Items Message -->
                    <div id="emptyMessage" class="text-center py-12">
                        <i class="fas fa-inbox text-4xl text-gray-300 mb-3 block"></i>
                        <p class="text-gray-400 font-medium">No products added yet</p>
                    </div>

                    <!-- Total Row -->
                    <div class="flex justify-end mt-4 border-t border-gray-200 pt-4">
                        <div class="w-full md:w-80">
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="text-gray-600">Subtotal:</span>
                                <span class="font-semibold text-gray-800" id="subtotalAmount">$0.00</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="text-gray-600">Shipping:</span>
                                <span class="font-semibold text-gray-800" id="shippingDisplay">$0.00</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="text-gray-600">Tax:</span>
                                <span class="font-semibold text-gray-800" id="taxDisplay">$0.00</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="text-gray-600">Discount:</span>
                                <span class="font-semibold text-gray-800" id="discountDisplay">$0.00</span>
                            </div>
                            <div class="flex justify-between items-center py-3 bg-primary-light px-3 rounded-lg mt-2">
                                <span class="text-lg font-bold text-primary">Grand Total:</span>
                                <span class="text-2xl font-bold text-primary" id="grandTotal">$0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes Card -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-sticky-note text-yellow-600"></i> Notes
                    </h2>
                </div>
                <div class="p-6">
                    <textarea name="notes" id="notes" rows="3" class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-sm" placeholder="Internal notes...">{{ $purchase_order->notes }}</textarea>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end gap-3 bg-white rounded-xl border border-gray-200 shadow-sm px-6 py-4">
                <a href="{{ route('purchase-orders.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Cancel</a>
                <button type="submit" id="saveBtn" class="px-6 py-2.5 text-sm font-medium text-white bg-primary hover:bg-primary rounded-lg transition flex items-center gap-2 shadow-sm">
                    <i class="fas fa-save"></i> <span id="submitText">Update Purchase Order</span>
                </button>
            </div>
        </form>
    </div>

   <script>
    // State management
    const state = {
        items: [],
        init() {
            @foreach($purchase_order->items as $item)
            this.items.push({
                id: {{ $item->variant_id }},
                product_name: '{{ addslashes($item->variant?->product?->name ?? "") }} - {{ addslashes($item->variant?->name ?? "N/A") }}',
                sku: '{{ addslashes($item->variant?->sku ?? "") }}',
                cost_price: parseFloat({{ $item->unit_cost ?? 0 }}),
                qty: parseInt({{ $item->quantity ?? 1 }})
            });
            @endforeach
        }
    };

    // Initialize state
    state.init();

    // Calculate and update totals
    function updateTotals() {
        let subtotal = 0;
        
        state.items.forEach(item => {
            subtotal += (parseFloat(item.cost_price) || 0) * (parseInt(item.qty) || 0);
        });

        const shipping = parseFloat(document.getElementById('shipping_cost').value) || 0;
        const tax = parseFloat(document.getElementById('tax_amount').value) || 0;
        const discount = parseFloat(document.getElementById('discount_amount').value) || 0;
        const grand = subtotal + shipping + tax - discount;

        document.getElementById('subtotalAmount').textContent = '$' + subtotal.toFixed(2);
        document.getElementById('shippingDisplay').textContent = '$' + shipping.toFixed(2);
        document.getElementById('taxDisplay').textContent = '$' + tax.toFixed(2);
        document.getElementById('discountDisplay').textContent = '$' + discount.toFixed(2);
        document.getElementById('grandTotal').textContent = '$' + grand.toFixed(2);
    }

    // Render table rows
    function renderTable() {
        const tbody = document.getElementById('itemsTableBody');
        const emptyMsg = document.getElementById('emptyMessage');
        const hiddenContainer = document.getElementById('poItemsHidden');
        
        // Clear existing rows and hidden inputs
        tbody.innerHTML = '';
        hiddenContainer.innerHTML = '';

        if (state.items.length === 0) {
            emptyMsg.style.display = 'block';
            updateTotals();
            return;
        }

        emptyMsg.style.display = 'none';

        // Render each row
        state.items.forEach((item, idx) => {
            const cost = parseFloat(item.cost_price) || 0;
            const qty = parseInt(item.qty) || 1;
            const subtotal = cost * qty;

            // Create table row
            const row = document.createElement('tr');
            row.className = 'border-b border-gray-100 hover:bg-gray-50 transition';
            row.id = `row-${idx}`;
            row.innerHTML = `
                <td class="py-3 px-3">
                    <div class="font-medium text-gray-800 text-sm">${item.product_name}</div>
                </td>
                <td class="py-3 px-3 text-xs text-gray-500">${item.sku}</td>
                <td class="py-3 px-3">
                    <input type="number" 
                        class="qty-input w-20 px-2 py-1 border border-gray-300 rounded text-right text-sm"
                        value="${qty}" 
                        min="1" 
                        data-idx="${idx}">
                </td>
                <td class="py-3 px-3">
                    <input type="number" 
                        class="cost-input w-24 px-2 py-1 border border-gray-300 rounded text-right text-sm"
                        value="${cost.toFixed(2)}" 
                        step="0.01" 
                        min="0" 
                        data-idx="${idx}">
                </td>
                <td class="py-3 px-3 text-right font-semibold text-gray-800" id="subtotal-${idx}">
                    $${subtotal.toFixed(2)}
                </td>
                <td class="py-3 px-3 text-center">
                    <button type="button" class="btn-remove text-red-500 hover:text-red-700 transition" data-idx="${idx}">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);

            // Create hidden inputs
            const hiddenDiv = document.createElement('div');
            hiddenDiv.innerHTML = `
                <input type="hidden" name="items[${idx}][variant_id]" value="${item.id}">
                <input type="hidden" name="items[${idx}][quantity]" value="${qty}" class="hidden-qty-${idx}">
                <input type="hidden" name="items[${idx}][unit_cost]" value="${cost.toFixed(2)}" class="hidden-cost-${idx}">
            `;
            hiddenContainer.appendChild(hiddenDiv);
        });

        updateTotals();
        attachEventListeners();
    }

    // Attach event listeners to dynamic elements
    function attachEventListeners() {
        // Qty change
        document.querySelectorAll('.qty-input').forEach(input => {
            input.addEventListener('input', function() {
                const idx = parseInt(this.dataset.idx);
                const newQty = parseInt(this.value) || 1;

                if (newQty < 1) {
                    this.value = 1;
                    state.items[idx].qty = 1;
                } else {
                    state.items[idx].qty = newQty;
                }

                document.querySelector(`.hidden-qty-${idx}`).value = state.items[idx].qty;
                updateRowSubtotal(idx);
                updateTotals();
            });
        });

        // Cost change
        document.querySelectorAll('.cost-input').forEach(input => {
            input.addEventListener('input', function() {
                const idx = parseInt(this.dataset.idx);
                const newCost = parseFloat(this.value) || 0;

                if (newCost < 0) {
                    this.value = 0;
                    state.items[idx].cost_price = 0;
                } else {
                    state.items[idx].cost_price = newCost;
                }

                document.querySelector(`.hidden-cost-${idx}`).value = state.items[idx].cost_price.toFixed(2);
                updateRowSubtotal(idx);
                updateTotals();
            });
        });

        // Remove button
        document.querySelectorAll('.btn-remove').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const idx = parseInt(this.dataset.idx);
                state.items.splice(idx, 1);
                renderTable();
            });
        });
    }

    // Update subtotal for a row
    function updateRowSubtotal(idx) {
        const item = state.items[idx];
        const subtotal = (item.qty || 1) * (item.cost_price || 0);
        document.getElementById(`subtotal-${idx}`).textContent = '$' + subtotal.toFixed(2);
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        renderTable();

        // Select2 initialization
        $('#productSelect').select2({
            ajax: {
                url: '{{ route("purchase-orders.search-products") }}',
                dataType: 'json',
                delay: 300,
                data: params => ({ q: params.term }),
                processResults: data => ({ results: data.results })
            },
            placeholder: 'Search and select product...',
            minimumInputLength: 2
        });

        // Handle product selection
        $('#productSelect').on('select2:select', function (e) {
            const data = e.params.data;

            // Check if product already exists
            const exists = state.items.find(p => p.id === data.id);
            if (exists) {
                Swal.fire('Warning', 'This product is already added!', 'warning');
                $(this).val(null).trigger('change');
                return;
            }

            // Add new item
            state.items.push({
                id: data.id,
                product_name: data.product_name + ' - ' + data.variant_name,
                sku: data.sku,
                cost_price: parseFloat(data.cost_price || 0),
                qty: 1
            });

            renderTable();
            $(this).val(null).trigger('change');
        });

        // Financial charges listeners
        ['shipping_cost', 'tax_amount', 'discount_amount'].forEach(id => {
            document.getElementById(id).addEventListener('input', updateTotals);
        });

        // Form submission
        document.getElementById('poForm').addEventListener('submit', function(e) {
            e.preventDefault();

            if (state.items.length === 0) {
                Swal.fire('Error', 'Please add at least one product', 'error');
                return;
            }

            const saveBtn = document.getElementById('saveBtn');
            const submitText = document.getElementById('submitText');
            
            saveBtn.disabled = true;
            submitText.textContent = 'Updating...';

            const formData = new FormData(this);

            fetch('{{ route("purchase-orders.update", $purchase_order->id) }}', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Toastify({
                        text: data.message,
                        duration: 3000,
                        gravity: 'bottom',
                        position: 'right',
                        style: { background: '#16a34a' }
                    }).showToast();

                    setTimeout(() => {
                        window.location.href = '{{ route("purchase-orders.index") }}';
                    }, 1000);
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Server error: ' + error.message, 'error');
            })
            .finally(() => {
                saveBtn.disabled = false;
                submitText.textContent = 'Update Purchase Order';
            });
        });
    });
   </script>

  
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
   

   
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  
</x-app-layout>