<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('History') }}</h2>
    </x-slot>

    <div class="p-4">
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-4">
            <form class="flex flex-wrap items-end gap-3 mb-5" id="historyFilters">
                <div><label class="block text-xs font-semibold text-gray-500 mb-1">Action</label><select name="action" class="rounded-lg border-gray-300 text-sm"><option value="">All actions</option><option>created</option><option>updated</option><option>deleted</option><option>restored</option></select></div>
                <div><label class="block text-xs font-semibold text-gray-500 mb-1">Model</label><input name="entity_type" placeholder="Model class" class="rounded-lg border-gray-300 text-sm"></div>
                <div><label class="block text-xs font-semibold text-gray-500 mb-1">Entity ID</label><input name="entity_id" type="number" placeholder="ID" class="rounded-lg border-gray-300 text-sm w-28"></div>
                <div><label class="block text-xs font-semibold text-gray-500 mb-1">From</label><input name="from" type="date" class="rounded-lg border-gray-300 text-sm"></div>
                <div><label class="block text-xs font-semibold text-gray-500 mb-1">To</label><input name="to" type="date" class="rounded-lg border-gray-300 text-sm"></div>
                <div><label class="block text-xs font-semibold text-gray-500 mb-1">Per page</label><select name="per_page" class="rounded-lg border-gray-300 text-sm"><option>25</option><option>50</option><option>100</option></select></div>
                <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-semibold"><i class="fa fa-filter mr-1"></i> Filter</button>
            </form>
            <div class="text-sm text-red-600 mb-3" id="historyError"></div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-sm text-gray-700 dark:text-gray-200">
                    <thead><tr class="bg-gray-50 dark:bg-gray-700"><th class="px-4 py-3 text-left">Action</th><th class="px-4 py-3 text-left">Entity</th><th class="px-4 py-3 text-left">User</th><th class="px-4 py-3 text-left">Description</th><th class="px-4 py-3 text-left">Date</th><th class="px-4 py-3 text-center">Action</th></tr></thead>
                    <tbody id="historyRows"><tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">Loading...</td></tr></tbody>
                </table>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-3 mt-4 text-sm">
                <span id="historyPageInfo" class="text-gray-500"></span>
                <div class="flex gap-2">
                    <button type="button" id="historyPrev" class="px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 disabled:opacity-40 disabled:cursor-not-allowed" disabled>Prev</button>
                    <button type="button" id="historyNext" class="px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 disabled:opacity-40 disabled:cursor-not-allowed" disabled>Next</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    window.historyPage = (function () {
        const listUrl = @json(route('history.data'));
        const restoreUrl = @json(route('history.restore', ':id'));
        const rows = document.getElementById('historyRows');
        const error = document.getElementById('historyError');
        const filters = document.getElementById('historyFilters');
        const pageInfo = document.getElementById('historyPageInfo');
        const prevBtn = document.getElementById('historyPrev');
        const nextBtn = document.getElementById('historyNext');

        let currentPage = 1;
        let lastPage = 1;

        const actionBadges = {
            created: 'bg-green-100 text-green-700',
            updated: 'bg-blue-100 text-blue-700',
            deleted: 'bg-red-100 text-red-700',
            restored: 'bg-purple-100 text-purple-700',
        };

        function esc(value) {
            return String(value ?? '').replace(/[&<>"']/g, function (c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c];
            });
        }

        function emptyRow(message) {
            return '<tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">' + esc(message) + '</td></tr>';
        }

        function render(payload) {
            currentPage = payload.current_page || 1;
            lastPage = payload.last_page || 1;
            const items = payload.data || [];

            rows.innerHTML = items.length ? items.map(function (item) {
                const entity = esc((item.entity_type || '').split('\\').pop()) + ' #' + esc(item.entity_id ?? '-');
                const user = esc(item.user?.name || item.user?.email || 'System');
                const badge = actionBadges[item.action] || 'bg-gray-100 text-gray-700';
                const restoreBtn = item.action === 'deleted'
                    ? '<button type="button" class="bg-primary text-white px-3 py-1 rounded text-xs" onclick="historyPage.restore(' + Number(item.id) + ')">Restore</button>'
                    : '';
                return '<tr class="border-b border-gray-100 dark:border-gray-700">' +
                    '<td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-semibold ' + badge + '">' + esc(item.action) + '</span></td>' +
                    '<td class="px-4 py-3 whitespace-nowrap">' + entity + '</td>' +
                    '<td class="px-4 py-3">' + user + '</td>' +
                    '<td class="px-4 py-3">' + esc(item.description || '') + '</td>' +
                    '<td class="px-4 py-3 whitespace-nowrap">' + new Date(item.created_at).toLocaleString() + '</td>' +
                    '<td class="px-4 py-3 text-center">' + restoreBtn + '</td>' +
                    '</tr>';
            }).join('') : emptyRow('No history found.');

            pageInfo.textContent = 'Page ' + currentPage + ' of ' + lastPage + ' · ' + (payload.total || 0) + ' records';
            prevBtn.disabled = currentPage <= 1;
            nextBtn.disabled = currentPage >= lastPage;
        }

        async function load(event) {
            if (event) event.preventDefault();
            error.textContent = '';
            const params = new URLSearchParams(new FormData(filters));
            params.set('page', currentPage);
            try {
                const response = await fetch(listUrl + '?' + params.toString(), { headers: { 'Accept': 'application/json' } });
                const payload = await response.json();
                if (!response.ok) {
                    rows.innerHTML = emptyRow(payload.message || 'Unable to load history.');
                    pageInfo.textContent = '';
                    prevBtn.disabled = true;
                    nextBtn.disabled = true;
                    return;
                }
                render(payload);
            } catch (e) {
                rows.innerHTML = emptyRow('Unable to load history.');
            }
        }

        async function restore(id) {
            error.textContent = '';
            const confirmed = window.Swal
                ? (await Swal.fire({ title: 'Restore this record?', icon: 'question', showCancelButton: true, confirmButtonText: 'Yes, restore it!' })).isConfirmed
                : window.confirm('Restore this record?');
            if (!confirmed) return;

            try {
                const response = await fetch(restoreUrl.replace(':id', id), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                const payload = await response.json();
                if (!response.ok) {
                    error.textContent = payload.message || 'Unable to restore record.';
                    return;
                }
                if (window.Swal) Swal.fire('Restored!', payload.message || 'Record restored successfully.', 'success');
                load();
            } catch (e) {
                error.textContent = 'Unable to restore record.';
            }
        }

        filters.addEventListener('submit', function (event) {
            currentPage = 1;
            load(event);
        });
        prevBtn.addEventListener('click', function () {
            if (currentPage > 1) { currentPage--; load(); }
        });
        nextBtn.addEventListener('click', function () {
            if (currentPage < lastPage) { currentPage++; load(); }
        });

        load();
        return { load, restore };
    })();
    </script>
    @endpush
</x-app-layout>
