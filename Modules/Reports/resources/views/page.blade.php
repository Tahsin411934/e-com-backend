<x-app-layout>
    <div class="p-4 max-w-full">

        {{-- Page header --}}
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div>
                <h1 class="text-xl font-bold text-gray-800">{{ $title }}</h1>
                <p class="text-sm text-gray-500">Reports module · date range, store, product/category/brand filters & CSV export.</p>
            </div>
            <button type="button" class="js-reports-reload"
                class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-semibold">
                <i class="fa fa-rotate mr-1"></i> Refresh
            </button>
        </div>

        {{-- Filter bar --}}
        <form method="GET" class="flex flex-wrap items-end gap-3 mb-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Period</label>
                <select name="period" data-local-select2 class="rounded-lg border-gray-300 text-sm">
                    <option value="all" {{ request('period', 'all') === 'all' ? 'selected' : '' }}>All time</option>
                    <option value="today" {{ request('period') === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="week" {{ request('period') === 'week' ? 'selected' : '' }}>This week</option>
                    <option value="month" {{ request('period') === 'month' ? 'selected' : '' }}>This month</option>
                    <option value="custom" {{ request('period') === 'custom' ? 'selected' : '' }}>Custom</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-lg border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-lg border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Store</label>
                <select name="store_id" data-local-select2 class="rounded-lg border-gray-300 text-sm">
                    <option value="">All Stores</option>
                    @foreach(\Modules\Store\Models\Store::orderBy('name')->get() as $store)
                        <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                    @endforeach
                </select>
            </div>
            <button class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-semibold">
                <i class="fa fa-filter mr-1"></i> Filter
            </button>
        </form>

        {{-- Sections: rendered by JS from the API --}}
        <div id="reportsContainer" class="space-y-4">
            @forelse($sections as $section)
                <div class="report-section bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm"
                     data-category="{{ $section['category'] ?? $category }}"
                     data-method="{{ $section['method'] }}"
                     data-title="{{ $section['title'] }}">
                    <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="font-semibold text-gray-800">{{ $section['title'] }}</h2>
                        <a href="#" class="js-reports-export export-btn text-xs text-primary hover:underline">
                            <i class="fa fa-download mr-1"></i> Export CSV
                        </a>
                    </div>
                    <div class="p-4 report-body">
                        <div class="text-sm text-gray-400"><i class="fa fa-spinner fa-spin mr-1"></i>Loading…</div>
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm p-6 text-sm text-gray-500">
                    <i class="fa fa-circle-info mr-1"></i> No report sections are wired up for this category yet.
                </div>
            @endforelse
        </div>

    </div>

    @push('scripts')
    <script>
        (function () {
            const API_BASE = '/api/v1/reports';

            function queryString() {
                return new URLSearchParams(window.location.search).toString();
            }

            function apiUrl(category, method) {
                const url = API_BASE + '/' + category + '/' + method;
                const qs = queryString();
                return qs ? url + '?' + qs : url;
            }

            function esc(value) {
                return String(value ?? '').replace(/[&<>"']/g, function (c) {
                    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c];
                });
            }

            function formatMoney(v) {
                return Number(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function kpiCard(k) {
                let change = '';
                if (k.change !== null && k.change !== undefined) {
                    const cls = k.change >= 0 ? 'text-green-600' : 'text-red-600';
                    const arrow = k.change >= 0 ? '▲' : '▼';
                    change = '<span class="text-xs ' + cls + '">' + arrow + ' ' + Math.abs(k.change) + '% vs prev.</span>';
                }
                return '<div class="flex flex-col justify-between rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-3">'
                    + '<span class="text-xs text-gray-500">' + esc(k.label) + '</span>'
                    + '<span class="text-xl font-bold text-gray-800 mt-1">' + formatMoney(k.value) + '</span>'
                    + change
                    + '</div>';
            }

            function renderTable(payload) {
                const columns = payload.columns || {};
                const rows = payload.rows || [];
                const keys = Object.keys(columns);
                if (!keys.length) return null;
                const head = '<tr>' + keys.map(k => '<th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">' + esc(columns[k]) + '</th>').join('') + '</tr>';
                const body = rows.length
                    ? rows.map(r => '<tr>' + keys.map(k => '<td class="px-3 py-2 border-t border-gray-100 dark:border-gray-700">' + esc(r[k] ?? '-') + '</td>').join('') + '</tr>').join('')
                    : '<tr><td class="px-3 py-4 text-center text-gray-400" colspan="' + keys.length + '">No data.</td></tr>';
                return '<div class="overflow-x-auto"><table class="w-full text-sm text-gray-700 dark:text-gray-200"><thead>' + head + '</thead><tbody>' + body + '</tbody></table></div>';
            }

            function renderKpis(payload) {
                const items = Array.isArray(payload) ? payload : [];
                const isKpi = items.length > 0 && items.every(i => i && typeof i === 'object' && 'value' in i);
                if (!isKpi) return null;
                const grid = items.map(kpiCard).join('');
                return '<div class="grid grid-cols-2 md:grid-cols-4 gap-3">' + grid + '</div>';
            }

            function renderSeries(payload) {
                const items = Array.isArray(payload) ? payload : [];
                const isSeries = items.length > 0 && items.every(i => i && typeof i === 'object' && 'label' in i);
                if (!isSeries) return null;
                const body = items.length
                    ? items.map(i => '<tr><td class="px-3 py-2 border-t border-gray-100 dark:border-gray-700">' + esc(i.label) + '</td><td class="px-3 py-2 border-t border-gray-100 dark:border-gray-700 text-right">' + formatMoney(i.value) + '</td></tr>').join('')
                    : '<tr><td class="px-3 py-4 text-center text-gray-400" colspan="2">No data.</td></tr>';
                return '<div class="overflow-x-auto"><table class="w-full text-sm text-gray-700 dark:text-gray-200"><thead><tr><th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Label</th><th class="px-3 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Value</th></tr></thead><tbody>' + body + '</tbody></table></div>';
            }

            function renderFallback(payload) {
                try {
                    return '<pre class="text-xs text-gray-600 dark:text-gray-300 overflow-x-auto whitespace-pre-wrap">' + esc(JSON.stringify(payload, null, 2)) + '</pre>';
                } catch (e) {
                    return '<div class="text-sm text-gray-400">Unexpected response.</div>';
                }
            }

            function renderPayload(body, payload) {
                body.innerHTML = renderTable(payload) || renderKpis(payload) || renderSeries(payload) || renderFallback(payload);
            }

            async function loadSection(section) {
                const body = section.querySelector('.report-body');
                const category = section.dataset.category;
                const method = section.dataset.method;
                try {
                    const response = await fetch(apiUrl(category, method), { headers: { 'Accept': 'application/json' } });
                    const payload = await response.json();
                    if (!response.ok) {
                        body.innerHTML = '<div class="text-sm text-red-600">' + esc(payload.error || payload.message || 'Failed to load this section.') + '</div>';
                        return;
                    }
                    renderPayload(body, payload.data ?? payload);
                } catch (e) {
                    body.innerHTML = '<div class="text-sm text-red-600">Failed to load this section.</div>';
                }
            }

            function load() {
                document.querySelectorAll('.report-section').forEach(loadSection);
            }

            function exportCsv(btn) {
                const section = btn.closest('.report-section');
                if (!section) return false;
                const qs = queryString();
                location.href = API_BASE + '/' + section.dataset.category + '/' + section.dataset.method + '/export' + (qs ? '?' + qs : '');
                return false;
            }

            $(document).on('click', '.js-reports-reload', load);
            $(document).on('click', '.js-reports-export', function (event) {
                event.preventDefault();
                exportCsv(this);
            });

            load();
        })();
    </script>
    @endpush
</x-app-layout>

