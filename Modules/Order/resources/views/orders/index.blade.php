<x-app-layout>
    @php
        $orderStoreFilter = count($stores ?? []) ? [
            'store_id' => [
                'label' => 'Store',
                'options' => '<option value="">All Stores</option>'
                    . collect($stores)->map(fn ($s) => '<option value="'.$s->id.'">'.e($s->name).'</option>')->implode(''),
            ],
        ] : [];
        $orderColumns = ['Order #', 'Customer'];
        $orderDtColumns = [
            ['data' => 'order_number'],
            ['data' => 'user_email'],
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
            return '<div class="flex justify-between gap-4 border-b border-gray-100 py-2">'
                + '<span class="font-medium text-gray-500">' + label + '</span>'
                + '<span class="text-right text-gray-800">' + value + '</span>'
                + '</div>';
        }

        function showOrderDetails(id) {
            $.get("{{ route('orders.details', ':id') }}".replace(':id', id), function (res) {
                if (res.status !== 'success') {
                    Swal.fire('Error', res.message || 'Order not found.', 'error');
                    return;
                }

                var order = res.data;
                var delivery = order.deliveries && order.deliveries[0];
                var customerName = order.user ? order.user.name : (order.customer_name || 'Guest');
                var customerEmail = order.user ? order.user.email : 'Guest checkout';
                var deliveryBoy = delivery && delivery.delivery_boy ? delivery.delivery_boy.name : 'Not assigned';
                var deliveryBoyPhone = delivery && delivery.delivery_boy ? (delivery.delivery_boy.phone || '-') : '-';
                var items = (order.items || []).map(function (item) {
                    return '<tr class="border-b border-gray-100">'
                        + '<td class="py-2 pr-3">' + escapeHtml(item.product_name) + '<br><span class="text-xs text-gray-500">' + escapeHtml(item.variant_name) + '</span></td>'
                        + '<td class="py-2 text-center">' + item.quantity + '</td>'
                        + '<td class="py-2 text-right">' + Number(item.line_total || 0).toFixed(2) + '</td>'
                        + '</tr>';
                }).join('');

                var storeDetails = @json($canAssignStore)
                    ? detailRow('Store', escapeHtml(order.store ? order.store.name : '-'))
                    : '';
                var html = '<section><h3 class="font-semibold text-gray-800 mb-2">Order</h3>'
                    + detailRow('Order #', escapeHtml(order.order_number))
                    + detailRow('Date', escapeHtml(order.created_at))
                    + detailRow('Status', escapeHtml(order.status))
                    + detailRow('Payment', escapeHtml(order.payment_status))
                    + detailRow('Fulfillment', escapeHtml(order.fulfillment_status))
                    + detailRow('Source', escapeHtml(order.source))
                    + storeDetails
                    + '</section>'
                    + '<section><h3 class="font-semibold text-gray-800 mb-2">Customer</h3>'
                    + detailRow('Name', escapeHtml(customerName))
                    + detailRow('Email', escapeHtml(customerEmail))
                    + detailRow('Phone', escapeHtml(delivery ? delivery.delivery_phone : '-'))
                    + '</section>'
                    + '<section><h3 class="font-semibold text-gray-800 mb-2">Delivery</h3>'
                    + detailRow('Address', escapeHtml(delivery ? delivery.delivery_address : '-'))
                    + detailRow('City', escapeHtml(delivery ? delivery.delivery_city : '-'))
                    + detailRow('Delivery boy', escapeHtml(deliveryBoy))
                    + detailRow('Delivery boy phone', escapeHtml(deliveryBoyPhone))
                    + detailRow('Delivery status', escapeHtml(delivery ? delivery.status : 'Not created'))
                    + detailRow('Delivery note', escapeHtml(delivery ? delivery.delivery_notes : '-'))
                    + '</section>'
                    + '<section><h3 class="font-semibold text-gray-800 mb-2">Items</h3>'
                    + '<table class="w-full"><thead><tr class="border-b text-left text-gray-500"><th>Product</th><th class="text-center">Qty</th><th class="text-right">Total</th></tr></thead><tbody>' + items + '</tbody></table></section>'
                    + '<section><h3 class="font-semibold text-gray-800 mb-2">Amount</h3>'
                    + detailRow('Subtotal', Number(order.subtotal || 0).toFixed(2))
                    + detailRow('Delivery charge', Number(order.shipping_total || 0).toFixed(2))
                    + detailRow('Grand total', Number(order.grand_total || 0).toFixed(2))
                    + detailRow('Customer note', escapeHtml(order.customer_note || '-'))
                    + '</section>';

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
        @endif
    </script>
    @endpush
</x-app-layout>
