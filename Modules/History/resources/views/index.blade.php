<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('History') }}</h2>
    </x-slot>

    <div class="p-4">
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-4">
            <form class="flex flex-wrap items-end gap-3 mb-5" id="historyFilters">
                <div><label class="block text-xs font-semibold text-gray-500 mb-1">Action</label><select name="action" class="rounded-lg border-gray-300 text-sm"><option value="">All actions</option><option>created</option><option>updated</option><option>deleted</option><option>restored</option></select></div>
                <div><label class="block text-xs font-semibold text-gray-500 mb-1">Model</label><input name="entity_type" placeholder="Model class" class="rounded-lg border-gray-300 text-sm"></div>
                <div><label class="block text-xs font-semibold text-gray-500 mb-1">From</label><input name="from" type="date" class="rounded-lg border-gray-300 text-sm"></div>
                <div><label class="block text-xs font-semibold text-gray-500 mb-1">To</label><input name="to" type="date" class="rounded-lg border-gray-300 text-sm"></div>
                <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-semibold"><i class="fa fa-filter mr-1"></i> Filter</button>
            </form>
            <div class="text-sm text-red-600 mb-3" id="historyError"></div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-sm text-gray-700 dark:text-gray-200">
                    <thead><tr class="bg-gray-50 dark:bg-gray-700"><th class="px-4 py-3 text-left">Action</th><th class="px-4 py-3 text-left">Entity</th><th class="px-4 py-3 text-left">User</th><th class="px-4 py-3 text-left">Description</th><th class="px-4 py-3 text-left">Date</th><th class="px-4 py-3 text-center">Action</th></tr></thead>
                    <tbody id="historyRows"><tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">Loading...</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    window.historyPage = (function () {
        const rows = document.getElementById('historyRows');
        const error = document.getElementById('historyError');
        const filters = document.getElementById('historyFilters');

        async function load(event) {
            event?.preventDefault();
            const params = new URLSearchParams(new FormData(filters));
            const response = await fetch(`/api/v1/histories?${params}`);
            const payload = await response.json();
            if (!response.ok) { error.textContent = payload.message || 'Unable to load history.'; return; }
            rows.innerHTML = payload.data.length ? payload.data.map(item => `
                <tr><td>${item.action}</td><td>${item.entity_type.split('\\').pop()} #${item.entity_id ?? '-'}</td>
                <td>${item.user?.name || item.user?.email || 'System'}</td><td>${item.description || ''}</td>
                <td>${new Date(item.created_at).toLocaleString()}</td><td class="text-center">${item.action === 'deleted' ? `<button class="bg-primary text-white px-3 py-1 rounded text-xs" onclick="historyPage.restore(${item.id})">Restore</button>` : ''}</td></tr>`).join('') : '<tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">No history found.</td></tr>';
        }

        async function restore(id) {
            const response = await fetch(`/api/v1/histories/${id}/restore`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } });
            const payload = await response.json();
            if (!response.ok) { error.textContent = payload.message || 'Unable to restore record.'; return; }
            load();
        }

        filters.addEventListener('submit', load);
        load();
        return { load, restore };
    })();
    </script>
    @endpush
</x-app-layout>
