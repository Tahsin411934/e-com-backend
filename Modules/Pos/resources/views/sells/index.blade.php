<x-app-layout>
    <div class="pos-container" style="height: calc(100vh - 64px); display: flex; flex-direction: column; background: #f0f2f5; overflow: hidden;">
        <!-- Top Toolbar -->
        <div class="pos-toolbar" style="background: white; padding: 10px 20px; border-bottom: 1px solid #e0e0e0; display: flex; align-items: center; gap: 12px; flex-shrink: 0;">
            <div class="d-flex align-items-center gap-3" style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap; width: 100%;">
                <h5 class="mb-0 fw-bold" style="margin: 0; font-weight: 700; font-size: 18px; color: #1a1a2e; white-space: nowrap;">
                    <i class="fas fa-cash-register me-2" style="color: #2563eb;"></i>New Sale
                </h5>
                <select id="pos_register_id" class="form-select form-select-sm" style="width: 180px; border-radius: 8px; border: 1.5px solid #e0e0e0; padding: 6px 12px; font-size: 13px;">
                    <option value="">Select Register</option>
                    @foreach($registers as $register)
                        <option value="{{ $register['id'] }}" {{ $loop->first ? 'selected' : '' }}>{{ $register['name'] }}</option>
                    @endforeach
                </select>
                <select id="pos_shift_id" class="form-select form-select-sm" style="width: 180px; border-radius: 8px; border: 1.5px solid #e0e0e0; padding: 6px 12px; font-size: 13px;">
                    <option value="">Select Shift</option>
                    @foreach($openShifts as $shift)
                        <option value="{{ $shift['id'] }}" {{ $loop->first ? 'selected' : '' }}>{{ $shift['name'] ?? 'Shift #'.$shift['id'] }}</option>
                    @endforeach
                </select>
                <div class="ms-auto" style="margin-left: auto; display: flex; gap: 8px;">
                    <button id="btnPosHistory" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px; padding: 6px 14px; font-size: 13px;">
                        <i class="fas fa-history me-1"></i>Recent Sales
                    </button>
                    <button id="btnNewSale" class="btn btn-sm btn-outline-danger" style="border-radius: 8px; padding: 6px 14px; font-size: 13px; display: none;">
                        <i class="fas fa-plus me-1"></i>New Sale
                    </button>
                </div>
            </div>
        </div>

        <!-- Main POS Body -->
        <div class="pos-body" style="display: flex; flex: 1; overflow: hidden; gap: 0;">
            
            <!-- LEFT SIDE: Cart / Sale Items -->
            <div class="pos-cart-panel" style="flex: 1; display: flex; flex-direction: column; background: white; margin: 12px; margin-right: 0; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); overflow: hidden;">
                
                <!-- Cart Header -->
                <div class="cart-header" style="padding: 14px 18px; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center; background: #fafbfc;">
                    <div>
                        <h6 class="mb-0 fw-bold" style="font-size: 15px; color: #1a1a2e;">
                            <i class="fas fa-shopping-cart me-2" style="color: #2563eb;"></i>
                            Sale Items 
                            <span id="cartCount" class="badge bg-primary rounded-pill ms-1" style="font-size: 11px;">0</span>
                        </h6>
                    </div>
                    <button id="clearCartBtn" class="btn btn-sm btn-outline-danger" style="border-radius: 8px; padding: 4px 12px; font-size: 12px; display: none;">
                        <i class="fas fa-trash-alt me-1"></i>Clear
                    </button>
                </div>

                <!-- Cart Items Table -->
                <div class="cart-items" style="flex: 1; overflow-y: auto; padding: 0;">
                    <table class="table table-hover mb-0" style="font-size: 13px;">
                        <thead style="background: #f8f9fa; position: sticky; top: 0; z-index: 2;">
                            <tr>
                                <th style="width: 40%; padding: 10px 14px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d;">Item</th>
                                <th style="width: 15%; padding: 10px 14px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d;">Price</th>
                                <th style="width: 18%; padding: 10px 14px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d;">Qty</th>
                                <th style="width: 15%; padding: 10px 14px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; text-align: right;">Total</th>
                                <th style="width: 12%; padding: 10px 14px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="cartItemsBody">
                            <tr id="emptyCartRow">
                                <td colspan="5" class="text-center py-5" style="color: #adb5bd;">
                                    <i class="fas fa-cart-plus mb-2" style="font-size: 40px; display: block; opacity: 0.3;"></i>
                                    <span style="font-size: 14px;">No items in cart. Search & add products.</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Cart Footer / Totals -->
                <div class="cart-footer" style="border-top: 2px solid #e0e0e0; padding: 14px 18px; background: #f8f9fa;">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between mb-1" style="font-size: 13px;">
                                <span style="color: #6c757d;">Subtotal</span>
                                <span id="cartSubtotal" class="fw-bold" style="color: #1a1a2e;">0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1" style="font-size: 13px;">
                                <span style="color: #6c757d;">Discount</span>
                                <span id="cartDiscount" style="color: #dc3545;">0.00</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between mb-1" style="font-size: 13px;">
                                <span style="color: #6c757d;">Tax</span>
                                <span id="cartTax" style="color: #6c757d;">0.00</span>
                            </div>
                            <div class="d-flex justify-content-between" style="font-size: 18px; font-weight: 700; border-top: 2px solid #1a1a2e; padding-top: 4px;">
                                <span style="color: #1a1a2e;">Total</span>
                                <span id="cartTotal" style="color: #2563eb;">0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Always-visible Complete Sale button (bottom of cart) -->
                    <button id="processSaleBtnLeft" class="btn w-100 mt-3"
                        style="border-radius: 10px; padding: 12px; font-size: 16px; font-weight: 700; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: white; border: none; box-shadow: 0 4px 12px rgba(37,99,235,0.3); transition: all 0.2s;">
                        <i class="fas fa-check-circle me-2"></i>Complete Sale
                    </button>
                </div>
            </div>

            <!-- RIGHT SIDE: Search + Checkout -->
            <div class="pos-right-panel" style="width: 400px; display: flex; flex-direction: column; margin: 12px; gap: 12px; overflow-y: auto; min-height: 0;">
                
                <!-- Customer Section -->
                <div class="pos-section" style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); overflow: hidden; flex-shrink: 0;">
                    <div class="section-header" style="padding: 12px 16px; border-bottom: 1px solid #f0f0f0; background: #fafbfc;">
                        <h6 class="mb-0 fw-bold" style="font-size: 13px; color: #1a1a2e;">
                            <i class="fas fa-user me-2" style="color: #2563eb;"></i>Customer
                        </h6>
                    </div>
                    <div class="section-body" style="padding: 12px 16px;">
                        <div class="position-relative">
                            <div class="input-group" style="border-radius: 8px; overflow: hidden; border: 1.5px solid #e0e0e0;">
                                <span class="input-group-text" style="background: white; border: none; padding: 0 10px;">
                                    <i class="fas fa-search text-muted" style="font-size: 13px;"></i>
                                </span>
                                <input type="text" id="customerSearch" class="form-control" placeholder="Search by phone or name..." 
                                    style="border: none; padding: 8px 4px; font-size: 13px; box-shadow: none;">
                            </div>
                            <div id="customerResults" class="dropdown-menu w-100" style="display: none; max-height: 200px; overflow-y: auto; border-radius: 8px; margin-top: 4px; padding: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                            </div>
                        </div>
                        <div id="selectedCustomer" class="mt-2 p-2 bg-light rounded" style="display: none; border-radius: 8px; font-size: 13px;">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <strong id="custName" style="color: #1a1a2e;"></strong>
                                    <small id="custPhone" class="d-block text-muted"></small>
                                </div>
                                <button id="removeCustomerBtn" class="btn btn-sm btn-link text-danger p-0">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <input type="hidden" id="customer_id" value="">
                        </div>
                        <button id="walkinBtn" class="btn btn-sm btn-outline-secondary mt-2 w-100" style="border-radius: 8px; font-size: 12px; padding: 6px;">
                            <i class="fas fa-person-walking me-1"></i>Walk-in Customer
                        </button>
                    </div>
                </div>

                <!-- Product Search Section -->
                <div class="pos-section" style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); overflow: hidden; flex-shrink: 0;">
                    <div class="section-header" style="padding: 12px 16px; border-bottom: 1px solid #f0f0f0; background: #fafbfc;">
                        <h6 class="mb-0 fw-bold" style="font-size: 13px; color: #1a1a2e;">
                            <i class="fas fa-box me-2" style="color: #2563eb;"></i>Search Products
                        </h6>
                    </div>
                    <div class="section-body" style="padding: 12px 16px;">
                        <div class="position-relative">
                            <div class="input-group" style="border-radius: 8px; overflow: hidden; border: 1.5px solid #e0e0e0;">
                                <span class="input-group-text" style="background: white; border: none; padding: 0 10px;">
                                    <i class="fas fa-barcode text-muted" style="font-size: 13px;"></i>
                                </span>
                                <input type="text" id="productSearch" class="form-control" placeholder="Search by name or SKU..." 
                                    style="border: none; padding: 8px 4px; font-size: 13px; box-shadow: none;" autofocus>
                            </div>
                            <div id="productResults" class="dropdown-menu w-100" style="display: none; max-height: 280px; overflow-y: auto; border-radius: 8px; margin-top: 4px; padding: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Section -->
                <div class="pos-section" style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); overflow: visible; flex-shrink: 0;">
                    <div class="section-header" style="padding: 12px 16px; border-bottom: 1px solid #f0f0f0; background: #fafbfc;">
                        <h6 class="mb-0 fw-bold" style="font-size: 13px; color: #1a1a2e;">
                            <i class="fas fa-credit-card me-2" style="color: #2563eb;"></i>Payment
                        </h6>
                    </div>
                    <div class="section-body" style="padding: 12px 16px;">
                        <div class="mb-2">
                            <label style="font-size: 12px; color: #6c757d; font-weight: 600;">Payment Method</label>
                            <div class="d-flex gap-2 mt-1">
                                <button class="pay-method-btn active btn btn-sm" data-method="cash" 
                                    style="flex: 1; border-radius: 8px; padding: 8px; font-size: 12px; background: #2563eb; color: white; border: none; font-weight: 600;">
                                    <i class="fas fa-money-bill-wave me-1"></i>Cash
                                </button>
                                <button class="pay-method-btn btn btn-sm" data-method="card"
                                    style="flex: 1; border-radius: 8px; padding: 8px; font-size: 12px; background: #f0f0f0; color: #6c757d; border: none;">
                                    <i class="fas fa-credit-card me-1"></i>Card
                                </button>
                                <button class="pay-method-btn btn btn-sm" data-method="mixed"
                                    style="flex: 1; border-radius: 8px; padding: 8px; font-size: 12px; background: #f0f0f0; color: #6c757d; border: none;">
                                    <i class="fas fa-layer-group me-1"></i>Mixed
                                </button>
                            </div>
                        </div>

                        <!-- Cash Payment (default) -->
                        <div id="cashPaymentSection">
                            <div class="mb-2">
                                <label style="font-size: 12px; color: #6c757d; font-weight: 600;">Amount Received</label>
                                <input type="number" id="amountReceived" class="form-control" step="0.01" min="0" value="0"
                                    style="border-radius: 8px; border: 1.5px solid #e0e0e0; padding: 8px 12px; font-size: 16px; font-weight: 700; text-align: center;">
                            </div>
                            <div class="d-flex justify-content-between p-2 bg-light rounded" style="border-radius: 8px;">
                                <span style="font-size: 13px; color: #6c757d;">Change Due</span>
                                <span id="changeDue" class="fw-bold" style="font-size: 18px; color: #059669;">0.00</span>
                            </div>
                        </div>

                        <!-- Mixed Payment Section -->
                        <div id="mixedPaymentSection" style="display: none;">
                            <div class="row g-1 mt-1">
                                <div class="col-6">
                                    <label style="font-size: 11px; color: #6c757d;">Cash</label>
                                    <input type="number" id="mixedCash" class="form-control form-control-sm" step="0.01" min="0" value="0"
                                        style="border-radius: 6px; border: 1.5px solid #e0e0e0; padding: 6px 8px; font-size: 13px;">
                                </div>
                                <div class="col-6">
                                    <label style="font-size: 11px; color: #6c757d;">Card</label>
                                    <input type="number" id="mixedCard" class="form-control form-control-sm" step="0.01" min="0" value="0"
                                        style="border-radius: 6px; border: 1.5px solid #e0e0e0; padding: 6px 8px; font-size: 13px;">
                                </div>
                            </div>
                        </div>

                        <!-- Discount & Notes -->
                        <div class="row g-1 mt-2">
                            <div class="col-6">
                                <label style="font-size: 12px; color: #6c757d; font-weight: 600;">Discount (&#2547;)</label>
                                <input type="number" id="inputDiscount" class="form-control form-control-sm" step="0.01" min="0" value="0"
                                    style="border-radius: 6px; border: 1.5px solid #e0e0e0; padding: 6px 8px; font-size: 13px;">
                            </div>
                            <div class="col-6">
                                <label style="font-size: 12px; color: #6c757d; font-weight: 600;">Tax (&#2547;)</label>
                                <input type="number" id="inputTax" class="form-control form-control-sm" step="0.01" min="0" value="0"
                                    style="border-radius: 6px; border: 1.5px solid #e0e0e0; padding: 6px 8px; font-size: 13px;">
                            </div>
                        </div>
                        <div class="mt-2">
                            <input type="text" id="inputNotes" class="form-control form-control-sm" placeholder="Notes (optional)..."
                                style="border-radius: 6px; border: 1.5px solid #e0e0e0; padding: 6px 8px; font-size: 12px;">
                        </div>

                        <button id="processSaleBtn" class="btn w-100 mt-3" 
                            style="border-radius: 10px; padding: 12px; font-size: 16px; font-weight: 700; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: white; border: none; box-shadow: 0 4px 12px rgba(37,99,235,0.3); transition: all 0.2s;">
                            <i class="fas fa-check-circle me-2"></i>Complete Sale
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Sales Modal -->
    <div class="modal fade" id="recentSalesModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content" style="border-radius: 12px;">
                <div class="modal-header" style="border-bottom: 1px solid #f0f0f0; padding: 16px 20px;">
                    <h5 class="modal-title fw-bold" style="font-size: 16px;"><i class="fas fa-history me-2" style="color: #2563eb;"></i>Recent Sales</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover mb-0" style="font-size: 13px;">
                        <thead style="background: #f8f9fa;">
                            <tr>
                                <th style="padding: 10px 16px;">Receipt</th>
                                <th style="padding: 10px 16px;">Customer</th>
                                <th style="padding: 10px 16px;">Items</th>
                                <th style="padding: 10px 16px;">Total</th>
                                <th style="padding: 10px 16px;">Status</th>
                                <th style="padding: 10px 16px;">Time</th>
                            </tr>
                        </thead>
                        <tbody id="recentSalesBody">
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No recent sales found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Toast / Modal -->
    <div class="modal fade" id="saleSuccessModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content text-center" style="border-radius: 16px; padding: 20px;">
                <div class="modal-body">
                    <div class="mb-3">
                        <i class="fas fa-check-circle" style="font-size: 64px; color: #059669;"></i>
                    </div>
                    <h5 class="fw-bold" style="color: #1a1a2e;">Sale Completed!</h5>
                    <p class="text-muted mb-1" style="font-size: 13px;">Receipt #: <strong id="receiptNumber" class="text-dark"></strong></p>
                    <p class="text-muted mb-3" style="font-size: 13px;">Total: <strong id="saleTotalDisplay" class="text-primary" style="font-size: 20px;"></strong></p>
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" data-bs-dismiss="modal" style="border-radius: 10px; padding: 10px; font-weight: 600;">
                            <i class="fas fa-plus me-1"></i>New Sale
                        </button>
                        <button class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 10px; padding: 10px; font-size: 13px;">
                            <i class="fas fa-times me-1"></i>Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    // Fix for double/multi-HTML-encoded product names ("&amp;amp;amp;").
    function decodeEntities(str) {
        if (typeof str !== 'string') return str;
        for (let i = 0; i < 5 && str.indexOf('&') !== -1; i++) {
            const txt = document.createElement('textarea');
            txt.innerHTML = str;
            const dec = txt.value;
            if (dec === str) break;
            str = dec;
        }
        return str;
    }

    $(document).ready(function() {
        // ====== STATE ======
        let cart = [];
        let selectedCustomer = null;
        let customerSearchTimer = null;
        let productSearchTimer = null;
        let paymentMethod = 'cash';

        // ====== DOM REFS ======
        const $productSearch = $('#productSearch');
        const $productResults = $('#productResults');
        const $customerSearch = $('#customerSearch');
        const $customerResults = $('#customerResults');
        const $cartBody = $('#cartItemsBody');
        const $emptyRow = $('#emptyCartRow');
        const $cartCount = $('#cartCount');
        const $cartSubtotal = $('#cartSubtotal');
        const $cartDiscount = $('#cartDiscount');
        const $cartTax = $('#cartTax');
        const $cartTotal = $('#cartTotal');
        const $clearCart = $('#clearCartBtn');
        const $amountReceived = $('#amountReceived');
        const $changeDue = $('#changeDue');
        const $inputDiscount = $('#inputDiscount');
        const $inputTax = $('#inputTax');
        const $processBtn = $('#processSaleBtn');

        // ====== CUSTOMER SEARCH ======
        $customerSearch.on('input', function() {
            clearTimeout(customerSearchTimer);
            const term = $(this).val();
            if (term.length < 1) {
                $customerResults.hide();
                return;
            }
            customerSearchTimer = setTimeout(() => searchCustomers(term), 300);
        });

        function searchCustomers(term) {
            $.get('{{ route("pos.sell.search-customers") }}', { term: term }, function(res) {
                if (res.status === 'success' && res.data.length > 0) {
                    $customerResults.empty().show();
                    res.data.forEach(c => {
                        $customerResults.append(`
                            <a class="dropdown-item customer-item" href="#" data-id="${c.id}" data-name="${c.name}" data-phone="${c.phone}" style="padding: 8px 12px; border-radius: 6px; font-size: 13px;">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 12px; font-weight: 600;">
                                        ${c.name.charAt(0).toUpperCase()}
                                    </div>
                                    <div>
                                        <strong style="font-size: 13px;">${c.name}</strong>
                                        <small class="d-block text-muted" style="font-size: 11px;"><i class="fas fa-phone me-1"></i>${c.phone} ${c.email !== '-' ? '| ' + c.email : ''}</small>
                                    </div>
                                </div>
                            </a>
                        `);
                    });
                } else {
                    $customerResults.hide();
                }
            });
        }

        $(document).on('click', '.customer-item', function(e) {
            e.preventDefault();
            selectedCustomer = {
                id: $(this).data('id'),
                name: $(this).data('name'),
                phone: $(this).data('phone')
            };
            $('#customer_id').val(selectedCustomer.id);
            $('#custName').text(selectedCustomer.name);
            $('#custPhone').text('📞 ' + selectedCustomer.phone);
            $('#selectedCustomer').show();
            $customerResults.hide();
            $customerSearch.val(selectedCustomer.name).prop('disabled', true);
            $('#walkinBtn').hide();
        });

        $('#removeCustomerBtn').on('click', function() {
            selectedCustomer = null;
            $('#customer_id').val('');
            $('#selectedCustomer').hide();
            $customerSearch.val('').prop('disabled', false).focus();
            $('#walkinBtn').show();
        });

        $('#walkinBtn').on('click', function() {
            selectedCustomer = null;
            $('#customer_id').val('');
            $('#custName').text('Walk-in Customer');
            $('#custPhone').text('No contact info');
            $('#selectedCustomer').show();
            $customerSearch.val('Walk-in Customer').prop('disabled', true);
            $(this).hide();
        });

        // ====== PRODUCT SEARCH ======
        $productSearch.on('input', function() {
            clearTimeout(productSearchTimer);
            const term = $(this).val();
            if (term.length < 1) {
                $productResults.hide();
                return;
            }
            productSearchTimer = setTimeout(() => searchProducts(term), 300);
        });

        $productSearch.on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(productSearchTimer);
                const term = $(this).val();
                if (term.length > 0) searchProducts(term);
            }
        });

        function searchProducts(term) {
            $.get('{{ route("pos.sell.search-products") }}', { term: term }, function(res) {
                if (res.status === 'success' && res.data.length > 0) {
                    $productResults.empty().show();
                    res.data.forEach(p => {
                        $productResults.append(`
                            <a class="dropdown-item product-item" href="#" 
                                data-id="${p.id}" data-name="${decodeEntities(p.name)}" data-price="${p.price}" data-sku="${p.sku}" 
                                style="padding: 8px 12px; border-radius: 6px; font-size: 13px; border-bottom: 1px solid #f5f5f5;">
                                <div class="d-flex align-items-center">
                                    <div class="rounded bg-light d-flex align-items-center justify-content-center me-2 flex-shrink-0" style="width: 40px; height: 40px; overflow: hidden;">
                                        ${p.image ? `<img src="${p.image}" style="width: 100%; height: 100%; object-fit: cover;">` : `<i class="fas fa-box text-muted" style="font-size: 18px;"></i>`}
                                    </div>
                                    <div class="flex-grow-1" style="min-width: 0;">
                                        <strong style="font-size: 13px; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${decodeEntities(p.name)}</strong>
                                        <small class="text-muted" style="font-size: 11px;">
                                            ${p.sku ? 'SKU: ' + p.sku : ''} ${p.brand ? '| ' + p.brand : ''} ${p.unit ? '| ' + p.unit : ''}
                                        </small>
                                    </div>
                                    <div class="text-end ms-2 flex-shrink-0">
                                        <strong style="color: #2563eb; font-size: 14px;">৳${parseFloat(p.price).toFixed(2)}</strong>
                                    </div>
                                </div>
                            </a>
                        `);
                    });
                } else {
                    $productResults.html(`
                        <div class="text-center py-3 text-muted" style="font-size: 13px;">
                            <i class="fas fa-search mb-1" style="font-size: 24px; display: block; opacity: 0.3;"></i>
                            No products found for "<strong>${term}</strong>"
                        </div>
                    `).show();
                }
            });
        }

        $(document).on('click', '.product-item', function(e) {
            e.preventDefault();
            const product = {
                id: $(this).data('id'),
                name: decodeEntities($(this).data('name')),
                price: parseFloat($(this).data('price')),
                sku: $(this).data('sku') || ''
            };
            addToCart(product);
            $productResults.hide();
            $productSearch.val('').focus();
        });

        // ====== CART OPERATIONS ======
        function addToCart(product) {
            const existing = cart.find(item => item.product_id === product.id);
            if (existing) {
                existing.quantity += 1;
                existing.subtotal = existing.unit_price * existing.quantity;
                existing.total = existing.subtotal;
            } else {
                cart.push({
                    product_id: product.id,
                    product_name: product.name,
                    sku: product.sku,
                    unit_price: product.price,
                    quantity: 1,
                    subtotal: product.price,
                    total: product.price
                });
            }
            renderCart();
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            renderCart();
        }

        function updateQuantity(index, newQty) {
            if (newQty < 0.01) {
                removeFromCart(index);
                return;
            }
            cart[index].quantity = newQty;
            cart[index].subtotal = cart[index].unit_price * newQty;
            cart[index].total = cart[index].subtotal;
            renderCart();
        }

        function renderCart() {
            const count = cart.length;
            $cartCount.text(count);
            $clearCart.toggle(count > 0);
            
            if (count === 0) {
                $cartBody.html(`
                    <tr id="emptyCartRow">
                        <td colspan="5" class="text-center py-5" style="color: #adb5bd;">
                            <i class="fas fa-cart-plus mb-2" style="font-size: 40px; display: block; opacity: 0.3;"></i>
                            <span style="font-size: 14px;">No items in cart. Search & add products.</span>
                        </td>
                    </tr>
                `);
                updateTotals();
                return;
            }

            let html = '';
            cart.forEach((item, i) => {
                html += `
                    <tr>
                        <td style="padding: 10px 14px; vertical-align: middle;">
                            <strong style="font-size: 13px;">${decodeEntities(item.product_name)}</strong>
                            <small class="d-block text-muted" style="font-size: 11px;">${item.sku ? 'SKU: ' + item.sku : ''}</small>
                        </td>
                        <td style="padding: 10px 14px; vertical-align: middle; font-weight: 600;">৳${item.unit_price.toFixed(2)}</td>
                        <td style="padding: 6px 14px; vertical-align: middle;">
                            <div class="input-group input-group-sm" style="max-width: 110px;">
                                <button class="btn btn-outline-secondary qty-minus" data-i="${i}" style="padding: 2px 8px; font-size: 11px; border-radius: 6px 0 0 6px;">-</button>
                                <input type="number" class="form-control text-center qty-input" value="${item.quantity}" min="0.01" step="1" data-i="${i}" 
                                    style="padding: 2px 4px; font-size: 13px; font-weight: 600; border-radius: 0; height: 30px;">
                                <button class="btn btn-outline-secondary qty-plus" data-i="${i}" style="padding: 2px 8px; font-size: 11px; border-radius: 0 6px 6px 0;">+</button>
                            </div>
                        </td>
                        <td style="padding: 10px 14px; vertical-align: middle; text-align: right; font-weight: 700; color: #2563eb;">৳${item.total.toFixed(2)}</td>
                        <td style="padding: 10px 14px; vertical-align: middle; text-align: center;">
                            <button class="btn btn-sm btn-link text-danger remove-item" data-i="${i}" style="padding: 4px; font-size: 14px;">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
            $cartBody.html(html);
            updateTotals();
        }

        $(document).on('click', '.qty-minus', function() {
            const i = parseInt($(this).data('i'));
            const current = cart[i].quantity;
            updateQuantity(i, current - 1);
        });

        $(document).on('click', '.qty-plus', function() {
            const i = parseInt($(this).data('i'));
            const current = cart[i].quantity;
            updateQuantity(i, current + 1);
        });

        $(document).on('change', '.qty-input', function() {
            const i = parseInt($(this).data('i'));
            const val = parseFloat($(this).val()) || 0;
            updateQuantity(i, val);
        });

        $(document).on('click', '.remove-item', function() {
            const i = parseInt($(this).data('i'));
            removeFromCart(i);
        });

        $('#clearCartBtn').on('click', function() {
            if (cart.length > 0 && confirm('Clear all items from cart?')) {
                cart = [];
                renderCart();
            }
        });

        function updateTotals() {
            const discount = parseFloat($inputDiscount.val()) || 0;
            const tax = parseFloat($inputTax.val()) || 0;
            
            let subtotal = 0;
            cart.forEach(item => { subtotal += item.total; });
            
            const total = subtotal - discount + tax;
            
            $cartSubtotal.text('৳' + subtotal.toFixed(2));
            $cartDiscount.text('- ৳' + discount.toFixed(2));
            $cartTax.text('+ ৳' + tax.toFixed(2));
            $cartTotal.text('৳' + total.toFixed(2));
            
            // Update change
            calculateChange();
        }

        $inputDiscount.on('input', updateTotals);
        $inputTax.on('input', updateTotals);

        // ====== PAYMENT METHODS ======
        $(document).on('click', '.pay-method-btn', function() {
            $('.pay-method-btn').each(function() {
                $(this).css({ background: '#f0f0f0', color: '#6c757d' });
            });
            $(this).css({ background: '#2563eb', color: 'white' });
            paymentMethod = $(this).data('method');
            
            if (paymentMethod === 'mixed') {
                $('#cashPaymentSection').hide();
                $('#mixedPaymentSection').show();
                $('#amountReceived').val(0);
                $('#changeDue').text('0.00');
            } else {
                $('#cashPaymentSection').show();
                $('#mixedPaymentSection').hide();
                $('#mixedCash').val(0);
                $('#mixedCard').val(0);
                calculateChange();
            }
        });

        $amountReceived.on('input', calculateChange);
        $('#mixedCash, #mixedCard').on('input', calculateChange);

        function calculateChange() {
            const discount = parseFloat($inputDiscount.val()) || 0;
            const tax = parseFloat($inputTax.val()) || 0;
            let subtotal = 0;
            cart.forEach(item => { subtotal += item.total; });
            const total = subtotal - discount + tax;

            if (paymentMethod === 'cash') {
                const received = parseFloat($amountReceived.val()) || 0;
                const change = received - total;
                $changeDue.text(change >= 0 ? '৳' + change.toFixed(2) : '৳0.00');
                if (change < 0 && received > 0) {
                    $changeDue.css('color', '#dc2626');
                } else {
                    $changeDue.css('color', '#059669');
                }
            } else if (paymentMethod === 'mixed') {
                const cash = parseFloat($('#mixedCash').val()) || 0;
                const card = parseFloat($('#mixedCard').val()) || 0;
                const paid = cash + card;
                const change = cash - total;
                $changeDue.text(change >= 0 ? '৳' + change.toFixed(2) : '৳0.00');
            }
        }

        // ====== PROCESS SALE ======
        // The left cart-panel button triggers the same flow as the payment button.
        $('#processSaleBtnLeft').on('click', function() {
            $('#processSaleBtn').trigger('click');
        });

        $('#processSaleBtn').on('click', function() {
            if (cart.length === 0) {
                alert('Please add at least one item to the cart.');
                return;
            }

            const registerId = $('#pos_register_id').val();
            const shiftId = $('#pos_shift_id').val();
            
            if (!registerId) {
                alert('Please select a register.');
                return;
            }
            if (!shiftId) {
                alert('Please select a shift.');
                return;
            }

            const discount = parseFloat($inputDiscount.val()) || 0;
            const tax = parseFloat($inputTax.val()) || 0;
            let subtotal = 0;
            cart.forEach(item => { subtotal += item.total; });
            const total = subtotal - discount + tax;

            const received = paymentMethod === 'cash' ? (parseFloat($amountReceived.val()) || 0) : 0;
            const mixedCash = paymentMethod === 'mixed' ? (parseFloat($('#mixedCash').val()) || 0) : 0;
            const mixedCard = paymentMethod === 'mixed' ? (parseFloat($('#mixedCard').val()) || 0) : 0;
            
            let cashAmount = 0, cardAmount = 0, otherAmount = 0, changeAmount = 0, paymentStatus = 'paid';

            if (paymentMethod === 'cash') {
                cashAmount = received;
                changeAmount = received >= total ? received - total : 0;
                paymentStatus = received >= total ? 'paid' : 'partial';
            } else if (paymentMethod === 'card') {
                cardAmount = total;
                paymentStatus = 'paid';
            } else if (paymentMethod === 'mixed') {
                cashAmount = mixedCash;
                cardAmount = mixedCard;
                changeAmount = mixedCash >= total ? mixedCash - total : 0;
                paymentStatus = (mixedCash + mixedCard) >= total ? 'paid' : 'partial';
            }

            const payload = {
                customer_id: $('#customer_id').val() || null,
                register_id: registerId,
                shift_id: shiftId,
                items: cart,
                subtotal: subtotal,
                tax_amount: tax,
                discount_amount: discount,
                total: total,
                cash_amount: cashAmount,
                card_amount: cardAmount,
                other_amount: otherAmount,
                change_amount: changeAmount,
                payment_status: paymentStatus,
                notes: $('#inputNotes').val() || ''
            };

            const $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Processing...');

            $.ajax({
                url: '{{ route("pos.sell.process") }}',
                method: 'POST',
                data: JSON.stringify(payload),
                contentType: 'application/json',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function(res) {
                    if (res.status === 'success') {
                        $('#receiptNumber').text(res.data.receipt.receipt_number);
                        $('#saleTotalDisplay').text('৳' + parseFloat(res.data.receipt.total).toFixed(2));
                        $('#saleSuccessModal').modal('show');
                        
                        // Reset cart
                        cart = [];
                        renderCart();
                        $amountReceived.val(0);
                        $inputDiscount.val(0);
                        $inputTax.val(0);
                        $inputNotes.val('');
                        $('#changeDue').text('0.00');
                        $('#changeDue').css('color', '#059669');
                    } else {
                        alert(res.message || 'Error processing sale.');
                    }
                },
                error: function(xhr) {
                    alert('Error: ' + (xhr.responseJSON?.message || 'Something went wrong'));
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<i class="fas fa-check-circle me-2"></i>Complete Sale');
                }
            });
        });

        // Reset on new sale modal close
        $('#saleSuccessModal').on('hidden.bs.modal', function() {
            $productSearch.focus();
        });

        // ====== RECENT SALES ======
        $('#btnPosHistory').on('click', function() {
            const registerId = $('#pos_register_id').val();
            $.get('{{ route("pos.sell.recent-sales") }}', { register_id: registerId }, function(res) {
                if (res.status === 'success' && res.data.length > 0) {
                    let html = '';
                    res.data.forEach(s => {
                        html += `
                            <tr>
                                <td style="padding: 10px 16px; font-weight: 600;">${s.receipt_number}</td>
                                <td style="padding: 10px 16px;">${s.customer}</td>
                                <td style="padding: 10px 16px;">${s.items_count}</td>
                                <td style="padding: 10px 16px; font-weight: 700; color: #2563eb;">৳${s.total}</td>
                                <td style="padding: 10px 16px;">
                                    <span class="badge ${s.payment_status === 'paid' ? 'bg-success' : s.payment_status === 'partial' ? 'bg-warning' : 'bg-secondary'}">
                                        ${s.payment_status.charAt(0).toUpperCase() + s.payment_status.slice(1)}
                                    </span>
                                </td>
                                <td style="padding: 10px 16px;">${s.created_at}</td>
                            </tr>
                        `;
                    });
                    $('#recentSalesBody').html(html);
                } else {
                    $('#recentSalesBody').html('<tr><td colspan="6" class="text-center py-4 text-muted">No recent sales found.</td></tr>');
                }
                $('#recentSalesModal').modal('show');
            });
        });

        // Close product results on click outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#productSearch, #productResults').length) {
                $productResults.hide();
            }
            if (!$(e.target).closest('#customerSearch, #customerResults').length) {
                $customerResults.hide();
            }
        });
    });
    </script>
    @endpush
</x-app-layout>