<x-app-layout>
    <div class="p-4">
        {{-- Filters (auto-reload the DataTable via the dt-filter-* class) --}}
        <div class="flex flex-col md:flex-row md:items-end gap-4 mb-5 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <div class="flex flex-col w-full md:w-1/5">
                <x-form-select label="Type" id="notification_filter_type" placeholder="All Types" class="dt-filter-notificationTable">
                    @foreach ($types as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </x-form-select>
            </div>
            <div class="flex flex-col w-full md:w-1/5">
                <x-form-select label="Channel" id="notification_filter_channel" placeholder="All Channels" class="dt-filter-notificationTable">
                    <option value="in_app">In App</option>
                    <option value="email">Email</option>
                    <option value="sms">SMS</option>
                </x-form-select>
            </div>
            <div class="flex flex-col w-full md:w-1/5">
                <x-form-select label="Read status" id="notification_filter_read" placeholder="All" class="dt-filter-notificationTable">
                    <option value="unread">Unread only</option>
                </x-form-select>
            </div>
            <div class="w-full md:w-auto flex items-end">
                <button id="resetNotificationFilters" class="px-4 py-2 text-sm font-medium text-white bg-gray-700 hover:bg-gray-800 rounded-lg transition active:scale-95">
                    <i class="fa fa-rotate mr-1"></i> Reset
                </button>
            </div>
        </div>

        {{-- Notifications table (reusable DataTable component) --}}
        <x-data-table id="notificationTable" title="Notifications" icon="fa-solid fa-bell"
            :buttonId="null"
            :columns="['Type','User','Subject','Details','Channel','Read','Date','Action']"
            :ajaxUrl="route('notifications.dataTable')"
            :dtColumns="[
                ['data' => 'type', 'orderable' => false, 'searchable' => false],
                ['data' => 'user_name', 'orderable' => false, 'searchable' => false],
                ['data' => 'subject', 'name' => 'subject'],
                ['data' => 'body', 'name' => 'body', 'orderable' => false],
                ['data' => 'channel', 'orderable' => false, 'searchable' => false],
                ['data' => 'read_at', 'name' => 'read_at', 'orderable' => false, 'searchable' => false],
                ['data' => 'created_at', 'name' => 'created_at'],
                ['data' => 'action', 'orderable' => false, 'searchable' => false],
            ]"
            :filters="[
                'type' => '#notification_filter_type',
                'channel' => '#notification_filter_channel',
                'read' => '#notification_filter_read',
            ]"
            :order="[[6, 'desc']]"
            :exportButtons="true" />
    </div>

    {{-- Notification drawer (reusable drawer component) --}}
    <x-drawer id="notificationDrawer" overlayId="notificationOverlay" title="Edit Notification" maxWidth="max-w-lg"
        submitBtnId="saveNotificationBtn" submitBtnText="Update Notification" submitBtnColor="bg-emerald-600 hover:bg-emerald-700"
        submitOnClick="saveNotificationForm()">

        <form id="notificationForm">
            <input type="hidden" name="notification_id" id="notification_hid">

            <div class="space-y-5">
                <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 text-sm mb-3 flex items-center gap-2">
                        <i class="fa fa-bell text-primary"></i> Notification
                    </h4>
                    <div class="space-y-4">
                        <x-form-select label="Recipient" name="user_id" id="ntf_user_id" placeholder="All Users (Broadcast)">
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </x-form-select>
                        <x-form-input label="Type" name="type" id="ntf_type" placeholder="e.g. review.created" required />
                        <x-form-select label="Channel" name="channel" id="ntf_channel" placeholder="Select channel" required>
                            <option value="in_app">In App</option>
                            <option value="email">Email</option>
                            <option value="sms">SMS</option>
                        </x-form-select>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 text-sm mb-3 flex items-center gap-2">
                        <i class="fa fa-pen text-amber-500"></i> Content
                    </h4>
                    <div class="space-y-4">
                        <x-form-input label="Subject" name="subject" id="ntf_subject" placeholder="Notification subject" required />
                        <x-form-textarea label="Body" name="body" id="ntf_body" rows="4" placeholder="Notification body" />
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                            <input type="checkbox" name="is_read" id="ntf_read" value="1"
                                class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary">
                            Mark as read
                        </label>
                    </div>
                </div>
            </div>
        </form>
    </x-drawer>

    @push('scripts')
    <script>
        function getTable() {
            return $('#notificationTable').DataTable();
        }

        // ========== EDIT (opens the reusable drawer) ==========
        window.notificationEdit = function (id) {
            $.get("{{ route('notifications.show', ':id') }}".replace(':id', id), function (res) {
                if (res.status !== 'success') {
                    Swal.fire('Error', res.message || 'Notification not found.', 'error');
                    return;
                }
                fillNotificationForm(res.data);
                $('#drawerTitle').text('Edit Notification');
                openGlobalDrawer('notificationDrawer', 'notificationOverlay');
            }).fail(function (xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Server communication error.', 'error');
            });
        };

        function fillNotificationForm(n) {
            $('#notification_hid').val(n.id);
            $('#ntf_user_id').val(n.user_id || '');
            $('#ntf_type').val(n.type || '');
            $('#ntf_channel').val(n.channel || 'in_app');
            $('#ntf_subject').val(n.subject || '');
            $('#ntf_body').val(n.body || '');
            $('#ntf_read').prop('checked', !!n.read_at);
        }

        // ========== SAVE (drawer footer) ==========
        function saveNotificationForm() {
            const id = $('#notification_hid').val();
            const isRead = $('#ntf_read').is(':checked');
            const payload = {
                user_id: $('#ntf_user_id').val() || null,
                type: $('#ntf_type').val(),
                channel: $('#ntf_channel').val(),
                subject: $('#ntf_subject').val(),
                body: $('#ntf_body').val(),
                read_at: isRead ? new Date().toISOString() : null,
                _token: '{{ csrf_token() }}',
            };

            const request = id
                ? $.post("{{ route('notifications.update', ':id') }}".replace(':id', id), { ...payload, _method: 'PUT' })
                : $.post("{{ route('notifications.store') }}", payload);

            request.done(function (res) {
                if (res.status === 'success') {
                    Toastify({
                        text: res.message || 'Saved successfully.',
                        duration: 3000,
                        gravity: 'bottom',
                        position: 'right',
                        style: { background: 'linear-gradient(135deg, #059669, #34d399)' }
                    }).showToast();
                    closeGlobalDrawer('notificationDrawer', 'notificationOverlay');
                    getTable().ajax.reload(null, false);
                } else {
                    Swal.fire('Error', res.message || 'Unable to save notification.', 'error');
                }
            }).fail(function (xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Unable to save notification.', 'error');
            });
        }

        // ========== DELETE ==========
        window.notificationDelete = function (id) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'This notification will be deleted!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#4b5563',
                confirmButtonText: 'Yes, delete it!'
            }).then(function (result) {
                if (!result.isConfirmed) return;
                $.ajax({
                    url: "{{ route('notifications.destroy', ':id') }}".replace(':id', id),
                    type: 'POST',
                    data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        if (res.status === 'success') {
                            Toastify({
                                text: res.message || 'Notification deleted.',
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
            $('#resetNotificationFilters').on('click', function () {
                $('#notification_filter_type').val('').trigger('change.select2');
                $('#notification_filter_channel').val('').trigger('change.select2');
                $('#notification_filter_read').val('').trigger('change.select2');
                getTable().ajax.reload(null, false);
            });
        });
    </script>
    @endpush
</x-app-layout>
