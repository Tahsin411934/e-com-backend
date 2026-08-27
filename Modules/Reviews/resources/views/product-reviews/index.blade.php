<x-app-layout>
    <div class="p-4">
        {{-- Filters (auto-reload the DataTable via the dt-filter-* class) --}}
        <div class="flex flex-col md:flex-row md:items-end gap-4 mb-5 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <div class="flex flex-col w-full md:w-1/5">
                <x-form-select label="Status" id="review_filter_status" placeholder="All Status" class="dt-filter-productReviewTable">
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </x-form-select>
            </div>
            <div class="flex flex-col w-full md:w-1/4">
                <x-form-select label="Product" id="review_filter_product" placeholder="All Products" class="dt-filter-productReviewTable">
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </x-form-select>
            </div>
            <div class="w-full md:w-auto flex items-end">
                <button id="resetReviewFilters" class="px-4 py-2 text-sm font-medium text-white bg-gray-700 hover:bg-gray-800 rounded-lg transition active:scale-95">
                    <i class="fa fa-rotate mr-1"></i> Reset
                </button>
            </div>
        </div>

        {{-- Reviews table (reusable DataTable component) --}}
        <x-data-table id="productReviewTable" title="Product Reviews" icon="fa-solid fa-star"
            :buttonId="null"
            :columns="['Product','Customer','Rating','Status','Verified','Date','Action']"
            :ajaxUrl="route('product-reviews.dataTable')"
            :dtColumns="[
                ['data' => 'product_name', 'orderable' => false, 'searchable' => false],
                ['data' => 'user_name', 'orderable' => false, 'searchable' => false],
                ['data' => 'rating', 'orderable' => false, 'searchable' => false],
                ['data' => 'status', 'orderable' => false, 'searchable' => false],
                ['data' => 'is_verified_purchase', 'orderable' => false, 'searchable' => false],
                ['data' => 'created_at', 'name' => 'created_at'],
                ['data' => 'action', 'orderable' => false, 'searchable' => false],
            ]"
            :filters="[
                'status' => '#review_filter_status',
                'product_id' => '#review_filter_product',
            ]"
            :order="[[5, 'desc']]"
            :exportButtons="true" />
    </div>

    {{-- Review drawer (reusable drawer component) --}}
    <x-drawer id="productReviewDrawer" overlayId="productReviewOverlay" title="Edit Review" maxWidth="max-w-lg"
        submitBtnId="saveReviewBtn" submitBtnText="Update Review" submitBtnColor="bg-emerald-600 hover:bg-emerald-700"
        submitOnClick="saveReviewForm()">

        <form id="productReviewForm">
            <input type="hidden" name="review_id" id="productReview_hid">

            <div class="space-y-5">
                {{-- Customer info (read-only) --}}
                <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 text-sm mb-2 flex items-center gap-2">
                        <i class="fa fa-user text-primary"></i> Customer
                    </h4>
                    <p id="prv_user_name" class="text-sm text-gray-600 dark:text-gray-300">-</p>
                </div>

                {{-- Review details --}}
                <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 text-sm mb-3 flex items-center gap-2">
                        <i class="fa fa-star text-amber-500"></i> Review Details
                    </h4>
                    <div class="space-y-4">
                        <x-form-select label="Product" name="product_id" id="prv_product_id" placeholder="Select a product" required>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </x-form-select>
                        <x-form-select label="Rating" name="rating" id="prv_rating" placeholder="Select rating" required>
                            @for ($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}">{{ str_repeat('★', $i) }} ({{ $i }})</option>
                            @endfor
                        </x-form-select>
                        <x-form-input label="Title" name="title" id="prv_title" placeholder="Review title" />
                        <x-form-textarea label="Review" name="body" id="prv_body" rows="4" placeholder="Review body" />
                    </div>
                </div>

                {{-- Moderation --}}
                <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 text-sm mb-3 flex items-center gap-2">
                        <i class="fa fa-shield-halved text-primary"></i> Moderation
                    </h4>
                    <div class="space-y-4">
                        <x-form-select label="Status" name="status" id="prv_status" placeholder="Select status" required>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </x-form-select>
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                            <input type="checkbox" name="is_verified_purchase" id="prv_verified" value="1"
                                class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary">
                            Verified purchase
                        </label>
                    </div>
                </div>
            </div>
        </form>
    </x-drawer>

    @push('scripts')
    <script>
        function getTable() {
            return $('#productReviewTable').DataTable();
        }

        // ========== EDIT (opens the reusable drawer) ==========
        window.productReviewEdit = function (id) {
            $.get("{{ route('product-reviews.show', ':id') }}".replace(':id', id), function (res) {
                if (res.status !== 'success') {
                    Swal.fire('Error', res.message || 'Review not found.', 'error');
                    return;
                }
                fillReviewForm(res.review);
                $('#drawerTitle').text('Edit Review');
                $('#drawerButtonText').text('Update Review');
                openGlobalDrawer('productReviewDrawer', 'productReviewOverlay');
            }).fail(function (xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Server communication error.', 'error');
            });
        };

        function fillReviewForm(review) {
            $('#productReview_hid').val(review.id);
            $('#prv_product_id').val(review.product_id);
            $('#prv_rating').val(review.rating);
            $('#prv_title').val(review.title || '');
            $('#prv_body').val(review.body || '');
            $('#prv_status').val(review.status || 'pending');
            $('#prv_verified').prop('checked', !!review.is_verified_purchase);
            $('#prv_user_name').text(review.user?.name || '-');
        }

        // ========== SAVE (drawer footer) ==========
        function saveReviewForm() {
            const id = $('#productReview_hid').val();
            const payload = {
                product_id: $('#prv_product_id').val(),
                rating: $('#prv_rating').val(),
                title: $('#prv_title').val(),
                body: $('#prv_body').val(),
                status: $('#prv_status').val(),
                is_verified_purchase: $('#prv_verified').is(':checked') ? 1 : 0,
                _token: '{{ csrf_token() }}',
            };

            const request = id
                ? $.post("{{ route('product-reviews.update', ':id') }}".replace(':id', id), { ...payload, _method: 'PUT' })
                : $.post("{{ route('product-reviews.store') }}", payload);

            request.done(function (res) {
                if (res.status === 'success') {
                    Toastify({
                        text: res.message || 'Saved successfully.',
                        duration: 3000,
                        gravity: 'bottom',
                        position: 'right',
                        style: { background: 'linear-gradient(135deg, #059669, #34d399)' }
                    }).showToast();
                    closeGlobalDrawer('productReviewDrawer', 'productReviewOverlay');
                    getTable().ajax.reload(null, false);
                } else {
                    Swal.fire('Error', res.message || 'Unable to save review.', 'error');
                }
            }).fail(function (xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Unable to save review.', 'error');
            });
        }

        // ========== DELETE ==========
        window.productReviewDelete = function (id) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'This review will be deleted!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#4b5563',
                confirmButtonText: 'Yes, delete it!'
            }).then(function (result) {
                if (!result.isConfirmed) return;
                $.ajax({
                    url: "{{ route('product-reviews.destroy', ':id') }}".replace(':id', id),
                    type: 'POST',
                    data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        if (res.status === 'success') {
                            Toastify({
                                text: res.message || 'Review deleted.',
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

        // ========== APPROVE ==========
        window.productReviewApprove = function (id) {
            $.post("{{ route('product-reviews.approve', ':id') }}".replace(':id', id),
                { _token: '{{ csrf_token() }}' },
                function (res) {
                    if (res.status === 'success') {
                        Toastify({
                            text: res.message || 'Review approved.',
                            duration: 3000,
                            gravity: 'bottom',
                            position: 'right',
                            style: { background: 'linear-gradient(135deg, #059669, #34d399)' }
                        }).showToast();
                        getTable().ajax.reload(null, false);
                    } else {
                        Swal.fire('Error', res.message || 'Unable to approve review.', 'error');
                    }
                }).fail(function (xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Server error', 'error');
                });
        };

        // ========== INIT ==========
        $(document).ready(function () {
            $('#resetReviewFilters').on('click', function () {
                $('#review_filter_status').val('');
                $('#review_filter_product').val('');
                getTable().ajax.reload(null, false);
            });
        });
    </script>
    @endpush
</x-app-layout>
