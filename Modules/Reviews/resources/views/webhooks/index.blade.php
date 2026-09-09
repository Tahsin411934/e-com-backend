<x-app-layout>
    <div class="p-4">
        {{-- Filters (auto-reload the DataTable via the dt-filter-* class) --}}
        <div class="flex flex-col md:flex-row md:items-end gap-4 mb-5 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <div class="flex flex-col w-full md:w-1/5">
                <x-form-select label="Status" id="webhook_filter_status" placeholder="All Status" class="dt-filter-webhookTable">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="failed">Failed</option>
                </x-form-select>
            </div>
            <div class="w-full md:w-auto flex items-end">
                <button id="resetWebhookFilters" class="px-4 py-2 text-sm font-medium text-white bg-gray-700 hover:bg-gray-800 rounded-lg transition active:scale-95">
                    <i class="fa fa-rotate mr-1"></i> Reset
                </button>
            </div>
        </div>

        {{-- Webhooks table (reusable DataTable component) --}}
        <x-data-table id="webhookTable" title="Webhooks" icon="fa-solid fa-plug"
            :buttonId="'btnAddWebhook'" :buttonText="'Add New Webhook'"
            :columns="['Name','Endpoint URL','Events','Status','Retries','Timeout','Date','Action']"
            :ajaxUrl="route('webhooks.dataTable')"
            :dtColumns="[
                ['data' => 'name', 'name' => 'name'],
                ['data' => 'url', 'name' => 'url', 'orderable' => false],
                ['data' => 'events', 'orderable' => false, 'searchable' => false],
                ['data' => 'status', 'name' => 'status', 'orderable' => false, 'searchable' => false],
                ['data' => 'retry_count', 'name' => 'retry_count'],
                ['data' => 'timeout_seconds', 'name' => 'timeout_seconds'],
                ['data' => 'created_at', 'name' => 'created_at'],
                ['data' => 'action', 'orderable' => false, 'searchable' => false],
            ]"
            :filters="[
                'status' => '#webhook_filter_status',
            ]"
            :order="[[6, 'desc']]"
            :exportButtons="true" />
    </div>

    {{-- Webhook drawer (reusable drawer component) --}}
    <x-drawer id="webhookDrawer" overlayId="webhookOverlay" title="Add New Webhook" maxWidth="max-w-lg"
        submitBtnId="saveWebhookBtn" submitBtnText="Save Webhook" submitBtnColor="bg-emerald-600 hover:bg-emerald-700"
        submitOnClick="saveWebhookForm()">

        <form id="webhookForm">
            <input type="hidden" name="webhook_id" id="webhook_hid">

            <div class="space-y-5">
                <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 text-sm mb-3 flex items-center gap-2">
                        <i class="fa fa-plug text-primary"></i> Endpoint
                    </h4>
                    <div class="space-y-4">
                        <x-form-input label="Name" name="name" id="wh_name" placeholder="e.g. Slack Notifications" required />
                        <x-form-input label="Endpoint URL" name="url" id="wh_url" type="url" placeholder="https://example.com/webhook" required />
                        <x-form-input label="Secret (optional)" name="secret" id="wh_secret" placeholder="Used for HMAC signature" />
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 text-sm mb-3 flex items-center gap-2">
                        <i class="fa fa-bolt text-amber-500"></i> Behaviour
                    </h4>
                    <div class="space-y-4">
                        <div>
                            <label class="font-semibold text-sm text-slate-700 dark:text-slate-300 block mb-1">
                                Events
                                <span class="font-normal text-xs text-gray-400">(comma separated, empty = all events)</span>
                            </label>
                            <input name="events" id="wh_events" placeholder="review.created, review.approved"
                                class="w-full border border-slate-300 dark:border-slate-600 rounded-md p-2 bg-white dark:bg-gray-700 text-slate-800 dark:text-slate-200 text-sm outline-none transition-all placeholder:text-slate-400" />
                        </div>
                        <x-form-select label="Status" name="status" id="wh_status" placeholder="Select status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="failed">Failed</option>
                        </x-form-select>
                        <div class="grid grid-cols-2 gap-4">
                            <x-form-input label="Retries" name="retry_count" id="wh_retry" type="number" min="0" value="3" />
                            <x-form-input label="Timeout (seconds)" name="timeout_seconds" id="wh_timeout" type="number" min="1" value="30" />
                        </div>
                        <x-form-textarea label="Description" name="description" id="wh_description" rows="2" placeholder="What is this webhook for?" />
                    </div>
                </div>
            </div>
        </form>
    </x-drawer>

    @push('scripts')
    <script>
        function getTable() {
            return $('#webhookTable').DataTable();
        }

        // ========== EDIT (opens the reusable drawer) ==========
        window.webhookEdit = function (id) {
            $.get("{{ route('webhooks.show', ':id') }}".replace(':id', id), function (res) {
                if (res.status !== 'success') {
                    Swal.fire('Error', res.message || 'Webhook not found.', 'error');
                    return;
                }
                fillWebhookForm(res.data);
                $('#drawerTitle').text('Edit Webhook');
                $('#drawerButtonText').text('Update Webhook');
                openGlobalDrawer('webhookDrawer', 'webhookOverlay');
            }).fail(function (xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Server communication error.', 'error');
            });
        };

        function fillWebhookForm(webhook) {
            $('#webhook_hid').val(webhook.id);
            $('#wh_name').val(webhook.name || '');
            $('#wh_url').val(webhook.url || '');
            $('#wh_secret').val(webhook.secret || '');
            $('#wh_events').val((webhook.events || []).join(', '));
            $('#wh_status').val(webhook.status || 'active');
            $('#wh_retry').val(webhook.retry_count ?? 3);
            $('#wh_timeout').val(webhook.timeout_seconds ?? 30);
            $('#wh_description').val(webhook.description || '');
        }

        function resetWebhookForm() {
            const form = document.getElementById('webhookForm');
            if (form) form.reset();
            $('#webhook_hid').val('');
        }

        // ========== SAVE (drawer footer) ==========
        function saveWebhookForm() {
            const id = $('#webhook_hid').val();
            const payload = {
                name: $('#wh_name').val(),
                url: $('#wh_url').val(),
                secret: $('#wh_secret').val() || null,
                events: $('#wh_events').val().split(',').map(function (s) { return s.trim(); }).filter(Boolean),
                status: $('#wh_status').val(),
                retry_count: $('#wh_retry').val(),
                timeout_seconds: $('#wh_timeout').val(),
                description: $('#wh_description').val() || null,
                _token: '{{ csrf_token() }}',
            };

            const request = id
                ? $.post("{{ route('webhooks.update', ':id') }}".replace(':id', id), { ...payload, _method: 'PUT' })
                : $.post("{{ route('webhooks.store') }}", payload);

            request.done(function (res) {
                if (res.status === 'success') {
                    Toastify({
                        text: res.message || 'Saved successfully.',
                        duration: 3000,
                        gravity: 'bottom',
                        position: 'right',
                        style: { background: 'linear-gradient(135deg, #059669, #34d399)' }
                    }).showToast();
                    closeGlobalDrawer('webhookDrawer', 'webhookOverlay');
                    getTable().ajax.reload(null, false);
                } else {
                    Swal.fire('Error', res.message || 'Unable to save webhook.', 'error');
                }
            }).fail(function (xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Unable to save webhook.', 'error');
            });
        }

        // ========== DELETE ==========
        window.webhookDelete = function (id) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'This webhook and its delivery logs will be deleted!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#4b5563',
                confirmButtonText: 'Yes, delete it!'
            }).then(function (result) {
                if (!result.isConfirmed) return;
                $.ajax({
                    url: "{{ route('webhooks.destroy', ':id') }}".replace(':id', id),
                    type: 'POST',
                    data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        if (res.status === 'success') {
                            Toastify({
                                text: res.message || 'Webhook deleted.',
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

        // ========== INIT ==========
        $(document).ready(function () {
            $('#btnAddWebhook').on('click', function () {
                resetWebhookForm();
                $('#drawerTitle').text('Add New Webhook');
                $('#drawerButtonText').text('Save Webhook');
                openGlobalDrawer('webhookDrawer', 'webhookOverlay');
            });

            $('#resetWebhookFilters').on('click', function () {
                $('#webhook_filter_status').val('').trigger('change.select2');
                getTable().ajax.reload(null, false);
            });
        });
    </script>
    @endpush
</x-app-layout>
