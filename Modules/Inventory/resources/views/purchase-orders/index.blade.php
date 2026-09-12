<x-app-layout>
    <div class="p-4">
        <x-data-table
            id="purchaseOrderTable"
            title="Purchase Orders"
            icon="fa-solid fa-file-invoice"
            createPermission="purchase-orders"
            :buttonId="'btnAddPurchaseOrder'"
            buttonText="Add New Purchase Order"
            :columns="['PO Number','Supplier','Store','Status','Payment','Paid','Due','Total','Created At','Action']"
            :dtColumns="[
                ['data' => 'po_number'],
                ['data' => 'supplier_name'],
                ['data' => 'store_name'],
                ['data' => 'status', 'orderable' => false, 'searchable' => false],
                ['data' => 'payment_status', 'orderable' => false, 'searchable' => false],
                ['data' => 'paid_amount', 'orderable' => false, 'searchable' => false],
                ['data' => 'due_amount', 'orderable' => false, 'searchable' => false],
                ['data' => 'total_amount'],
                ['data' => 'created_at'],
                ['data' => 'action', 'orderable' => false, 'searchable' => false],
            ]"
            ajaxUrl="{{ route('purchase-orders.dataTable') }}"
            :exportButtons="false"
            :order="[[8, 'desc']]"
        />
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('#btnAddPurchaseOrder').on('click', function() {
                window.location.href = '{{ route("purchase-orders.create") }}';
            });
        });

        function updatePoStatus(id, value, field = 'status') {
            var labels = {
                'ordered': 'Are you sure you want to mark this order as <strong>Ordered</strong>?',
                'received': 'Are you sure you want to mark this order as <strong>Received</strong>? This will update inventory stock.',
                'cancelled': 'Are you sure you want to <strong>Cancel</strong> this order?',
                'paid': 'Are you sure you want to mark this order as <strong>Paid</strong>?',
            };
            var message = labels[value] || 'Update this order?';

            Swal.fire({
                title: 'Update Purchase Order',
                html: message,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#4b5563',
                confirmButtonText: 'Yes, proceed!'
            }).then(function(result) {
                if (result.isConfirmed) {
                    var data = {
                        _token: '{{ csrf_token() }}'
                    };
                    data[field] = value;

                    $.ajax({
                        url: '{{ route('purchase-orders.update-status', ':id') }}'.replace(':id', id),
                        type: 'POST',
                        data: data,
                        success: function(res) {
                            if (res.status === 'success') {
                                Toastify({
                                    text: res.message || 'Status updated successfully!',
                                    duration: 3000,
                                    gravity: 'bottom',
                                    position: 'right',
                                    style: { background: 'linear-gradient(135deg, #16a34a, #4ade80)' }
                                }).showToast();
                                var dt = $('#purchaseOrderTable').DataTable();
                                if (dt) {
                                    dt.ajax.reload(null, false);
                                } else {
                                    location.reload();
                                }
                            } else {
                                Swal.fire('Error', res.message || 'Failed to update status', 'error');
                            }
                        },
                        error: function(xhr) {
                            var msg = 'Server error';
                            if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                            Swal.fire('Error', msg, 'error');
                        }
                    });
                }
            });
        }

        Crud.register('purchaseorder', 'status', updatePoStatus);

        $(document).on('click', '.js-purchase-order-status', function () {
            var button = $(this);
            Crud.get('purchaseorder', 'status')(button.data('id'), button.data('status'));
        });
    </script>
    @endpush
</x-app-layout>
