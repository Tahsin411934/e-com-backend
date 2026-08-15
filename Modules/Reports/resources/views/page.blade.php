<x-app-layout>
    <div class="p-4 max-w-full">

        {{-- Page header --}}
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div>
                <h1 class="text-xl font-bold text-gray-800">{{ $title }}</h1>
                <p class="text-sm text-gray-500">Reports module · date range, store, product/category/brand filters & CSV export.</p>
            </div>
            <button type="button" onclick="reports.reload()"
                class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-semibold">
                <i class="fa fa-rotate mr-1"></i> Refresh
            </button>
        </div>

        {{-- Filter bar --}}
        <form method="GET" class="flex flex-wrap items-end gap-3 mb-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Period</label>
                <select name="period" class="rounded-lg border-gray-300 text-sm">
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
                <select name="store_id" class="rounded-lg border-gray-300 text-sm">
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
            @foreach($sections as $section)
                <div class="report-section bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm"
                     data-category="{{ $category }}"
                     data-method="{{ $section['method'] }}"
                     data-title="{{ $section['title'] }}">
                    <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="font-semibold text-gray-800">{{ $section['title'] }}</h2>
                        <a href="#" class="export-btn text-xs text-primary hover:underline" onclick="return reports.export(this)">
                            <i class="fa fa-download mr-1"></i> Export CSV
                        </a>
                    </div>
                    <div class="p-4 report-body">
                        <div class="text-sm text-gray-400"><i class="fa fa-spinner fa-spin mr-1"></i>Loading…</div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>

    @push('scripts')
    <script>
        window.reports = (function () {
            const API_BASE = '/api/v1/reports';

            function queryString() {
                return new URLSearchParams(window.location.search).toString();
            }

            function apiUrl(category, method) {
                const url = API_BASE + '/' + category + '/' + method;
                const qs = queryString();
                return qs ? url + '?' + qs : url;
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
                    + '<span class="text-xs text-gray-500">' + k.label + '</span>'
                    + '<span class="text-xl font-bold text-gray-800 mt-1">' + formatMoney(k.value) + '</span>'
                    + change
                    + '</div>';
            }