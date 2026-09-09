<x-app-layout>
    <div class="p-4">
        {{-- Filters (auto-reload the DataTable via the dt-filter-* class) --}}
        <div class="flex flex-col md:flex-row md:items-end gap-4 mb-5 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <div class="flex flex-col w-full md:w-1/5">
                <x-form-select label="Action" id="history_filter_action" placeholder="All Actions" class="dt-filter-historyTable">
                    <option value="created">Created</option>
                    <option value="updated">Updated</option>
                    <option value="deleted">Deleted</option>
                    <option value="restored">Restored</option>
                </x-form-select>
            </div>
            <div class="flex flex-col w-full md:w-1/4">
                <x-form-select label="Model" id="history_filter_entity_type" placeholder="All Models" class="dt-filter-historyTable">
                    @foreach ($entityTypes as $type => $label)
                        <option value="{{ $type }}">{{ $label }}</option>
                    @endforeach
                </x-form-select>
            </div>
            <div class="flex flex-col w-full md:w-1/6">
                <label class="font-semibold text-sm text-slate-700 dark:text-slate-300 block mb-1">From</label>
                <input id="history_filter_date_from" type="date" class="dt-filter-historyTable w-full border border-slate-300 dark:border-slate-600 rounded-md p-2 bg-white dark:bg-gray-700 text-slate-800 dark:text-slate-200 text-sm outline-none transition-all">
            </div>
            <div class="flex flex-col w-full md:w-1/6">
                <label class="font-semibold text-sm text-slate-700 dark:text-slate-300 block mb-1">To</label>
                <input id="history_filter_date_to" type="date" class="dt-filter-historyTable w-full border border-slate-300 dark:border-slate-600 rounded-md p-2 bg-white dark:bg-gray-700 text-slate-800 dark:text-slate-200 text-sm outline-none transition-all">
            </div>
            <div class="w-full md:w-auto flex items-end">
                <button id="resetHistoryFilters" class="px-4 py-2 text-sm font-medium text-white bg-gray-700 hover:bg-gray-800 rounded-lg transition active:scale-95">
                    <i class="fa fa-rotate mr-1"></i> Reset
                </button>
            </div>
        </div>

        {{-- History table (same reusable DataTable component as the other pages) --}}
        <x-data-table id="historyTable" title="History" icon="fas fa-history"
            :buttonId="null"
            :columns="['Type','Entity','User','Description','Date','Action']"
            :ajaxUrl="route('history.dataTable')"
            :dtColumns="[
                ['data' => 'action_badge', 'orderable' => false, 'searchable' => false],
                ['data' => 'entity_label', 'orderable' => false, 'searchable' => false],
                ['data' => 'user_name', 'orderable' => false, 'searchable' => false],
                ['data' => 'description', 'name' => 'description'],
                ['data' => 'created_at', 'name' => 'created_at'],
                ['data' => 'row_actions', 'orderable' => false, 'searchable' => false],
            ]"
            :filters="[
                'action' => '#history_filter_action',
                'entity_type' => '#history_filter_entity_type',
                'date_from' => '#history_filter_date_from',
                'date_to' => '#history_filter_date_to',
            ]"
            :order="[[4, 'desc']]"
            :exportButtons="true" />
    </div>

    {{-- Details drawer (reusable drawer component) --}}
    <x-drawer id="historyDrawer" overlayId="historyOverlay" title="History Details" maxWidth="max-w-2xl"
        submitBtnId="restoreHistoryBtn" submitBtnText="Restore Record" submitBtnColor="bg-primary hover:bg-primary"
        submitOnClick="restoreFromDrawer()">
        <div id="historyDetailsContent" class="space-y-4">
            <div class="text-sm text-gray-400">No record selected.</div>
        </div>
    </x-drawer>

    @push('scripts')
    <script>
        function getTable() {
            return $('#historyTable').DataTable();
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

        function buildDetailsHtml(row) {
            const badgeColors = {
                created: 'bg-green-100 text-green-700',
                updated: 'bg-blue-100 text-blue-700',
                deleted: 'bg-red-100 text-red-700',
                restored: 'bg-purple-100 text-purple-700',
            };
            const badge = badgeColors[row.action] || 'bg-gray-100 text-gray-700';
            const entityName = (row.entity_type || '').split('\\').pop();

            return '<div class="space-y-4">'
                + '<div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg border border-gray-200 dark:border-gray-700">'
                + detailRow('Action', '<span class="px-2 py-0.5 rounded-full text-xs font-semibold ' + badge + '">' + esc(row.action) + '</span>')
                + detailRow('Entity', '<span class="font-medium">' + esc(entityName) + '</span> <span class="text-gray-400">#' + esc(row.entity_id ?? '-') + '</span>')
                + detailRow('Entity Type', '<code class="text-xs">' + esc(row.entity_type || '') + '</code>')
                + detailRow('User', esc(row.user_name || 'System'))
                + detailRow('Date', esc(row.created_at || ''))
                + detailRow('IP Address', esc(row.ip_address || '—'))
                + '</div>'
                + '<div>'
                + '<h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2"><i class="fa fa-clock-rotate-left text-amber-500 mr-1"></i>Old Values</h4>'
                + jsonBlock(row.old_values)
                + '</div>'
                + '<div>'
                + '<h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2"><i class="fa fa-clock text-blue-500 mr-1"></i>New Values</h4>'
                + jsonBlock(row.new_values)
                + '</div>'
                + (row.user_agent
                    ? '<div><h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2"><i class="fa fa-desktop text-gray-400 mr-1"></i>User Agent</h4>'
                        + '<p class="text-xs text-gray-500 break-words">' + esc(row.user_agent) + '</p></div>'
                    : '')
                + '</div>';
        }

        let currentHistoryId = null;

        // Details: open the reusable drawer with the row's full data.
        window.openHistoryDetails = function (id, btn) {
            const row = getTable().row($(btn).closest('tr')).data();
            if (!row) return;

            currentHistoryId = id;
            $('#historyDetailsContent').html(buildDetailsHtml(row));

            // The drawer's footer action doubles as the Restore action, but only
            // for deleted records (details-only otherwise).
            const entityName = (row.entity_type || '').split('\\').pop();
            $('#drawerTitle').text('History Details — ' + entityName + ' #' + (row.entity_id ?? '-'));
            $('#restoreHistoryBtn').toggleClass('hidden', row.action !== 'deleted');

            openGlobalDrawer('historyDrawer', 'historyOverlay');
        };

        function doRestore(id) {
            Swal.fire({
                title: 'Restore this record?',
                text: 'The deleted record will be restored.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, restore it!'
            }).then(function (result) {
                if (!result.isConfirmed) return;

                $.post("{{ route('history.restore', ':id') }}".replace(':id', id),
                    { _token: '{{ csrf_token() }}' },
                    function (res) {
                        if (res.status === 'success') {
                            Toastify({
                                text: res.message || 'Record restored successfully.',
                                duration: 3000,
                                gravity: 'bottom',
                                position: 'right',
                                style: { background: 'linear-gradient(135deg, #059669, #34d399)' }
                            }).showToast();
                            closeGlobalDrawer('historyDrawer', 'historyOverlay');
                            getTable().ajax.reload(null, false);
                        } else {
                            Swal.fire('Error', res.message || 'Unable to restore record.', 'error');
                        }
                    }).fail(function (xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Unable to restore record.', 'error');
                    });
            });
        }

        // Restore straight from the row button.
        window.restoreHistory = function (id) {
            doRestore(id);
        };

        // Restore from the drawer footer button.
        function restoreFromDrawer() {
            if (currentHistoryId) doRestore(currentHistoryId);
        }

        $(document).ready(function () {
            $('#resetHistoryFilters').on('click', function () {
                $('#history_filter_action').val('').trigger('change.select2');
                $('#history_filter_entity_type').val('').trigger('change.select2');
                $('#history_filter_date_from').val('');
                $('#history_filter_date_to').val('');
                getTable().ajax.reload(null, false);
            });
        });
    </script>
    @endpush
</x-app-layout>
