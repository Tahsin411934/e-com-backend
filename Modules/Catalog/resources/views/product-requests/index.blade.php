<x-app-layout>
    <div class="p-4">
        <div class="flex flex-col md:flex-row md:items-end gap-4 mb-5 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <div class="flex flex-col w-full md:w-1/4">
                <x-form-select label="Status" id="filter_status" class="dt-filter-productRequestTable">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="fulfilled">Fulfilled</option>
                </x-form-select>
            </div>
            <div class="w-full md:w-auto flex items-end">
                <button id="resetFilters" class="px-4 py-2 text-sm font-medium text-white bg-gray-700 hover:bg-gray-800 rounded-lg transition active:scale-95">
                    Reset
                </button>
            </div>
        </div>

        <x-data-table id="productRequestTable" title="Product Requests" icon="fa-solid fa-clipboard-list" 
            :buttonId="'btnAddProductRequest'" :buttonText="'Add New Request'"
            :columns="['Image','Customer','Email','Phone','Product','Status','Date','Action']" 
            :ajaxUrl="route('product-requests.dataTable')" :dtColumns="[
            ['data' => 'product_image_preview', 'orderable' => false, 'searchable' => false],
            ['data' => 'customer_name'],
            ['data' => 'customer_email'],
            ['data' => 'customer_phone'],
            ['data' => 'product_name'],
            ['data' => 'status'],
            ['data' => 'created_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]" :filters="[
            'status' => '#filter_status',
        ]" :exportButtons="true" />
    </div>

    {{-- View Modal --}}
    <div id="viewModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeViewModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg font-bold text-gray-900 mb-4" id="modal-title">Product Request Details</h3>
                            <div id="viewContent" class="space-y-3 text-sm">
                                <div class="flex justify-between border-b pb-2">
                                    <span class="font-medium text-gray-600">Customer:</span>
                                    <span class="text-gray-900" id="view_name"></span>
                                </div>
                                <div class="flex justify-between border-b pb-2">
                                    <span class="font-medium text-gray-600">Email:</span>
                                    <span class="text-gray-900" id="view_email"></span>
                                </div>
                                <div class="flex justify-between border-b pb-2">
                                    <span class="font-medium text-gray-600">Phone:</span>
                                    <span class="text-gray-900" id="view_phone"></span>
                                </div>
                                <div class="flex justify-between border-b pb-2">
                                    <span class="font-medium text-gray-600">Product:</span>
                                    <span class="text-gray-900 font-semibold" id="view_product"></span>
                                </div>
                                <div class="flex justify-between border-b pb-2">
                                    <span class="font-medium text-gray-600">Description:</span>
                                    <span class="text-gray-900" id="view_description"></span>
                                </div>
                                <div class="flex justify-between border-b pb-2">
                                    <span class="font-medium text-gray-600">Quantity:</span>
                                    <span class="text-gray-900" id="view_quantity"></span>
                                </div>
                                <div class="flex justify-between border-b pb-2">
                                    <span class="font-medium text-gray-600">Expected Price:</span>
                                    <span class="text-gray-900" id="view_price"></span>
                                </div>
                                <div class="flex justify-between border-b pb-2">
                                    <span class="font-medium text-gray-600">Status:</span>
                                    <span class="text-gray-900" id="view_status"></span>
                                </div>
                                <div class="flex justify-between border-b pb-2">
                                    <span class="font-medium text-gray-600">Notes:</span>
                                    <span class="text-gray-900" id="view_notes"></span>
                                </div>
                                <div class="flex justify-between border-b pb-2">
                                    <span class="font-medium text-gray-600">Image:</span>
                                    <div id="view_image"></div>
                                </div>
                                <div class="flex justify-between border-b pb-2">
                                    <span class="font-medium text-gray-600">Date:</span>
                                    <span class="text-gray-900" id="view_date"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="closeViewModal()" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Status Modal --}}
    <div id="statusModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeStatusModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Change Request Status</h3>
                            <form id="statusForm" class="space-y-4">
                                <input type="hidden" id="status_request_id">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">New Status</label>
                                    <select id="new_status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-sm">
                                        <option value="pending">Pending</option>
                                        <option value="approved">Approved</option>
                                        <option value="rejected">Rejected</option>
                                        <option value="fulfilled">Fulfilled</option>
                                    </select>
                                </div>
                                <div class="flex justify-end gap-3 pt-2">
                                    <button type="button" onclick="closeStatusModal()" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg">Cancel</button>
                                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-primary hover:bg-primary rounded-lg">Update Status</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Drawer for Create / Edit --}}
    <x-drawer id="productRequestDrawer" overlayId="productRequestOverlay" title="Product Request" maxWidth="max-w-lg"
        submitBtnId="saveProductRequestBtn" submitBtnText="Save" submitBtnColor="bg-emerald-600 hover:bg-emerald-700"
        submitOnClick="saveProductRequestForm()">

        <form id="productRequestForm" enctype="multipart/form-data">
            <input type="hidden" name="id" id="productRequest_hid">

            <div class="space-y-5">
                {{-- Customer Info --}}
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <h4 class="font-semibold text-gray-800 text-sm mb-3 flex items-center gap-2">
                        <i class="fa fa-user text-primary"></i> Customer Information
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-form-input label="Customer Name" id="pr_customer_name" name="customer_name" required />
                        </div>
                        <div>
                            <x-form-input label="Email" id="pr_customer_email" name="customer_email" type="email" required />
                        </div>
                        <div>
                            <x-form-input label="Phone" id="pr_customer_phone" name="customer_phone" />
                        </div>
                        <div>
                            <x-form-input label="Quantity" id="pr_quantity" name="quantity" type="number" min="1" value="1" />
                        </div>
                    </div>
                </div>

                {{-- Product Info --}}
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <h4 class="font-semibold text-gray-800 text-sm mb-3 flex items-center gap-2">
                        <i class="fa fa-box text-amber-500"></i> Product Details
                    </h4>
                    <div class="space-y-4">
                        <x-form-input label="Product Name" id="pr_product_name" name="product_name" required />
                        <x-form-textarea label="Description" id="pr_product_description" name="product_description" rows="2" />
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <x-form-input label="Expected Price (৳)" id="pr_expected_price" name="expected_price" type="number" min="0" step="0.01" />
                            <div>
                                <x-form-select label="Status" id="pr_status" name="status">
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="fulfilled">Fulfilled</option>
                                </x-form-select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Product Image</label>
                            <div class="flex flex-col gap-3">
                                <div id="pr_image_preview" class="hidden mb-2">
                                    <img id="pr_image_preview_img" class="h-24 w-24 object-cover rounded-lg border-2 border-gray-200 shadow-sm" src="" alt="Preview" />
                                </div>
                                <div class="flex items-center gap-3">
                                    <button type="button" onclick="document.getElementById('pr_product_image').click()" class="px-4 py-2.5 bg-white border-2 border-dashed border-gray-300 rounded-lg text-sm text-gray-500 hover:border-primary hover:text-primary transition-colors flex items-center gap-2">
                                        <i class="fa fa-upload"></i>
                                        <span>Choose Image</span>
                                    </button>
                                    <span id="pr_image_name" class="text-xs text-gray-400">No file selected</span>
                                </div>
                                <input type="file" name="product_image" id="pr_product_image" accept="image/*" onchange="previewProductRequestImage(this)" class="hidden" />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Notes --}}
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <h4 class="font-semibold text-gray-800 text-sm mb-3 flex items-center gap-2">
                        <i class="fa fa-sticky-note text-purple-500"></i> Additional Notes
                    </h4>
                    <x-form-textarea label="Notes" id="pr_notes" name="notes" rows="2" placeholder="Any special requests..." />
                </div>
            </div>
        </form>
    </x-drawer>

    <x-confirm-delete />

    @push('scripts')
    <script>
        var dtInstance = null;

        function getTable() {
            if (!dtInstance) dtInstance = $('#productRequestTable').DataTable();
            return dtInstance;
        }

        // ========== VIEW ==========
        function viewProductRequest(id) {
            $.get("{{ route('product-requests.show', ':id') }}".replace(':id', id), function(res) {
                if (res.status === 'success') {
                    const pr = res.product_request;
                    $('#view_name').text(pr.customer_name || '-');
                    $('#view_email').text(pr.customer_email || '-');
                    $('#view_phone').text(pr.customer_phone || '-');
                    $('#view_product').text(pr.product_name || '-');
                    $('#view_description').text(pr.product_description || '-');
                    $('#view_quantity').text(pr.quantity || '-');
                    $('#view_price').text(pr.expected_price ? '৳' + parseFloat(pr.expected_price).toLocaleString('en-BD') : '-');
                    $('#view_status').html(pr.status.charAt(0).toUpperCase() + pr.status.slice(1));
                    $('#view_notes').text(pr.notes || '-');
                    $('#view_date').text(pr.created_at ? new Date(pr.created_at).toLocaleDateString() : '-');
                    
                    if (pr.product_image) {
                        const imgUrl = pr.product_image.startsWith('http') ? pr.product_image : '/storage/' + pr.product_image;
                        $('#view_image').html('<img src="' + imgUrl + '" class="h-24 w-24 object-cover rounded border" />');
                    } else {
                        $('#view_image').text('No image');
                    }

                    $('#viewModal').removeClass('hidden');
                }
            });
        }

        function closeViewModal() {
            $('#viewModal').addClass('hidden');
        }

        // ========== STATUS ==========
        function changeProductRequestStatus(id) {
            $('#status_request_id').val(id);
            $('#new_status').val('pending');
            $('#statusModal').removeClass('hidden');
        }

        function closeStatusModal() {
            $('#statusModal').addClass('hidden');
        }

        $('#statusForm').on('submit', function(e) {
            e.preventDefault();
            const id = $('#status_request_id').val();
            const status = $('#new_status').val();
            const url = "{{ route('product-requests.status', ':id') }}".replace(':id', id);

            $.post(url, { status: status, _token: '{{ csrf_token() }}' }, function(res) {
                if (res.status === 'success') {
                    Toastify({
                        text: res.message,
                        duration: 3000,
                        gravity: 'bottom',
                        position: 'right',
                        style: { background: 'linear-gradient(135deg, #16a34a, #4ade80)' },
                    }).showToast();
                    closeStatusModal();
                    getTable().ajax.reload();
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            }).fail(function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Something went wrong', 'error');
            });
        });

        // ========== DRAWER ==========
        function openProductRequestDrawer(mode) {
            if (mode === 'edit') {
                $('#drawerTitle').text('Edit Product Request');
                $('#drawerButtonText').text('Update Request');
            } else {
                resetProductRequestForm();
                $('#drawerTitle').text('Add New Product Request');
                $('#drawerButtonText').text('Save Request');
            }
            openGlobalDrawer('productRequestDrawer', 'productRequestOverlay');
        }

        function resetProductRequestForm() {
            const form = document.getElementById('productRequestForm');
            if (!form) return;
            form.reset();
            $('#productRequest_hid').val('');
            $('#pr_image_preview').addClass('hidden');
            $('#pr_image_preview_img').attr('src', '');
            $('#pr_quantity').val('1');
            $('#pr_status').val('pending');
        }

        function fillProductRequestForm(data) {
            $('#productRequest_hid').val(data.id);
            $('#pr_customer_name').val(data.customer_name || '');
            $('#pr_customer_email').val(data.customer_email || '');
            $('#pr_customer_phone').val(data.customer_phone || '');
            $('#pr_product_name').val(data.product_name || '');
            $('#pr_product_description').val(data.product_description || '');
            $('#pr_quantity').val(data.quantity || 1);
            $('#pr_expected_price').val(data.expected_price || '');
            $('#pr_notes').val(data.notes || '');
            $('#pr_status').val(data.status || 'pending');
            if (data.product_image) {
                const imgUrl = data.product_image.startsWith('http') ? data.product_image : '/storage/' + data.product_image;
                $('#pr_image_preview_img').attr('src', imgUrl);
                $('#pr_image_preview').removeClass('hidden');
            } else {
                $('#pr_image_preview').addClass('hidden');
            }
        }

        // ========== EDIT ==========
        function productRequestEdit(id) {
            Swal.fire({
                title: 'Loading...',
                text: 'Fetching details',
                allowOutsideClick: false,
                didOpen: function() { Swal.showLoading(); }
            });

            resetProductRequestForm();
            $.get("{{ route('product-requests.show', ':id') }}".replace(':id', id), function(res) {
                Swal.close();
                if (res.status === 'success') {
                    fillProductRequestForm(res.product_request);
                    openProductRequestDrawer('edit');
                } else {
                    Swal.fire('Error', res.message || 'Failed to fetch data.', 'error');
                }
            }).fail(function() {
                Swal.close();
                Swal.fire('Error', 'Server communication error.', 'error');
            });
        }

        // ========== SAVE ==========
        var isSaving = false;

        function saveProductRequestForm() {
            if (isSaving) return;

            var id = $('#productRequest_hid').val();
            var url = id 
                ? "{{ route('product-requests.update', ':id') }}".replace(':id', id)
                : "{{ route('product-requests.store') }}";
            var formData = new FormData(document.getElementById('productRequestForm'));
            if (id) formData.append('_method', 'POST');

            isSaving = true;
            $('#saveProductRequestBtn').prop('disabled', true).addClass('opacity-70 cursor-not-allowed');
            $('#drawerButtonText').text('Saving...');

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function(res) {
                    isSaving = false;
                    $('#saveProductRequestBtn').prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                    if (res.status === 'success') {
                        Toastify({
                            text: res.message,
                            duration: 3000,
                            gravity: 'bottom',
                            position: 'right',
                            style: { background: 'linear-gradient(135deg, #16a34a, #4ade80)' }
                        }).showToast();
                        closeGlobalDrawer('productRequestDrawer', 'productRequestOverlay');
                        getTable().ajax.reload();
                    } else {
                        Swal.fire('Error', res.message || 'Something went wrong', 'error');
                        $('#drawerButtonText').text(id ? 'Update Request' : 'Save Request');
                    }
                },
                error: function(xhr) {
                    isSaving = false;
                    $('#saveProductRequestBtn').prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                    $('#drawerButtonText').text(id ? 'Update Request' : 'Save Request');
                    var errorMsg = 'Server error occurred';
                    if (xhr.responseJSON && xhr.responseJSON.errors) errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                    else if (xhr.responseJSON && xhr.responseJSON.message) errorMsg = xhr.responseJSON.message;
                    Swal.fire({ icon: 'error', title: 'Validation Error', html: errorMsg });
                }
            });
        }

        // ========== DELETE ==========
        function productRequestDelete(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#4b5563',
                confirmButtonText: 'Yes, delete it!'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('product-requests.destroy', ':id') }}".replace(':id', id),
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(res) {
                            if (res.status === 'success') {
                                Toastify({
                                    text: res.message,
                                    duration: 3000,
                                    gravity: 'bottom',
                                    position: 'right',
                                    style: { background: 'linear-gradient(135deg, #dc2626, #f87171)' }
                                }).showToast();
                                getTable().ajax.reload();
                            } else {
                                Swal.fire('Error', res.message || 'Error deleting', 'error');
                            }
                        },
                        error: function() { Swal.fire('Error', 'Server communication error.', 'error'); }
                    });
                }
            });
        }

        // ========== APPROVE ==========
        function approveProductRequest(id) {
            Swal.fire({
                title: 'Approve Request?',
                text: 'This will mark the request as approved.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#059669',
                cancelButtonColor: '#4b5563',
                confirmButtonText: 'Yes, Approve!'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.post("{{ route('product-requests.status', ':id') }}".replace(':id', id), 
                        { status: 'approved', _token: '{{ csrf_token() }}' }, 
                        function(res) {
                            if (res.status === 'success') {
                                Toastify({
                                    text: 'Request approved successfully!',
                                    duration: 3000,
                                    gravity: 'bottom',
                                    position: 'right',
                                    style: { background: 'linear-gradient(135deg, #059669, #34d399)' }
                                }).showToast();
                                getTable().ajax.reload();
                            } else {
                                Swal.fire('Error', res.message, 'error');
                            }
                        }).fail(function(xhr) {
                            Swal.fire('Error', xhr.responseJSON?.message || 'Something went wrong', 'error');
                        });
                }
            });
        }

        // ========== IMAGE PREVIEW ==========
        function previewProductRequestImage(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                // Show file name
                $('#pr_image_name').text(file.name);
                // Preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#pr_image_preview_img').attr('src', e.target.result);
                    $('#pr_image_preview').removeClass('hidden');
                };
                reader.readAsDataURL(file);
            }
        }

        // ========== INIT ==========
        $(document).ready(function() {
            $('#resetFilters').on('click', function() {
                $('#filter_status').val('');
                getTable().ajax.reload();
            });

            $('#btnAddProductRequest').on('click', function() {
                openProductRequestDrawer('add');
            });
        });
    </script>
    @endpush
</x-app-layout>