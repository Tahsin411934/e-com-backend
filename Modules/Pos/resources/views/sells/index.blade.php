<x-app-layout>
    @push('head')
        <style>
            /* Page-scoped styles (Tailwind-based look, no Bootstrap) */
            .pos-select-input {
                border-radius: 8px; border: 1.5px solid #e0e0e0; padding: 6px 12px;
                font-size: 13px; background: #fff; color: #1a1a2e;
            }
            .pos-dropdown-results {
                position: absolute; top: 100%; left: 0; right: 0; z-index: 50;
                max-height: 260px; overflow-y: auto; background: #fff;
                border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.10);
                margin-top: 4px; padding: 4px;
            }
            /* Minimal modal (project-styled, no Bootstrap) */
            .pos-modal {
                position: fixed; inset: 0; z-index: 1055; display: none;
                align-items: center; justify-content: center; padding: 16px;
            }
            .pos-modal.show { display: flex; }
            .pos-modal-backdrop {
                position: fixed; inset: 0; z-index: 1050; background: rgba(15,23,42,0.5);
                transition: opacity .15s linear; opacity: 0; display: none;
            }
            .pos-modal-backdrop.show { opacity: 1; display: block; }
            .pos-modal-dialog {
                background: #fff; border-radius: 14px; width: 100%; max-width: 430px;
                box-shadow: 0 20px 50px rgba(15,23,42,0.35); max-height: 90vh; display: flex; flex-direction: column;
            }
            .pos-modal-dialog.lg { max-width: 640px; }
            body.modal-open { overflow: hidden; }
        </style>
    @endpush
    <div class="pos-container" style="height: calc(100vh - 64px); display: flex; flex-direction: column; background: #f0f2f5; overflow: hidden;">
        <!-- Top Toolbar -->
        <div class="pos-toolbar" style="background: white; padding: 10px 20px; border-bottom: 1px solid #e0e0e0; display: flex; align-items: center; gap: 12px; flex-shrink: 0;">
            <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap; width: 100%;">
                <h4 style="margin: 0; font-weight: 700; font-size: 18px; color: #1a1a2e; white-space: nowrap;">
                    <i class="fas fa-cash-register" style="margin-right: 8px; color: #2563eb;"></i>New Sale
                </h4>
                <select id="pos_register_id" data-local-select2 data-placeholder="Select Register" class="pos-select-input" style="width: 180px;">
                    <option value="">Select Register</option>
                    @foreach($registers as $register)
                        <option value="{{ $register['id'] }}" {{ $loop->first ? 'selected' : '' }}>{{ $register['name'] }}</option>
                    @endforeach
                </select>
                <select id="pos_shift_id" data-local-select2 data-placeholder="Select Shift" class="pos-select-input" style="width: 180px;">
                    <option value="">Select Shift</option>
                    @foreach($openShifts as $shift)
                        <option value="{{ $shift['id'] }}" {{ $loop->first ? 'selected' : '' }}>{{ $shift['name'] ?? 'Shift #'.$shift['id'] }}</option>
                    @endforeach
                </select>
                <div style="margin-left: auto; display: flex; gap: 8px;">
                    <button id="btnPosHistory" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 px-3.5 py-1.5 text-[13px] font-medium text-gray-600 hover:bg-gray-50 transition-colors" style="background:#fff;">
                        <i class="fas fa-history"></i>Recent Sales
                    </button>
                    <button id="btnNewSale" class="inline-flex items-center gap-1.5 rounded-lg border border-red-300 px-3.5 py-1.5 text-[13px] font-medium text-red-500 hover:bg-red-50 transition-colors" style="background:#fff; display: none;">
                        <i class="fas fa-plus"></i>New Sale
                    </button>
                </div>
            </div>
        </div>

        <!-- Main POS Body -->
        <div class="pos-body" style="display: flex; flex: 1; overflow: hidden; gap: 0;">
            
            <!-- LEFT SIDE: Cart / Sale Items -->
            <div class="pos-cart-panel" style="flex: 1; display: flex; flex-direction: column; background: white; margin: 12px; margin-right: 0; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); overflow: hidden;">
                
                <!-- Cart Header -->
                <div style="padding: 14px 18px; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center; background: #fafbfc;">
                    <div>
                        <h6 style="margin:0; font-size: 15px; font-weight:700; color: #1a1a2e;">
                            <i class="fas fa-shopping-cart" style="margin-right:8px; color: #2563eb;"></i>
                            Sale Items
                            <span id="cartCount" class="inline-flex items-center rounded-full bg-blue-600 px-2 py-0.5 text-[11px] font-semibold text-white align-middle ml-1.5" style="background:#2563eb;">0</span>
                        </h6>
                    </div>
                    <button id="clearCartBtn" class="inline-flex items-center gap-1 rounded-lg border border-red-300 px-3 py-1 text-[12px] font-medium text-red-500 hover:bg-red-50 transition-colors" style="background:#fff; display: none;">
                        <i class="fas fa-trash-alt"></i>Clear
                    </button>
                </div>

                <!-- Cart Items Table -->
                <div style="flex: 1; overflow-y: auto;">
                    <table style="width:100%; border-collapse: collapse; font-size: 13px;">
                        <thead style="background: #f8f9fa; position: sticky; top: 0; z-index: 2;">
                            <tr>
                                <th style="text-align:left; width: 40%; padding: 10px 14px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d;">Item</th>
                                <th style="text-align:left; width: 15%; padding: 10px 14px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d;">Price</th>
                                <th style="text-align:left; width: 18%; padding: 10px 14px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d;">Qty</th>
                                <th style="text-align:right; width: 15%; padding: 10px 14px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d;">Total</th>
                                <th style="text-align:center; width: 12%; padding: 10px 14px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="cartItemsBody">
                            <tr id="emptyCartRow">
                                <td colspan="5" style="text-align:center; padding: 24px 0; color: #adb5bd;">
                                    <i class="fas fa-cart-plus" style="font-size: 40px; display: block; opacity: 0.3; margin-bottom:8px;"></i>
                                    <span style="font-size: 14px;">No items in cart. Search & add products.</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Cart Footer / Totals -->
                <div style="border-top: 2px solid #e0e0e0; padding: 14px 18px; background: #f8f9fa;">
                    <div style="display:flex; gap:8px;">
                        <div style="flex:1;">
                            <div style="display:flex; justify-content: space-between; margin-bottom:4px; font-size: 13px;">
                                <span style="color: #6c757d;">Subtotal</span>
                                <span id="cartSubtotal" style="font-weight:700; color: #1a1a2e;">0.00</span>
                            </div>
                            <div style="display:flex; justify-content: space-between; margin-bottom:4px; font-size: 13px;">
                                <span style="color: #6c757d;">Discount</span>
                                <span id="cartDiscount" style="color: #dc3545;">0.00</span>
                            </div>
                        </div>
                        <div style="flex:1;">
                            <div style="display:flex; justify-content: space-between; margin-bottom:4px; font-size: 13px;">
                                <span style="color: #6c757d;">Tax</span>
                                <span id="cartTax" style="color: #6c757d;">0.00</span>
                            </div>
                            <div style="display:flex; justify-content: space-between; font-size: 18px; font-weight: 700; border-top: 2px solid #1a1a2e; padding-top: 4px;">
                                <span style="color: #1a1a2e;">Total</span>
                                <span id="cartTotal" style="color: #2563eb;">0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Always-visible Complete Sale button (bottom of cart) -->
                    <button id="processSaleBtnLeft" class="w-full mt-3 inline-flex items-center justify-center gap-2"
                        style="border-radius: 10px; padding: 12px; font-size: 16px; font-weight: 700; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: white; border: none; box-shadow: 0 4px 12px rgba(37,99,235,0.3); transition: all 0.2s;">
                        <i class="fas fa-check-circle"></i>Complete Sale
                    </button>
                </div>
            </div>

            <!-- RIGHT SIDE: Search + Checkout -->
            <div class="pos-right-panel" style="width: 400px; display: flex; flex-direction: column; margin: 12px; gap: 12px; overflow-y: auto; min-height: 0;">
                
                <!-- Customer Section -->
                <div style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); overflow: visible; flex-shrink: 0;">
                    <div style="padding: 12px 16px; border-bottom: 1px solid #f0f0f0; background: #fafbfc;">
                        <h6 style="margin:0; font-size: 13px; font-weight:700; color: #1a1a2e;">
                            <i class="fas fa-user" style="margin-right:8px; color: #2563eb;"></i>Customer
                        </h6>
                    </div>
                    <div style="padding: 12px 16px;">
                        <div style="position: relative;">
                            <div style="display: flex; align-items: center; border-radius: 8px; overflow: hidden; border: 1.5px solid #e0e0e0; background:#fff;">
                                <span style="padding: 0 10px;"><i class="fas fa-search" style="font-size: 13px; color:#6c757d;"></i></span>
                                <input type="text" id="customerSearch" placeholder="Search by phone or name..."
                                    style="flex:1; border: none; outline: none; padding: 8px 4px; font-size: 13px; background: transparent;">
                            </div>
                            <div id="customerResults" class="pos-dropdown-results" style="display: none;"></div>
                        </div>
                        <div id="selectedCustomer" style="display: none; margin-top:8px; padding: 8px; background:#f8f9fa; border-radius: 8px; font-size: 13px;">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <div>
                                    <strong id="custName" style="color: #1a1a2e;"></strong>
                                    <small id="custPhone" style="display:block; color:#6c757d;"></small>
                                </div>
                                <button id="removeCustomerBtn" style="border:none; background:none; color:#dc2626; cursor:pointer;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <input type="hidden" id="customer_id" value="">
                        </div>
                        <button id="walkinBtn" class="w-full inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-300"
                            style="margin-top:8px; background:#fff; font-size: 12px; padding: 6px; color:#4b5563; hover:bg:#f9fafb;">
                            <i class="fas fa-person-walking"></i>Walk-in Customer
                        </button>
                    </div>
                </div>

                <!-- Product Search Section -->
                <div style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); overflow: visible; flex-shrink: 0;">
                    <div style="padding: 12px 16px; border-bottom: 1px solid #f0f0f0; background: #fafbfc;">
                        <h6 style="margin:0; font-size: 13px; font-weight:700; color: #1a1a2e;">
                            <i class="fas fa-box" style="margin-right:8px; color: #2563eb;"></i>Search Products
                        </h6>
                    </div>
                    <div style="padding: 12px 16px;">
                        <div style="position: relative;">
                            <div style="display: flex; align-items: center; border-radius: 8px; overflow: hidden; border: 1.5px solid #e0e0e0; background:#fff;">
                                <span style="padding: 0 10px;"><i class="fas fa-barcode" style="font-size: 13px; color:#6c757d;"></i></span>
                                <input type="text" id="productSearch" placeholder="Search by name or SKU..."
                                    style="flex:1; border: none; outline: none; padding: 8px 4px; font-size: 13px; background: transparent;" autofocus>
                            </div>
                            <div id="productResults" class="pos-dropdown-results" style="display: none;"></div>
                        </div>
                    </div>
                </div>

                <!-- Payment Section -->
                <div style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); overflow: visible; flex-shrink: 0;">
                    <div style="padding: 12px 16px; border-bottom: 1px solid #f0f0f0; background: #fafbfc;">
                        <h6 style="margin:0; font-size: 13px; font-weight:700; color: #1a1a2e;">
                            <i class="fas fa-credit-card" style="margin-right:8px; color: #2563eb;"></i>Payment
                        </h6>
                    </div>
                    <div style="padding: 12px 16px;">
                        <div style="margin-bottom:8px;">
                            <label style="font-size: 12px; color: #6c757d; font-weight: 600;">Payment Method</label>
                            <div style="display: flex; gap: 8px; margin-top:4px;">
                                <button class="pay-method-btn active" data-method="cash"
                                    style="flex: 1; border-radius: 8px; padding: 8px; font-size: 12px; background: #2563eb; color: white; border: none; font-weight: 600; cursor:pointer;">
                                    <i class="fas fa-money-bill-wave" style="margin-right:4px;"></i>Cash
                                </button>
                                <button class="pay-method-btn" data-method="card"
                                    style="flex: 1; border-radius: 8px; padding: 8px; font-size: 12px; background: #f0f0f0; color: #6c757d; border: none; font-weight:600; cursor:pointer;">
                                    <i class="fas fa-credit-card" style="margin-right:4px;"></i>Card
                                </button>
                                <button class="pay-method-btn" data-method="mixed"
                                    style="flex: 1; border-radius: 8px; padding: 8px; font-size: 12px; background: #f0f0f0; color: #6c757d; border: none; font-weight:600; cursor:pointer;">
                                    <i class="fas fa-layer-group" style="margin-right:4px;"></i>Mixed
                                </button>
                            </div>
                        </div>

                        <!-- Cash Payment (default) -->
                        <div id="cashPaymentSection">
                            <div style="margin-bottom:8px;">
                                <label style="font-size: 12px; color: #6c757d; font-weight: 600;">Amount Received</label>
                                <input type="number" id="amountReceived" step="0.01" min="0" value="0"
                                    style="width:100%; border-radius: 8px; border: 1.5px solid #e0e0e0; padding: 8px 12px; font-size: 16px; font-weight: 700; text-align: center; outline:none;">
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 8px; background:#f8f9fa; border-radius: 8px;">
                                <span id="changeDueLabel" style="font-size: 13px; color: #6c757d;">Change Due</span>
                                <span id="changeDue" style="font-weight:700; font-size: 18px; color: #059669;">0.00</span>
                            </div>
                        </div>

                        <!-- Mixed Payment Section -->
                        <div id="mixedPaymentSection" style="display: none;">
                            <div style="display:flex; gap:4px; margin-top:4px;">
                                <div style="flex:1;">
                                    <label style="font-size: 11px; color: #6c757d;">Cash</label>
                                    <input type="number" id="mixedCash" step="0.01" min="0" value="0"
                                        style="width:100%; border-radius: 6px; border: 1.5px solid #e0e0e0; padding: 6px 8px; font-size: 13px; outline:none;">
                                </div>
                                <div style="flex:1;">
                                    <label style="font-size: 11px; color: #6c757d;">Card</label>
                                    <input type="number" id="mixedCard" step="0.01" min="0" value="0"
                                        style="width:100%; border-radius: 6px; border: 1.5px solid #e0e0e0; padding: 6px 8px; font-size: 13px; outline:none;">
                                </div>
                            </div>
                        </div>

                        <!-- Discount & Notes -->
                        <div style="display:flex; gap:4px; margin-top:8px;">
                            <div style="flex:1;">
                                <label style="font-size: 12px; color: #6c757d; font-weight: 600;">Discount (&#2547;)</label>
                                <input type="number" id="inputDiscount" step="0.01" min="0" value="0"
                                    style="width:100%; border-radius: 6px; border: 1.5px solid #e0e0e0; padding: 6px 8px; font-size: 13px; outline:none;">
                            </div>
                            <div style="flex:1;">
                                <label style="font-size: 12px; color: #6c757d; font-weight: 600;">Tax (&#2547;)</label>
                                <input type="number" id="inputTax" step="0.01" min="0" value="0"
                                    style="width:100%; border-radius: 6px; border: 1.5px solid #e0e0e0; padding: 6px 8px; font-size: 13px; outline:none;">
                            </div>
                        </div>
                        <div style="margin-top:6px;">
                            <input type="text" id="inputNotes" placeholder="Notes (optional)..."
                                style="width:100%; border-radius: 6px; border: 1.5px solid #e0e0e0; padding: 6px 8px; font-size: 12px; outline:none;">
                        </div>

                        <button id="processSaleBtn" class="w-full inline-flex items-center justify-center gap-2"
                            style="margin-top:10px; border-radius: 10px; padding: 12px; font-size: 16px; font-weight: 700; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: white; border: none; box-shadow: 0 4px 12px rgba(37,99,235,0.3); transition: all 0.2s; cursor:pointer;">
                            <i class="fas fa-check-circle"></i>Complete Sale
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Sales Modal -->
    <div class="pos-modal" id="recentSalesModal">
        <div class="pos-modal-dialog lg">
            <div style="padding: 16px 20px; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center;">
                <h5 style="margin:0; font-size: 16px; font-weight:700; color:#1a1a2e;"><i class="fas fa-history" style="margin-right:8px; color:#2563eb;"></i>Recent Sales</h5>
                <button type="button" data-modal-close aria-label="Close" style="border:none; background:none; font-size:20px; color:#6c757d; cursor:pointer;">&times;</button>
            </div>
            <div style="max-height: 400px; overflow-y: auto;">
                <table style="width:100%; border-collapse: collapse; font-size: 13px;">
                    <thead style="background: #f8f9fa; position: sticky; top: 0;">
                        <tr>
                            <th style="text-align:left; padding: 10px 16px;">Receipt</th>
                            <th style="text-align:left; padding: 10px 16px;">Customer</th>
                            <th style="text-align:left; padding: 10px 16px;">Items</th>
                            <th style="text-align:right; padding: 10px 16px;">Total</th>
                            <th style="text-align:left; padding: 10px 16px;">Status</th>
                            <th style="text-align:left; padding: 10px 16px;">Time</th>
                        </tr>
                    </thead>
                    <tbody id="recentSalesBody">
                        <tr>
                            <td colspan="6" style="text-align:center; padding:16px 0; color:#6c757d;">No recent sales found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Success Toast / Modal -->
    <div class="pos-modal" id="saleSuccessModal">
        <div class="pos-modal-dialog" style="max-width: 360px; text-align: center; padding: 20px;">
            <div style="margin-bottom:12px;">
                <i class="fas fa-check-circle" style="font-size: 64px; color: #059669;"></i>
            </div>
            <h5 style="margin:0 0 8px; color:#1a1a2e; font-weight:700;">Sale Completed!</h5>
            <p style="margin:0 0 4px; color:#6c757d; font-size: 13px;">Receipt #: <strong id="receiptNumber" style="color:#1a1a2e;"></strong></p>
            <p style="margin:0 0 12px; color:#6c757d; font-size: 13px;">Total: <strong id="saleTotalDisplay" style="color:#2563eb; font-size: 20px;"></strong></p>
            <div style="display:flex; flex-direction: column; gap:8px;">
                <button data-modal-close class="w-full inline-flex items-center justify-center gap-1" style="border-radius: 10px; padding: 10px; background: #2563eb; color:#fff; border:none; font-weight:600; cursor:pointer;">
                    <i class="fas fa-plus"></i>New Sale
                </button>
                <button data-modal-close class="w-full inline-flex items-center justify-center gap-1" style="border-radius: 10px; padding: 10px; background:#fff; color:#4b5563; border:1px solid #e5e7eb; font-size:13px; cursor:pointer;">
                    <i class="fas fa-times"></i>Close
                </button>
            </div>
        </div>
    </div>

    <!-- Variant / Color Picker Modal -->
    <div class="pos-modal" id="variantPickerModal">
        <div class="pos-modal-dialog">
            <div style="background: #1a1a2e; color: white; padding: 14px 18px; display: flex; justify-content: space-between; align-items: center;">
                <h5 style="margin:0; font-size: 15px;"><i class="fas fa-box-open" style="margin-right:8px;"></i>Select Variant / Color</h5>
                <button type="button" data-modal-close aria-label="Close" style="border:none; background:none; color:#fff; font-size:20px; cursor:pointer;">&times;</button>
            </div>
            <div style="padding: 18px; max-height: 80vh; overflow-y: auto;">
                <div style="display: flex; align-items: center; margin-bottom:12px;">
                    <div style="width: 52px; height: 52px; border-radius: 8px; background:#f8f9fa; display:flex; align-items:center; justify-content:center; overflow:hidden; margin-right:12px; flex-shrink:0;">
                        <img id="vpImage" src="" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                        <i id="vpImageFallback" class="fas fa-box" style="font-size: 22px; color:#6c757d;"></i>
                    </div>
                    <div style="min-width: 0;">
                        <strong id="vpProductName" style="font-size: 14px; display: block;"></strong>
                    </div>
                </div>

                <div style="margin-bottom:12px;">
                    <label style="font-size: 12px; font-weight: 600; color: #6c757d;">Variant</label>
                    <div id="vpVariants" style="display: flex; flex-wrap: wrap; gap:8px; margin-top:4px;"></div>
                </div>

                <div style="margin-bottom:12px; display: none;" id="vpColorSection">
                    <label style="font-size: 12px; font-weight: 600; color: #6c757d;">Color</label>
                    <div id="vpColors" style="display: flex; flex-wrap: wrap; gap:8px; margin-top:4px;"></div>
                </div>

                <div style="background: #f8fafc; border: 1px solid #eef2f7; border-radius: 8px; padding: 12px; margin-bottom:12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div id="vpSelectedLabel" style="font-size: 13px; color: #334155;">Select a variant</div>
                            <div id="vpStock" style="font-size: 12px; color: #059669;">Stock: -</div>
                        </div>
                        <div style="text-align:right;">
                            <span id="vpDiscountBadge" style="font-size: 10px; background:#dc2626; color:#fff; padding:2px 6px; border-radius: 9999px; display: none;">-0%</span>
                            <span id="vpCampaignBadge" style="font-size: 10px; background:#7c3aed; color:#fff; padding:2px 6px; border-radius: 9999px; display: none;"></span>
                            <div id="vpOldPrice" style="font-size: 12px; color: #dc2626; text-decoration: line-through; display: none;"></div>
                            <div id="vpFinalPrice" style="font-size: 20px; font-weight: 800; color: #2563eb;">0.00</div>
                        </div>
                    </div>
                </div>

                <button id="vpAddBtn" class="w-full inline-flex items-center justify-center gap-2" disabled style="border-radius: 10px; padding: 12px; background:#2563eb; color:#fff; border:none; font-weight:700; font-size: 15px; cursor:pointer;">
                    <i class="fas fa-cart-plus"></i>Add To Cart
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let isPosSubmitting = false;
    // Custom modal helper (project-scoped, no Bootstrap).
    function posToastSuccess(message) {
        Toastify({
            text: message,
            duration: 4000,
            close: true,
            gravity: 'bottom',
            position: 'right',
            stopOnFocus: true,
            style: { background: 'linear-gradient(135deg, #16a34a, #4ade80)' }
        }).showToast();
    }

    function posToastError(message) {
        Toastify({
            text: message,
            duration: 3500,
            close: true,
            gravity: 'bottom',
            position: 'right',
            stopOnFocus: true,
            style: { background: 'linear-gradient(135deg, #dc2626, #f87171)' }
        }).showToast();
    }

    function bsModal(action, selector) {
        const el = document.querySelector(selector);
        if (!el) return;

        let backdrop = null;
        document.querySelectorAll('.pos-modal-backdrop').forEach(b => b.remove());

        if (action === 'show') {
            el.classList.add('show');
            document.body.classList.add('modal-open');
            const bd = document.createElement('div');
            bd.className = 'pos-modal-backdrop show';
            bd.setAttribute('data-target', selector);
            document.body.appendChild(bd);
            backdrop = bd;
            backdrop.addEventListener('click', function () { bsModal('hide', selector); });
        } else {
            el.classList.remove('show');
            document.body.classList.remove('modal-open');
            document.querySelectorAll('.pos-modal-backdrop').forEach(b => b.remove());
            if (window.jQuery) { window.jQuery(el).trigger('posModalHidden'); }
        }
    }

    // Close buttons inside modals use data-modal-close.
    $(document).on('click', '[data-modal-close]', function(e) {
        e.preventDefault();
        const modal = $(this).closest('.pos-modal');
        if (modal.length) bsModal('hide', '#' + modal.attr('id'));
    });

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
                            <a class="customer-item" href="#" data-id="${c.id}" data-name="${c.name}" data-phone="${c.phone}"
                                style="display:flex; align-items:center; padding: 8px 12px; border-radius: 6px; font-size: 13px; text-decoration:none; color:#1a1a2e; border-bottom: 1px solid #f5f5f5; cursor:pointer;">
                                <div class="inline-flex items-center justify-center rounded-full bg-blue-600 text-white" style="width: 32px; height: 32px; font-size: 12px; font-weight: 600; margin-right:8px; flex-shrink:0;">
                                    ${c.name.charAt(0).toUpperCase()}
                                </div>
                                <div>
                                    <strong style="font-size: 13px;">${c.name}</strong>
                                    <small style="display:block; font-size: 11px; color: #6c757d;"><i class="fas fa-phone" style="margin-right:4px;"></i>${c.phone} ${c.email !== '-' ? '| ' + c.email : ''}</small>
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

        let lastSearchResults = [];

        function searchProducts(term) {
            $.get('{{ route("pos.sell.search-products") }}', { term: term }, function(res) {
                if (res.status === 'success' && res.data.length > 0) {
                    lastSearchResults = res.data;
                    $productResults.empty().show();
                    res.data.forEach((p, idx) => {
                        const vcount = p.variants.length;
                        const priceStr = (p.min_price > 0)
                            ? `৳${p.min_price.toFixed(2)}` + (p.max_price > p.min_price ? ` - ৳${p.max_price.toFixed(2)}` : '')
                            : '—';
                        $productResults.append(`
                            <button type="button" class="product-item" data-idx="${idx}"
                                style="display:flex; align-items:center; width:100%; padding: 8px 12px; border-radius: 6px; font-size: 13px; border-bottom: 1px solid #f5f5f5; text-align: left; background:#fff; cursor:pointer;">
                                <div class="flex items-center justify-center" style="width: 40px; height: 40px; overflow:hidden; margin-right:8px; flex-shrink:0; background:#f8f9fa; border-radius:6px;">
                                    ${p.image ? `<img src="${p.image}" style="width: 100%; height: 100%; object-fit: cover;">` : `<i class="fas fa-box" style="font-size: 18px; color:#6c757d;"></i>`}
                                </div>
                                <div style="flex:1; min-width: 0;">
                                    <strong style="font-size: 13px; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${decodeEntities(p.name)}</strong>
                                    <small style="font-size: 11px; color:#6c757d;">
                                        ${p.brand ? decodeEntities(p.brand) + ' ' : ''} ${p.unit ? '| ' + decodeEntities(p.unit) : ''}
                                        ${vcount > 0 ? '<span style="background:#0891b2; color:#fff; font-size:9px; padding:1px 6px; border-radius:9999px; margin-left:4px;">' + vcount + ' Variant' + (vcount > 1 ? 's' : '') + '</span>' : ''}
                                        ${p.has_discount ? '<span style="background:#dc2626; color:#fff; font-size:9px; padding:1px 6px; border-radius:9999px; margin-left:4px;">DISCOUNT</span>' : ''}
                                        ${p.campaign ? '<span style="background:#059669; color:#fff; font-size:9px; padding:1px 6px; border-radius:9999px; margin-left:4px;">CAMPAIGN</span>' : ''}
                                    </small>
                                </div>
                                <div style="text-align:right; margin-left:8px; flex-shrink:0;">
                                    <strong style="color: #2563eb; font-size: 14px;">${priceStr}</strong>
                                </div>
                            </button>
                        `);
                    });
                } else {
                    $productResults.html(`
                        <div class="text-center" style="padding:12px 0; color:#6c757d; font-size: 13px;">
                            <i class="fas fa-search" style="font-size: 24px; display: block; opacity: 0.3; margin-bottom:4px;"></i>
                            No products found for "<strong>${term}</strong>"
                        </div>
                    `).show();
                }
            });
        }

        $(document).on('click', '.product-item', function(e) {
            e.preventDefault();
            const idx = parseInt($(this).data('idx'));
            const p = lastSearchResults[idx];
            if (!p) return;
            $productResults.hide();
            openProductPicker(p);
        });

        // ====== VARIANT / COLOR PICKER ======
        let vpCurrentProduct = null;
        let vpSelectedVariant = null;
        let vpSelectedOption = null;

        function openProductPicker(p) {
            const variants = p.variants;
            if (!variants || variants.length === 0) return;

            // Fast path: only one priceable choice, in stock → add straight to cart.
            const onlyChoice = variants.length === 1 && variants[0].options.length <= 1;
            const firstStock = variants[0].stock;
            const firstStockNum = (firstStock === null || firstStock === undefined || isNaN(Number(firstStock))) ? 0 : Number(firstStock);
            const inStock = onlyChoice && firstStockNum > 0;
            if (onlyChoice && inStock) {
                const v = variants[0];
                const opt = v.options[0] || null;
                const price = opt ? opt.price : v.price;
                if (price <= 0) return;

                addToCart({
                    product_id: p.id,
                    variant_id: v.id,
                    option_id: opt ? opt.id : null,
                    product_name: decodeEntities(p.name),
                    variant_name: decodeEntities(v.name),
                    color_name: opt ? decodeEntities(opt.color_name) : '',
                    sku: (opt ? opt.sku : v.sku) || '',
                    unit_price: price,
                    original_price: opt ? opt.original_price : v.original_price,
                    discount_percent: opt ? opt.discount_percent : v.discount_percent,
                    quantity: 1
                });
                $productSearch.val('').focus();
                return;
            }

            // Multiple choices (or out of stock) → open the picker so the
            // cashier sees the disabled state and stock info clearly.
            vpCurrentProduct = p;
            vpSelectedVariant = null;
            vpSelectedOption = null;

            $('#vpImage').hide();
            $('#vpImageFallback').show();
            if (p.image) { $('#vpImage').attr('src', p.image).show(); $('#vpImageFallback').hide(); }
            $('#vpProductName').text(decodeEntities(p.name));

            renderVariantButtons();
            selectVariant(0);
            bsModal('show', '#variantPickerModal');
        }

        function renderVariantButtons() {
            $('#vpVariants').empty();
            vpCurrentProduct.variants.forEach((v, i) => {
                const vStock = (v.stock === null || v.stock === undefined || isNaN(Number(v.stock))) ? 0 : Number(v.stock);
                const out = vStock <= 0;
                $('#vpVariants').append(`
                    <button type="button" class="vp-variant-btn" data-vidx="${i}"
                        style="text-align:left; border-radius: 10px; padding: 8px 12px; min-width: 130px; border: 1.5px solid #e5e7eb; background: #fff; cursor:pointer;">
                        <span style="display:block; font-size: 13px; font-weight: 700; color: #1a1a2e;">${decodeEntities(v.name)}</span>
                        <span style="display:block; font-size: 12px; font-weight: 700; color: #2563eb;">
                            ৳${Number(v.price).toFixed(2)}
                            ${v.discount_percent > 0 ? `<span style="font-size: 10px; color: #dc2626; font-weight: 600;">-${v.discount_percent}%</span>` : ''}
                        </span>
                        <span style="display:block; font-size: 10px; color: ${out ? '#dc2626' : '#059669'};">${out ? 'Out of stock' : 'In stock'}</span>
                    </button>
                `);
            });
        }

        function selectVariant(vidx) {
            const v = vpCurrentProduct.variants[vidx];
            vpSelectedVariant = v;
            vpSelectedOption = null;

            $('.vp-variant-btn').each(function(i) {
                const active = (i === vidx);
                $(this).css({
                    borderColor: active ? '#2563eb' : '#e5e7eb',
                    background: active ? '#eff6ff' : '#fff',
                    boxShadow: active ? '0 0 0 2px rgba(37,99,235,0.15)' : 'none'
                });
            });

            renderColorButtons();
            updateVpSummary();
        }

        function renderColorButtons() {
            const v = vpSelectedVariant;
            $('#vpColors').empty();

            if (!v.options || v.options.length === 0) {
                $('#vpColorSection').hide();
                return;
            }

            $('#vpColorSection').show();
            v.options.forEach((o, i) => {
                const swatch = o.color_code
                    ? `<span style="display:inline-block; border-radius:9999px; flex-shrink:0; width:14px; height:14px; background: ${o.color_code}; border: 1px solid #cbd5e1;"></span>`
                    : `<i class="fas fa-palette" style="color:#6c757d;"></i>`;
                $('#vpColors').append(`
                    <button type="button" class="vp-color-btn" data-oidx="${i}"
                        style="border-radius: 10px; padding: 6px 12px; display: flex; align-items: center; gap: 6px; border: 1.5px solid #e5e7eb; background: #fff; font-size: 12px; font-weight: 600; color: #1a1a2e; cursor:pointer;">
                        ${swatch}<span>${decodeEntities(o.color_name)}</span>
                    </button>
                `);
            });

            selectColor(0);
        }

        function selectColor(oidx) {
            const opt = vpSelectedVariant.options[oidx];
            vpSelectedOption = opt;

            $('.vp-color-btn').each(function(i) {
                const active = (i === oidx);
                $(this).css({
                    borderColor: active ? '#2563eb' : '#e5e7eb',
                    background: active ? '#eff6ff' : '#fff',
                    boxShadow: active ? '0 0 0 2px rgba(37,99,235,0.15)' : 'none'
                });
            });

            updateVpSummary();
        }

        function updateVpSummary() {
            if (!vpSelectedVariant) { return; }

            const v = vpSelectedVariant;
            const opt = vpSelectedOption;
            const price = opt ? opt.price : v.price;
            const original = opt ? opt.original_price : v.original_price;
            const disc = opt ? opt.discount_percent : v.discount_percent;
            const campaign = opt ? opt.campaign : v.campaign;
            const label = opt ? decodeEntities(v.name) + ' / ' + decodeEntities(opt.color_name) : decodeEntities(v.name);

            $('#vpSelectedLabel').text(label);

            // Robust stock: option → variant → 0. Treat null/undefined/NaN as 0.
            const rawStock = opt ? opt.stock : v.stock;
            const stock = (rawStock === null || rawStock === undefined || isNaN(Number(rawStock))) ? 0 : Number(rawStock);
            const outOfStock = stock <= 0;

            $('#vpStock').text('Stock: ' + stock).css('color', outOfStock ? '#dc2626' : '#059669');

            $('#vpFinalPrice').text('৳' + Number(price || 0).toFixed(2));

            // Show discount badge: campaign name in brackets when discount comes from campaign
            if (disc > 0) {
                $('#vpOldPrice').text('৳' + Number(original || 0).toFixed(2)).show();
                const discLabel = campaign
                    ? ('-' + disc + '% ' + decodeEntities(campaign.name))
                    : ('-' + disc + '%');
                $('#vpDiscountBadge').text(discLabel).show();
            } else {
                $('#vpOldPrice').hide();
                $('#vpDiscountBadge').hide();
            }

            // Show campaign name in brackets when product is in a campaign
            if (campaign) {
                $('#vpCampaignBadge').text('[' + decodeEntities(campaign.name) + ']').show();
            } else {
                $('#vpCampaignBadge').hide();
            }

            // Disable Add to Cart when stock is 0 or negative.
            $('#vpAddBtn').prop('disabled', outOfStock);
            $('#vpAddBtn').css({
                background: outOfStock ? '#9ca3af' : '#2563eb',
                cursor: outOfStock ? 'not-allowed' : 'pointer',
                opacity: outOfStock ? 0.6 : 1
            });
            $('#vpAddBtn').html(outOfStock
                ? '<i class="fas fa-ban" style="margin-right:8px;"></i>Out of Stock'
                : '<i class="fas fa-cart-plus" style="margin-right:8px;"></i>Add To Cart');
        }

        $(document).on('click', '.vp-variant-btn', function() {
            selectVariant(parseInt($(this).data('vidx')));
        });

        $(document).on('click', '.vp-color-btn', function() {
            selectColor(parseInt($(this).data('oidx')));
        });

        $('#vpAddBtn').on('click', function() {
            if (!vpCurrentProduct || !vpSelectedVariant) return;

            const p = vpCurrentProduct;
            const v = vpSelectedVariant;
            const opt = vpSelectedOption;
            const price = opt ? opt.price : v.price;

            if (price <= 0) return;

            addToCart({
                product_id: p.id,
                variant_id: v.id,
                option_id: opt ? opt.id : null,
                product_name: decodeEntities(p.name),
                variant_name: decodeEntities(v.name),
                color_name: opt ? decodeEntities(opt.color_name) : '',
                sku: (opt ? opt.sku : v.sku) || '',
                unit_price: price,
                original_price: opt ? opt.original_price : v.original_price,
                discount_percent: opt ? opt.discount_percent : v.discount_percent,
                campaign: opt ? opt.campaign : v.campaign,
                quantity: 1
            });

            bsModal('hide', '#variantPickerModal');
            $productSearch.val('').focus();
        });

        // ====== CART OPERATIONS ======
        function addToCart(product) {
            const key = product.product_id + '|' + (product.variant_id || '') + '|' + (product.option_id || '');
            const existing = cart.find(item => item.key === key);
            if (existing) {
                existing.quantity += 1;
                existing.subtotal = existing.unit_price * existing.quantity;
                existing.total = existing.subtotal;
                existing.discount_amount = existing.discount_per_unit * existing.quantity;
            } else {
                const perUnitDiscount = (product.original_price || product.unit_price) - product.unit_price;
                cart.push({
                    key,
                    product_id: product.product_id,
                    variant_id: product.variant_id || null,
                    option_id: product.option_id || null,
                    product_name: product.product_name,
                    variant_name: product.variant_name || '',
                    color_name: product.color_name || '',
                    sku: product.sku || '',
                    unit_price: product.unit_price,
                    original_price: product.original_price || product.unit_price,
                    discount_per_unit: perUnitDiscount,
                    discount_percent: product.discount_percent || 0,
                    campaign: product.campaign || null,
                    quantity: 1,
                    subtotal: product.unit_price,
                    discount_amount: perUnitDiscount,
                    total: product.unit_price
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
            cart[index].discount_amount = cart[index].discount_per_unit * newQty;
            renderCart();
        }

        function renderCart() {
            const count = cart.length;
            $cartCount.text(count);
            $clearCart.toggle(count > 0);
            
            if (count === 0) {
                $cartBody.html(`
                    <tr id="emptyCartRow">
                        <td colspan="5" style="text-align:center; padding:24px 0; color:#adb5bd;">
                            <i class="fas fa-cart-plus" style="font-size: 40px; display: block; opacity: 0.3; margin-bottom:8px;"></i>
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
                            ${(item.variant_name || item.color_name) ? `<small style="display:block; font-size: 11px; color: #2563eb; font-weight: 600;">${decodeEntities(item.variant_name)}${item.color_name ? ' / ' + decodeEntities(item.color_name) : ''}</small>` : ''}
                            <small style="display:block; font-size: 11px; color:#6c757d;">${item.sku ? 'SKU: ' + decodeEntities(item.sku) : ''}</small>
                            ${item.campaign ? `<small style="display:block; font-size: 11px; color: #059669; font-weight: 600;">[${decodeEntities(item.campaign.name)}${item.discount_per_unit > 0 ? ' -' + item.discount_percent + '% off' : ''}]</small>` : ''}
                        </td>
                        <td style="padding: 10px 14px; vertical-align: middle;">
                            ${item.discount_per_unit > 0 ? `
                                <div style="font-size: 11px; color: #9ca3af; text-decoration: line-through;">৳${Number(item.original_price).toFixed(2)}</div>
                                <div style="font-size: 11px; color: #dc2626; font-weight: 600;">-৳${Number(item.discount_per_unit).toFixed(2)}${item.discount_percent > 0 ? ' (-' + item.discount_percent + '%' + (item.campaign ? ' ' + decodeEntities(item.campaign.name) : '') + ')' : (item.campaign ? ' (' + decodeEntities(item.campaign.name) + ')' : '')}</div>
                                ${item.campaign ? `<div style="font-size: 10px; color: #7c3aed; font-weight: 600; margin-top: 2px;">[${decodeEntities(item.campaign.name)}]</div>` : ''}
                            ` : (item.campaign ? `<div style="font-size: 11px; color: #7c3aed; font-weight: 600;">[${decodeEntities(item.campaign.name)}]</div>` : '')}
                            <div style="font-weight: 700; color: #2563eb; font-size: 14px;">৳${Number(item.unit_price).toFixed(2)}</div>
                        </td>
                        <td style="padding: 6px 14px; vertical-align: middle;">
                            <div style="display:flex; align-items:center; max-width:110px; border-radius:6px; overflow:hidden; border:1px solid #e5e7eb;">
                                <button class="qty-minus" data-i="${i}" style="padding: 2px 8px; font-size: 11px; background:#fff; border:none; cursor:pointer; color:#4b5563;">-</button>
                                <input type="number" class="qty-input" value="${item.quantity}" min="0.01" step="1" data-i="${i}"
                                    style="padding: 2px 4px; font-size: 13px; font-weight: 600; border:none; text-align:center; width:100%; height: 30px; outline:none;">
                                <button class="qty-plus" data-i="${i}" style="padding: 2px 8px; font-size: 11px; background:#fff; border:none; cursor:pointer; color:#4b5563;">+</button>
                            </div>
                        </td>
                        <td style="padding: 10px 14px; vertical-align: middle; text-align: right; font-weight: 700; color: #2563eb;">৳${Number(item.total).toFixed(2)}</td>
                        <td style="padding: 10px 14px; vertical-align: middle; text-align: center;">
                            <button class="remove-item" data-i="${i}" style="padding: 4px; font-size: 14px; background:none; border:none; color:#dc2626; cursor:pointer;">
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
            const manualDiscount = parseFloat($inputDiscount.val()) || 0;
            const tax = parseFloat($inputTax.val()) || 0;

            let subtotal = 0;
            let itemDiscount = 0;
            cart.forEach(item => {
                subtotal += item.total;
                itemDiscount += item.discount_amount || 0;
            });

            const totalDiscount = itemDiscount + manualDiscount;
            const total = subtotal - manualDiscount + tax;

            $cartSubtotal.text('৳' + subtotal.toFixed(2));
            $cartDiscount.text('- ৳' + totalDiscount.toFixed(2));
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
                calculateChange();
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
            const $label = $('#changeDueLabel');

            if (paymentMethod === 'cash') {
                const received = parseFloat($amountReceived.val()) || 0;
                if (received >= total) {
                    const change = received - total;
                    $label.text('Change Due');
                    $changeDue.text('৳' + change.toFixed(2));
                    $changeDue.css('color', '#059669');
                } else if (received > 0) {
                    const due = total - received;
                    $label.text('Due');
                    $changeDue.text('-৳' + due.toFixed(2));
                    $changeDue.css('color', '#dc2626');
                } else {
                    $label.text('Total Due');
                    $changeDue.text('৳' + total.toFixed(2));
                    $changeDue.css('color', '#f59e0b');
                }
            } else if (paymentMethod === 'mixed') {
                const cash = parseFloat($('#mixedCash').val()) || 0;
                const card = parseFloat($('#mixedCard').val()) || 0;
                const paid = cash + card;
                if (paid >= total) {
                    const change = paid - total;
                    $label.text('Change Due');
                    $changeDue.text('৳' + change.toFixed(2));
                    $changeDue.css('color', '#059669');
                } else if (paid > 0) {
                    const due = total - paid;
                    $label.text('Due');
                    $changeDue.text('-৳' + due.toFixed(2));
                    $changeDue.css('color', '#dc2626');
                } else {
                    $label.text('Total Due');
                    $changeDue.text('৳' + total.toFixed(2));
                    $changeDue.css('color', '#f59e0b');
                }
            } else {
                // Card method: show the amount to be charged.
                $label.text('Amount');
                $changeDue.text('৳' + total.toFixed(2));
                $changeDue.css('color', '#f59e0b');
            }
        }

        // ====== PROCESS SALE ======
        // The left cart-panel button triggers the same flow as the payment button.
        $('#processSaleBtnLeft').on('click', function() {
            $('#processSaleBtn').trigger('click');
        });

        $('#processSaleBtn').on('click', function() {
            if (cart.length === 0) {
                posToastError('Please add at least one item to the cart.');
                return;
            }

            const registerId = $('#pos_register_id').val();
            const shiftId = $('#pos_shift_id').val();
            
            if (!registerId) {
                posToastError('Please select a register.');
                return;
            }
            if (!shiftId) {
                posToastError('Please select a shift.');
                return;
            }

            const manualDiscount = parseFloat($inputDiscount.val()) || 0;
            const tax = parseFloat($inputTax.val()) || 0;
            let subtotal = 0;
            let itemDiscountTotal = 0;
            cart.forEach(item => {
                subtotal += item.total;
                itemDiscountTotal += item.discount_amount || 0;
            });
            const totalDiscount = itemDiscountTotal + manualDiscount;
            const total = subtotal - manualDiscount + tax;

            const received = paymentMethod === 'cash' ? (parseFloat($amountReceived.val()) || 0) : 0;
            const mixedCash = paymentMethod === 'mixed' ? (parseFloat($('#mixedCash').val()) || 0) : 0;
            const mixedCard = paymentMethod === 'mixed' ? (parseFloat($('#mixedCard').val()) || 0) : 0;
            
            let cashAmount = 0, cardAmount = 0, otherAmount = 0, changeAmount = 0, paymentStatus = 'paid';

            if (paymentMethod === 'cash') {
                cashAmount = received;
                changeAmount = received >= total ? received - total : 0;
                paymentStatus = received >= total ? 'paid' : (received > 0 ? 'partial' : 'pending');
            } else if (paymentMethod === 'card') {
                cardAmount = total;
                paymentStatus = 'paid';
            } else if (paymentMethod === 'mixed') {
                cashAmount = mixedCash;
                cardAmount = mixedCard;
                const paidMixed = mixedCash + mixedCard;
                changeAmount = paidMixed >= total ? paidMixed - total : 0;
                paymentStatus = paidMixed >= total ? 'paid' : (paidMixed > 0 ? 'partial' : 'pending');
            }

            const items = cart.map(item => ({
                product_id: item.product_id,
                variant_id: item.variant_id,
                option_id: item.option_id,
                product_name: item.product_name,
                variant_name: item.variant_name,
                color_name: item.color_name,
                sku: item.sku,
                unit_price: item.unit_price,
                original_price: item.original_price,
                discount_amount: item.discount_amount,
                campaign_id: item.campaign ? item.campaign.id : null,
                discount_source: item.campaign ? 'campaign' : 'variant',
                quantity: item.quantity,
                subtotal: item.subtotal,
                total: item.total
            }));

            const payload = {
                customer_id: $('#customer_id').val() || null,
                register_id: registerId,
                shift_id: shiftId,
                items: items,
                subtotal: subtotal,
                tax_amount: tax,
                discount_amount: totalDiscount,
                total: total,
                cash_amount: cashAmount,
                card_amount: cardAmount,
                other_amount: otherAmount,
                change_amount: changeAmount,
                payment_status: paymentStatus,
                notes: $('#inputNotes').val() || ''
            };

            const $btn = $('#processSaleBtn');
            const $btnLeft = $('#processSaleBtnLeft');

            if (isPosSubmitting) return;
            isPosSubmitting = true;

            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right:8px;"></i>Processing...');
            $btnLeft.prop('disabled', true).css({ opacity: 0.6, cursor: 'not-allowed' });

            const restoreButtons = function() {
                isPosSubmitting = false;
                $btn.prop('disabled', false).html('<i class="fas fa-check-circle" style="margin-right:8px;"></i>Complete Sale');
                $btnLeft.prop('disabled', false).css({ opacity: 1, cursor: 'pointer' });
            };

            $.ajax({
                url: '{{ route("pos.sell.process") }}',
                method: 'POST',
                data: JSON.stringify(payload),
                contentType: 'application/json',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function(res) {
                    restoreButtons();

                    if (res.status === 'success') {
                        // Reset the entire sale form for the next customer.
                        resetSaleForm();

                        posToastSuccess('Sale completed! Receipt #' + res.data.receipt.receipt_number + ' | Total: ৳' + parseFloat(res.data.receipt.total).toFixed(2));
                    } else {
                        posToastError(res.message || 'Error processing sale.');
                    }
                },
                error: function(xhr) {
                    restoreButtons();

                    posToastError('Error: ' + (xhr.responseJSON?.message || 'Something went wrong'));
                },
                complete: function() {
                    restoreButtons();
                }
            });
        });

        // Reset the whole sale form (cart, customer, payment) after completion.
        function resetSaleForm() {
            cart = [];
            renderCart();

            // Reset customer to walk-in.
            selectedCustomer = null;
            $('#customer_id').val('');
            $('#selectedCustomer').hide();
            $('#customerSearch').val('').prop('disabled', false);
            $('#walkinBtn').show();
            $('#custName').text('Walk-in Customer');
            $('#custPhone').text('No contact info');

            // Reset payment to Cash.
            paymentMethod = 'cash';
            $('.pay-method-btn').each(function() {
                $(this).css({ background: '#f0f0f0', color: '#6c757d' });
            });
            $('.pay-method-btn[data-method="cash"]').css({ background: '#2563eb', color: 'white' });
            $('#cashPaymentSection').show();
            $('#mixedPaymentSection').hide();

            $amountReceived.val(0);
            $('#mixedCash').val(0);
            $('#mixedCard').val(0);
            $inputDiscount.val(0);
            $inputTax.val(0);
            $inputNotes.val('');
            $('#changeDueLabel').text('Change Due');
            $('#changeDue').text('৳0.00').css('color', '#059669');

            $productSearch.val('').focus();
        }

        // Reset on new sale modal close (custom modal event)
        $('#saleSuccessModal').on('posModalHidden', function() {
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
                                    ${s.payment_status === 'paid'
                                        ? '<span style="background:#059669; color:#fff; font-size:10px; font-weight:600; padding:2px 8px; border-radius:9999px;">Paid</span>'
                                        : (s.payment_status === 'partial'
                                            ? '<span style="background:#d97706; color:#fff; font-size:10px; font-weight:600; padding:2px 8px; border-radius:9999px;">Partial</span>'
                                            : '<span style="background:#6b7280; color:#fff; font-size:10px; font-weight:600; padding:2px 8px; border-radius:9999px;">Pending</span>')}
                                </td>
                                <td style="padding: 10px 16px;">${s.created_at}</td>
                            </tr>
                        `;
                    });
                    $('#recentSalesBody').html(html);
                } else {
                    $('#recentSalesBody').html('<tr><td colspan="6" style="text-align:center; padding:16px 0; color:#6c757d;">No recent sales found.</td></tr>');
                }
                bsModal('show', '#recentSalesModal');
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
