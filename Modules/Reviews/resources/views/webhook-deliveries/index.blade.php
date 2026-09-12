<x-app-layout>
    <div class="p-4">
        {{-- Filters (auto-reload the DataTable via the dt-filter-* class) --}}
        <div class="flex flex-col md:flex-row md:items-end gap-4 mb-5 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <div class="flex flex-col w-full md:w-1/4">
                <x-form-select label="Webhook" id="delivery_filter_webhook" placeholder="All Webhooks" class="dt-filter-webhookDeliveryTable">
                    @foreach ($webhooks as $webhook)
                        <option value="{{ $webhook->id }}">{{ $webhook->name }}</option>
                    @endforeach
                </x-form-select>
            </div>
            <div class="flex flex-col w-full md:w-1/5">
                <x-form-select label="Result" id="delivery_filter_success" placeholder="All Results" class="dt-filter-webhookDeliveryTable">
                    <option value="1">Success only</option>
                    <option value="0">Failed only</option>
                </x-form-select>
            </div>
            <div class="w-full md:w-auto flex items-end">
                <button id="resetDeliveryFilters" class="px-4 py-2 text-sm font-medium text-white bg-gray-700 hover:bg-gray-800 rounded-lg transition active:scale-95">
                    <i class="fa fa-rotate mr-1"></i> Reset
                </button>
            </div>
        </div>

        {{-- Deliveries table (reusable DataTable component) --}}
        <x-data-table id="webhookDeliveryTable" title="Webhook Deliveries" icon="fa-solid fa-satellite-dish"
            :buttonId="null"
            :columns="['Webhook','Event','Response','Attempt','Result','Delivered At','Date','Action']"
            :ajaxUrl="route('webhook-deliveries.dataTable')"
            :dtColumns="[
                ['data' => 'webhook_name', 'orderable' => false, 'searchable' => false],
                ['data' => 'event', 'orderable' => false, 'searchable' => false],
                ['data' => 'response_status', 'orderable' => false, 'searchable' => false],
                ['data' => 'attempt', 'orderable' => false, 'searchable' => false],
                ['data' => 'success', 'orderable' => false, 'searchable' => false],
                ['data' => 'delivered_at', 'orderable' => false, 'searchable' => false],
                ['data' => 'created_at', 'name' => 'created_at'],
                ['data' => 'action', 'orderable' => false, 'searchable' => false],
            ]"
            :filters="[
                'webhook_id' => '#delivery_filter_webhook',
                'success' => '#delivery_filter_success',
            ]"
            :order="[[6, 'desc']]"
            :exportButtons="true" />
    </div>

    {{-- Delivery details drawer (reusable drawer component, read-only) --}}
    <x-drawer id="webhookDeliveryDrawer" overlayId="webhookDeliveryOverlay" title="Delivery Details" maxWidth="max-w-2xl"
        submitBtnId="deliveryDrawerAction" submitBtnText="Close" submitBtnColor="bg-gray-500 hover:bg-gray-600"
        submitEntity="webhookdelivery" submitAction="close">
        <div id="deliveryDetailsContent" class="space-y-4">
            <div class="text-sm text-gray-400">No delivery selected.</div>
        </div>
    </x-drawer>

    @push('scripts')
    <script>
        function getTable() {
            return $('#webhookDeliveryTable').DataTable();
        }

        function esc(value) {
            return String(value ?? '').replace(/[&<>"']/g, function (c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c];
            });
        }

        function detailRow(label, value) {
            return '<div class="flex justify-between items-center gap-3 border-b border-gray-100 dark:border-gray-700 pb-2">'
                + '<span class="text-sm font-medium text-gray-500">' + label + '</span>'
                + '<span class="text-sm text-gray-800 dark:text-gray-200 text-right">' + value + '</span>'
                + '</div>';
        }

        function jsonBlock(value) {
            if (value === null || value === undefined) {
                return '<div class="text-sm text-gray-400">—</div>';
            }
            try {
                return '<pre class="text-xs bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 '
                    + 'rounded-lg p-3 overflow-x-auto whitespace-pre-wrap text-gray-700 dark:text-gray-300">'
                    + esc(JSON.stringify(value, null, 2)) + '</pre>';
            } catch (e) {
                return '<div class="text-sm text-gray-400">—</div>';
            }
        }

        // ========== VIEW (opens the reusable details drawer) ==========
        function webhookDeliveryView(id, btn) {
            const row = getTable().row($(btn).closest('tr')).data();
            if (!row) return;

            const statusColor = (row.response_status >= 200 && row.response_status < 300)
                ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
            const successBadge = row.success
                ? '<span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Success</span>'
                : '<span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">Failed</span>';

            $('#deliveryDetailsContent').html('<div class="space-y-4">'
                + '<div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg border border-gray-200 dark:border-gray-700">'
                + detailRow('Webhook', '<span class="font-medium">' + esc(row.webhook_name || '-') + '</span>')
                + detailRow('Event', '<span class="px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-700">' + esc(row.event || '-') + '</span>')
                + detailRow('Attempt', '#' + esc(row.attempt ?? 1))
                + detailRow('Result', successBadge)
                + detailRow('HTTP Status', row.response_status !== null && row.response_status !== undefined
                    ? '<span class="px-2 py-0.5 rounded-full text-xs font-semibold ' + statusColor + '">' + esc(row.response_status) + '</span>'
                    : '<span class="text-gray-400">—</span>')
                + detailRow('Delivered At', esc(row.delivered_at || '—'))
                + detailRow('Created At', esc(row.created_at || ''))
                + '</div>'
                + '<div>'
                + '<h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2"><i class="fa fa-paper-plane text-blue-500 mr-1"></i>Payload</h4>'
                + jsonBlock(row.payload)
                + '</div>'
                + '<div>'
                + '<h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2"><i class="fa fa-reply text-gray-400 mr-1"></i>Response</h4>'
                + jsonBlock(row.response)
                + '</div>'
                + '</div>');

            $('#drawerTitle').text('Delivery Details — ' + (row.event || ''));
            openGlobalDrawer('webhookDeliveryDrawer', 'webhookDeliveryOverlay');
        };

        // ========== DELETE ==========
        function webhookDeliveryDelete(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'This delivery log will be deleted!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#4b5563',
                confirmButtonText: 'Yes, delete it!'
            }).then(function (result) {
                if (!result.isConfirmed) return;
                $.ajax({
                    url: "{{ route('webhook-deliveries.destroy', ':id') }}".replace(':id', id),
                    type: 'POST',
                    data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        if (res.status === 'success') {
                            Toastify({
                                text: res.message || 'Delivery deleted.',
                                duration: 3000,
                                gravity: 'bottom',
                                position: 'right',
                                style: { background: 'linear-gradient(135deg, #dc2626, #f87171)' }
                            }).showToast();
                            getTable().ajax.reload(null, false);
                        } else {
                            Swal.fire('Error', res.message || 'Error deleting', 'error');
                        }
                    },
                    error: function (xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Server error', 'error');
                    }
                });
            });
        };

        Crud.register('webhookdelivery', 'view', webhookDeliveryView);
        Crud.register('webhookdelivery', 'delete', webhookDeliveryDelete);
        Crud.register('webhookdelivery', 'close', function () {
            closeGlobalDrawer('webhookDeliveryDrawer', 'webhookDeliveryOverlay');
        });

        $(document).on('click', '.js-webhook-delivery-action', function () {
            var button = $(this);
            var callback = Crud.get('webhookdelivery', button.data('action'));
            if (typeof callback === 'function') callback(button.data('id'), this);
        });

        // ========== INIT ==========
        $(document).ready(function () {
            $('#resetDeliveryFilters').on('click', function () {
                $('#delivery_filter_webhook').val('').trigger('change.select2');
                $('#delivery_filter_success').val('').trigger('change.select2');
                getTable().ajax.reload(null, false);
            });
        });
    </script>
    @endpush
</x-app-layout>
