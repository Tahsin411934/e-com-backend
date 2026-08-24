<x-app-layout>
    <x-confirm-delete />

    <div class="p-4">
        <x-entity-crud
            id="purchaseReturn"
            title="Purchase Returns"
            icon="fa-solid fa-undo-alt"
            :columns="['Return #','PO #','Supplier','Store','Status','Refund','Amount','Date','Action']"
            :dtColumns="[
                ['data' => 'return_number'],
                ['data' => 'purchase_order.po_number', 'orderable' => false],
                ['data' => 'supplier.name', 'orderable' => false],
                ['data' => 'store.name', 'orderable' => false],
                ['data' => 'status'],
                ['data' => 'refund_status'],
                ['data' => 'total_refund_amount'],
                ['data' => 'return_date'],
                ['data' => 'action', 'orderable' => false, 'searchable' => false],
            ]"
            ajaxUrl="{{ route('purchase-returns.dataTable') }}"
            storeUrl="{{ route('purchase-returns.store') }}"
            updateUrl="{{ route('purchase-returns.update', ':id') }}"
            showUrl="{{ route('purchase-returns.show', ':id') }}"
            destroyUrl="{{ route('purchase-returns.destroy', ':id') }}"
            drawerTitle="Purchase Return"
            dataKey="return"
            idField="return_id"
            :order="[[7, 'desc']]"
        >
            <div class="mb-4">
                <x-form-select label="Purchase Order" name="purchase_order_id" id="purchaseReturn_purchase_order_id">
                    <option value="">Select PO</option>
                    @foreach($purchaseOrders ?? [] as $po)
                        <option value="{{ $po->id }}" data-supplier-id="{{ $po->supplier_id }}" data-store-id="{{ $po->store_id }}">{{ $po->po_number }}</option>
                    @endforeach
                </x-form-select>
            </div>
            <div class="mb-4">
                <x-form-select label="Supplier" name="supplier_id" id="purchaseReturn_supplier_id">
                    <option value="">Select Supplier</option>
                    @foreach($suppliers ?? [] as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </x-form-select>
            </div>
            <div class="mb-4">
                <x-form-select label="Store" name="store_id" id="purchaseReturn_store_id">
                    <option value="">Select Store</option>
                    @foreach($stores ?? [] as $store)
                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                    @endforeach
                </x-form-select>
            </div>
            <div class="mb-4">
                <x-form-select label="Status" name="status" id="purchaseReturn_status">
                    <option value="draft">Draft</option>
                    <option value="returned">Returned</option>
                    <option value="partially_refunded">Partially Refunded</option>
                    <option value="refunded">Refunded</option>
                    <option value="cancelled">Cancelled</option>
                </x-form-select>
            </div>
            <div class="mb-4">
                <x-form-select label="Refund Status" name="refund_status" id="purchaseReturn_refund_status">
                    <option value="pending">Pending</option>
                    <option value="partial">Partial</option>
                    <option value="full">Full</option>
                </x-form-select>
            </div>
            <div class="mb-4">
                <x-form-input label="Total Refund Amount" name="total_refund_amount" id="purchaseReturn_total_refund_amount" type="number" step="0.01" placeholder="0.00" value="0" />
            </div>
            <div class="mb-4">
                <x-form-input label="Return Date" name="return_date" id="purchaseReturn_return_date" type="date" value="{{ date('Y-m-d') }}" required />
            </div>
            <div class="mb-4">
                <x-form-textarea label="Reason" name="reason" id="purchaseReturn_reason" placeholder="Reason for return" rows="2" />
            </div>
            <div class="mb-4">
                <x-form-textarea label="Notes" name="notes" id="purchaseReturn_notes" placeholder="Additional notes" rows="2" />
            </div>
        </x-entity-crud>
    </div>

    @push('scripts')
    <script>
        // Auto-fill Supplier & Store when a Purchase Order is chosen.
        $('#purchaseReturn_purchase_order_id').on('change', function() {
            var opt = $(this).find(':selected');
            if (opt.data('supplier-id')) $('#purchaseReturn_supplier_id').val(opt.data('supplier-id'));
            if (opt.data('store-id')) $('#purchaseReturn_store_id').val(opt.data('store-id'));
        });

        // When arriving from a received Purchase Order ("Return" button), open the drawer
        // with that order pre-selected (and its supplier/store auto-filled).
        $(function() {
            if (typeof window.openPurchaseReturnDrawer !== 'function') return;
            var params = new URLSearchParams(window.location.search);
            var poId = params.get('purchase_order_id');
            if (!poId) return;

            // Entity-CRUD resets the form when opening the "add" drawer, so set
            // the pre-selected values AFTER it opens.
            window.openPurchaseReturnDrawer('add');
            var opt = $('#purchaseReturn_purchase_order_id option[value="' + poId + '"]');
            if (opt.length) {
                $('#purchaseReturn_purchase_order_id').val(opt.val());
                $('#purchaseReturn_supplier_id').val(opt.data('supplier-id') || '');
                $('#purchaseReturn_store_id').val(opt.data('store-id') || '');
            }
        });

        window.fillPurchaseReturnForm = function(data) {
            $('#purchaseReturn_purchase_order_id').val(data.purchase_order_id);
            $('#purchaseReturn_supplier_id').val(data.supplier_id);
            $('#purchaseReturn_store_id').val(data.store_id);
            $('#purchaseReturn_status').val(data.status);
            $('#purchaseReturn_refund_status').val(data.refund_status);
            $('#purchaseReturn_total_refund_amount').val(data.total_refund_amount);
            $('#purchaseReturn_return_date').val(data.return_date);
            $('#purchaseReturn_reason').val(data.reason);
            $('#purchaseReturn_notes').val(data.notes);
        };
    </script>
    @endpush
</x-app-layout>