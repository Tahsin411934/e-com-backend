<x-app-layout>
    @push('styles')
    <style>
        .order-courier-button{display:inline-flex;align-items:center;gap:.5rem;padding:.48rem .78rem;border:1px solid #c7d2fe;border-radius:.6rem;background:#eef2ff;color:#3730a3;font-size:.75rem;font-weight:700;white-space:nowrap;transition:all .15s ease}
        .order-courier-button:hover{background:#e0e7ff;border-color:#a5b4fc;box-shadow:0 2px 7px rgb(79 70 229 / 12%)}
        .order-courier-button:disabled{opacity:.65;cursor:wait}
        .order-courier-chip{display:inline-flex;align-items:center;gap:.38rem;padding:.42rem .65rem;border:1px solid #a7f3d0;border-radius:.6rem;background:#ecfdf5;color:#047857;font-size:.7rem;font-weight:700;white-space:nowrap}
        .order-panel-section{overflow:hidden;border:1px solid #e5e7eb;border-radius:.85rem;background:#fff}
        .order-panel-heading{display:flex;align-items:center;gap:.55rem;padding:.85rem 1rem;border-bottom:1px solid #f1f5f9;color:#1f2937;font-size:.82rem;font-weight:700}
        .order-panel-body{padding:.35rem 1rem .7rem}
        .order-detail-row{display:flex;justify-content:space-between;gap:1rem;padding:.58rem 0;border-bottom:1px solid #f1f5f9}
        .order-detail-row:last-child{border-bottom:0}
        .order-detail-label{color:#6b7280;font-size:.75rem}
        .order-detail-value{color:#111827;font-size:.78rem;font-weight:600;text-align:right;overflow-wrap:anywhere}
        .order-status-pill{display:inline-flex;padding:.22rem .55rem;border-radius:999px;background:#f3f4f6;color:#374151;font-size:.68rem;font-weight:700;text-transform:capitalize}
        .order-item-table th{padding:.55rem .35rem;color:#6b7280;font-size:.7rem;font-weight:600}
        .order-item-table td{padding:.65rem .35rem;border-top:1px solid #f1f5f9;font-size:.76rem}
        .order-total-card{padding:.8rem;border-radius:.65rem;background:#f8fafc}
    </style>
    @endpush
    @php
        $orderStoreFilter = count($stores ?? []) ? [
            'store_id' => [
                'label' => 'Store',
                'options' => '<option value="">All Stores</option>'
                    . collect($stores)->map(fn ($s) => '<option value="'.$s->id.'">'.e($s->name).'</option>')->implode(''),
            ],
        ] : [];
        $orderColumns = ['Order #', 'Customer', 'Phone'];
        $orderDtColumns = [
            ['data' => 'order_number'],
            ['data' => 'customer_name'],
            ['data' => 'customer_phone'],
        ];

        if ($canAssignStore) {
            $orderColumns[] = 'Store';
            $orderDtColumns[] = ['data' => 'store_name'];
        }

        $orderColumns = [...$orderColumns, 'Status', 'Payment', 'Total', 'Created', 'Action'];
        $orderDtColumns = [...$orderDtColumns,
            ['data' => 'status'],
            ['data' => 'payment_status'],
            ['data' => 'grand_total'],
            ['data' => 'created_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ];
    @endphp
    <x-entity-crud
        id="order"
        createPermission="orders"
        title="Orders"
        icon="fa-solid fa-receipt"
        :columns="$orderColumns"
        :dtColumns="$orderDtColumns"
        ajaxUrl="{{ route('orders.dataTable') }}"
        :filters="$orderStoreFilter"
        storeUrl="{{ route('orders.store') }}"
        updateUrl="{{ route('orders.update', ':id') }}"
        showUrl="{{ route('orders.show', ':id') }}"
        destroyUrl="{{ route('orders.destroy', ':id') }}"
        drawerTitle="Order"
        dataKey="data"
        idField="order_id"
        :order="[[0, 'desc']]"
    >
        <div class="mb-4">
            <x-form-input label="Order Number" name="order_number" id="order_order_number" placeholder="ORD-XXXXX" required />
        </div>
        <div class="mb-4">
            <x-form-input label="Customer Note" name="customer_note" id="order_customer_note" placeholder="Customer notes" />
        </div>
        <div class="grid grid-cols-3 gap-4 mb-4">
            <x-form-select label="Source" name="source" id="order_source">
                <option value="web">Web</option>
                <option value="mobile">Mobile</option>
                <option value="pos">POS</option>
                <option value="admin">Admin</option>
                <option value="marketplace">Marketplace</option>
            </x-form-select>
            <x-form-select label="Status" name="status" id="order_status">
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="processing">Processing</option>
                <option value="ready">Ready</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
                <option value="refunded">Refunded</option>
            </x-form-select>
            <x-form-select label="Payment Status" name="payment_status" id="order_payment_status">
                <option value="unpaid">Unpaid</option>
                <option value="authorized">Authorized</option>
                <option value="paid">Paid</option>
                <option value="partially_refunded">Partially Refunded</option>
                <option value="refunded">Refunded</option>
                <option value="failed">Failed</option>
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-select label="Fulfillment Status" name="fulfillment_status" id="order_fulfillment_status">
                <option value="unfulfilled">Unfulfilled</option>
                <option value="partial">Partial</option>
                <option value="fulfilled">Fulfilled</option>
                <option value="returned">Returned</option>
            </x-form-select>
        </div>
    </x-entity-crud>

    @if ($canViewDetails)
        <x-drawer id="orderDetailsDrawer" overlayId="orderDetailsOverlay" title="Order Details" maxWidth="max-w-3xl"
            submitBtnId="closeOrderDetailsBtn" submitBtnText="Close" submitBtnColor="bg-gray-600 hover:bg-gray-700"
            submitEntity="orderdetails" submitAction="close">
            <div id="orderDetailsContent" class="space-y-5 text-sm">
                <p class="text-gray-400">Select an order to view its details.</p>
            </div>
        </x-drawer>
    @endif

    @push('scripts')
    <script>
Crud.register('order', 'fill', function (data) {
            $('#order_order_number').val(data.order_number);
            $('#order_customer_note').val(data.customer_note || '');
            $('#order_source').val(data.source);
            $('#order_status').val(data.status);
            $('#order_payment_status').val(data.payment_status);
            $('#order_fulfillment_status').val(data.fulfillment_status);
        });

        @if ($canViewDetails)
        function escapeHtml(value) {
            return $('<div>').text(value ?? '-').html();
        }

        function detailRow(label, value) {
            return '<div class="order-detail-row">'
                + '<span class="order-detail-label">' + label + '</span>'
                + '<span class="order-detail-value">' + value + '</span>'
                + '</div>';
        }

        function panelSection(icon, title, content) {
            return '<section class="order-panel-section">'
                + '<h3 class="order-panel-heading"><i class="' + icon + ' text-indigo-500"></i>' + title + '</h3>'
                + '<div class="order-panel-body">' + content + '</div></section>';
        }

        function statusPill(value) {
            return '<span class="order-status-pill">' + escapeHtml(String(value || '-').replaceAll('_', ' ')) + '</span>';
        }

        function showOrderDetails(id) {
            $.get("{{ route('orders.details', ':id') }}".replace(':id', id), function (res) {
                if (res.status !== 'success') {
                    Swal.fire('Error', res.message || 'Order not found.', 'error');
                    return;
                }

                var order = res.data;
                var delivery = order.deliveries && order.deliveries[0];
                var customerName = order.user && order.user.name ? order.user.name : (order.customer_name || 'Guest');
                var customerEmail = order.user && order.user.email ? order.user.email : 'Guest checkout';
                var customerPhone = order.user && order.user.phone ? order.user.phone : (delivery ? delivery.delivery_phone : '-');
                var deliveryBoy = delivery && delivery.delivery_boy ? delivery.delivery_boy.name : 'Not assigned';
                var deliveryBoyPhone = delivery && delivery.delivery_boy ? (delivery.delivery_boy.phone || '-') : '-';
                var courierShipment = (order.shipments || []).find(function (shipment) { return shipment.carrier_name === 'Steadfast'; });
                var items = (order.items || []).map(function (item) {
                    return '<tr>'
                        + '<td class="pr-3"><span class="font-semibold text-gray-800">' + escapeHtml(item.product_name) + '</span><br><span class="text-xs text-gray-500">' + escapeHtml(item.variant_name) + '</span></td>'
                        + '<td class="text-center text-gray-600">' + item.quantity + '</td>'
                        + '<td class="text-right font-semibold text-gray-800">' + Number(item.line_total || 0).toFixed(2) + '</td>'
                        + '</tr>';
                }).join('');

                var storeDetails = @json($canAssignStore)
                    ? detailRow('Store', escapeHtml(order.store ? order.store.name : '-'))
                    : '';
                var orderInfo = detailRow('Order number', escapeHtml(order.order_number))
                    + detailRow('Placed on', escapeHtml(order.created_at))
                    + detailRow('Order status', statusPill(order.status))
                    + detailRow('Payment', statusPill(order.payment_status))
                    + detailRow('Fulfillment', statusPill(order.fulfillment_status))
                    + detailRow('Source', escapeHtml(order.source)) + storeDetails;
                var customerInfo = detailRow('Customer', escapeHtml(customerName))
                    + detailRow('Email', escapeHtml(customerEmail))
                    + detailRow('Phone', escapeHtml(customerPhone));
                var deliveryInfo = detailRow('Address', escapeHtml(delivery ? delivery.delivery_address : '-'))
                    + detailRow('City', escapeHtml(delivery ? delivery.delivery_city : '-'))
                    + detailRow('Delivery partner', escapeHtml(deliveryBoy))
                    + detailRow('Partner phone', escapeHtml(deliveryBoyPhone))
                    + detailRow('Local delivery status', statusPill(delivery ? delivery.status : 'Not created'))
                    + detailRow('Delivery note', escapeHtml(delivery ? delivery.delivery_notes : '-'));
                var courierInfo = courierShipment
                    ? detailRow('Carrier', escapeHtml(courierShipment.carrier_name))
                        + detailRow('Tracking code', '<span class="font-mono">' + escapeHtml(courierShipment.tracking_number) + '</span>')
                        + detailRow('Courier status', statusPill(courierShipment.status))
                    : '<div class="py-3 text-xs text-gray-500">This order has not been sent to Steadfast yet. Use the “Send to Steadfast” action from the order list.</div>';
                var itemTable = '<div class="overflow-x-auto"><table class="order-item-table w-full"><thead><tr class="text-left"><th>Product</th><th class="text-center">Qty</th><th class="text-right">Total</th></tr></thead><tbody>' + items + '</tbody></table></div>';
                var totals = '<div class="order-total-card space-y-1">'
                    + detailRow('Subtotal', Number(order.subtotal || 0).toFixed(2))
                    + detailRow('Delivery charge', Number(order.shipping_total || 0).toFixed(2))
                    + '<div class="flex justify-between gap-4 pt-2"><span class="font-bold text-gray-800">Grand total</span><span class="font-bold text-indigo-700">৳' + Number(order.grand_total || 0).toFixed(2) + '</span></div>'
                    + '</div>' + detailRow('Customer note', escapeHtml(order.customer_note || '-'));
                var html = '<div class="space-y-3">'
                    + panelSection('fa-solid fa-receipt', 'Order overview', orderInfo)
                    + panelSection('fa-solid fa-user', 'Customer', customerInfo)
                    + panelSection('fa-solid fa-location-dot', 'Delivery details', deliveryInfo)
                    + panelSection('fa-solid fa-truck-fast', 'Steadfast shipment', courierInfo)
                    + panelSection('fa-solid fa-box-open', 'Items (' + (order.items || []).length + ')', itemTable)
                    + panelSection('fa-solid fa-calculator', 'Order total', totals)
                    + '</div>';

                $('#orderDetailsContent').html(html);
                openGlobalDrawer('orderDetailsDrawer', 'orderDetailsOverlay');
            }).fail(function () {
                Swal.fire('Error', 'Unable to load order details.', 'error');
            });
        }

        Crud.register('orderdetails', 'close', function () {
            closeGlobalDrawer('orderDetailsDrawer', 'orderDetailsOverlay');
        });

        $(document).on('click', '.js-order-details', function () {
            showOrderDetails($(this).data('order-id'));
        });

        $(document).on('click', '.js-order-steadfast:not(:disabled)', function () {
            var button = $(this);
            var orderId = button.data('order-id');
            Swal.fire({
                title: 'Create Steadfast parcel?',
                text: 'This will send this order to Steadfast for delivery.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Create parcel',
            }).then(function (result) {
                if (!result.isConfirmed) return;
                button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i><span>Sending…</span>');
                $.ajax({
                    url: "{{ route('orders.steadfast', ':id') }}".replace(':id', orderId),
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        if (res.status === 'success') {
                            Swal.fire('Created', res.message, 'success');
                            button.removeClass('order-courier-button').addClass('order-courier-chip').html('<i class="fa fa-check-circle"></i><span>Steadfast · ' + escapeHtml(res.data.shipment.tracking_number) + '</span>');
                            if ($('#orderDetailsDrawer').hasClass('open')) showOrderDetails(orderId);
                        } else {
                            button.prop('disabled', false);
                            Swal.fire('Error', res.message || 'Could not create parcel.', 'error');
                        }
                    },
                    error: function (xhr) {
                        button.prop('disabled', false).removeClass('order-courier-chip').addClass('order-courier-button').html('<i class="fa fa-truck"></i><span>Send to Steadfast</span>');
                        Swal.fire('Error', xhr.responseJSON?.message || 'Could not create parcel.', 'error');
                    }
                });
            });
        });
        @endif
    </script>
    @endpush
</x-app-layout>
