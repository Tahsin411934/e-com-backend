<x-app-layout>
    @push('head')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
        <style>
            .dashboard-card { border-radius: 16px; border: 1px solid #e5e7eb; background: #fff; box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04); }
            .kpi-card { transition: transform 0.18s ease, box-shadow 0.18s ease; }
            .kpi-card:hover { transform: translateY(-2px); box-shadow: 0 14px 30px -18px rgba(15, 23, 42, 0.35); }
            .status-badge { display: inline-flex; align-items: center; border-radius: 999px; padding: 3px 10px; font-size: 11px; font-weight: 700; text-transform: capitalize; }
            .status-pending, .status-unpaid, .status-unfulfilled { color: #92400e; background: #fef3c7; }
            .status-confirmed, .status-processing, .status-authorized, .status-partial { color: #1d4ed8; background: #dbeafe; }
            .status-ready, .status-paid, .status-fulfilled, .status-completed, .status-delivered { color: #047857; background: #d1fae5; }
            .status-cancelled, .status-refunded, .status-failed, .status-returned { color: #be123c; background: #ffe4e6; }
        </style>
    @endpush

    @php
        $formatMoney = fn ($value) => '৳' . number_format((float) $value, 2);
        $formatInt = fn ($value) => number_format((int) $value);
        $sourceAmounts = $revenueBySource['amounts'] ?? [];
        $hasRevenueSource = count($sourceAmounts) > 0;
        $hasDeliveryData = collect($deliveryOverview['data'] ?? [])->sum() > 0;
    @endphp

    <div class="p-6 lg:p-8 max-w-[1600px] mx-auto">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-8 gap-4">
            <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 tracking-tight">Admin Dashboard</h1>
                <p class="text-gray-500 mt-1.5 text-sm">Live business data from the database.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-sm font-medium text-gray-700">{{ now()->startOfMonth()->format('M j') }} - {{ now()->format('M j, Y') }}</span>
                <a href="{{ route('dashboard.api') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-900 text-white text-sm font-semibold hover:bg-gray-800">
                    <i class="fas fa-code text-xs"></i>
                    Data API
                </a>
            </div>
        </div>

        @if(($storeProfileProgress['total'] ?? 0) > 0 && ($storeProfileProgress['percent'] ?? 100) < 100)
            <div class="dashboard-card mb-8 p-5 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Complete your store profile</h2>
                        <p class="text-sm text-gray-500 mt-1">Add your branding and storefront details before launch.</p>
                    </div>
                    <span class="text-2xl font-bold text-primary">{{ $storeProfileProgress['percent'] }}%</span>
                </div>
                <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-primary rounded-full transition-all" style="width: {{ $storeProfileProgress['percent'] }}%"></div>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach($storeProfileProgress['items'] as $item)
                        <a href="{{ $item['url'] }}" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium {{ $item['done'] ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600 hover:bg-primary-soft hover:text-primary' }}">
                            <i class="fas {{ $item['done'] ? 'fa-check' : 'fa-arrow-up-right-from-square' }} text-[10px]"></i>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- First-time store onboarding --}}
        @if(($kpi['totalProducts'] ?? 0) == 0 && ($kpi['totalOrders'] ?? 0) == 0)
            <div class="dashboard-card mb-8 overflow-hidden border-primary/20 bg-gradient-to-br from-primary-soft to-white">
                <div class="p-5 sm:p-6 lg:p-7">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-primary text-white flex items-center justify-center shrink-0 shadow-sm">
                                <i class="fas fa-store text-lg"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-900">Welcome, {{ Auth::user()->name }}! 👋</h2>
                                <p class="text-sm text-gray-500 mt-1">Complete these quick steps to start selling from your store.</p>
                            </div>
                        </div>
                        <a href="{{ route('products.create') }}"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-primary text-white text-sm font-semibold hover:bg-primary-hover transition-colors shadow-sm">
                            <i class="fas fa-plus text-xs"></i>
                            Add Your First Product
                        </a>
                    </div>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-3">
                        <a href="{{ route('products.create') }}" class="flex items-center gap-3 rounded-xl bg-white border border-gray-200 p-4 hover:border-primary hover:shadow-sm transition-all">
                            <span class="w-8 h-8 rounded-full bg-primary-light text-primary flex items-center justify-center"><i class="fas fa-box text-xs"></i></span>
                            <span class="min-w-0"><span class="block text-sm font-semibold text-gray-800">Add a product</span><span class="block text-xs text-gray-500 mt-0.5">Build your catalog</span></span>
                            <i class="fas fa-arrow-right ml-auto text-xs text-gray-400"></i>
                        </a>
                        <a href="{{ route('categories.index') }}" class="flex items-center gap-3 rounded-xl bg-white border border-gray-200 p-4 hover:border-primary hover:shadow-sm transition-all">
                            <span class="w-8 h-8 rounded-full bg-primary-light text-primary flex items-center justify-center"><i class="fas fa-tags text-xs"></i></span>
                            <span class="min-w-0"><span class="block text-sm font-semibold text-gray-800">Create a category</span><span class="block text-xs text-gray-500 mt-0.5">Organize products</span></span>
                            <i class="fas fa-arrow-right ml-auto text-xs text-gray-400"></i>
                        </a>
                        <div class="flex items-center gap-3 rounded-xl bg-white/70 border border-dashed border-gray-300 p-4">
                            <span class="w-8 h-8 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center"><i class="fas fa-chart-line text-xs"></i></span>
                            <span class="min-w-0"><span class="block text-sm font-semibold text-gray-700">Track your sales</span><span class="block text-xs text-gray-500 mt-0.5">Analytics appear after your first order</span></span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-6 gap-4 mb-8">
            <div class="dashboard-card kpi-card p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center"><i class="fas fa-dollar-sign text-emerald-600"></i></div>
                    <span class="text-xs font-bold {{ $kpi['revenueGrowth'] >= 0 ? 'text-emerald-700 bg-emerald-50' : 'text-rose-700 bg-rose-50' }} px-2.5 py-1 rounded-full">{{ $kpi['revenueGrowth'] >= 0 ? '+' : '' }}{{ $kpi['revenueGrowth'] }}%</span>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $formatMoney($kpi['totalRevenue']) }}</p>
                <p class="text-xs text-gray-500 mt-1 font-medium">Total Revenue</p>
                <p class="text-[11px] text-gray-400 mt-1">Last month {{ $formatMoney($kpi['lastMonthRevenue']) }}</p>
            </div>

            <div class="dashboard-card kpi-card p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center"><i class="fas fa-shopping-bag text-indigo-600"></i></div>
                    <span class="text-xs font-bold {{ $kpi['orderGrowth'] >= 0 ? 'text-indigo-700 bg-indigo-50' : 'text-rose-700 bg-rose-50' }} px-2.5 py-1 rounded-full">{{ $kpi['orderGrowth'] >= 0 ? '+' : '' }}{{ $kpi['orderGrowth'] }}%</span>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $formatInt($kpi['totalOrders']) }}</p>
                <p class="text-xs text-gray-500 mt-1 font-medium">Total Orders</p>
                <p class="text-[11px] text-gray-400 mt-1">{{ $formatInt($kpi['thisMonthOrders']) }} this month</p>
            </div>

            <div class="dashboard-card kpi-card p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center"><i class="fas fa-users text-violet-600"></i></div>
                    <span class="text-xs font-bold {{ $kpi['customerGrowth'] >= 0 ? 'text-violet-700 bg-violet-50' : 'text-rose-700 bg-rose-50' }} px-2.5 py-1 rounded-full">{{ $kpi['customerGrowth'] >= 0 ? '+' : '' }}{{ $kpi['customerGrowth'] }}%</span>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $formatInt($kpi['totalCustomers']) }}</p>
                <p class="text-xs text-gray-500 mt-1 font-medium">Users</p>
                <p class="text-[11px] text-gray-400 mt-1">{{ $formatInt($kpi['thisMonthCustomers']) }} this month</p>
            </div>

            <div class="dashboard-card kpi-card p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center"><i class="fas fa-box text-amber-600"></i></div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $formatInt($kpi['totalProducts']) }}</p>
                <p class="text-xs text-gray-500 mt-1 font-medium">Products</p>
                <p class="text-[11px] text-gray-400 mt-1">Catalog records</p>
            </div>

            <div class="dashboard-card kpi-card p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl bg-cyan-50 flex items-center justify-center"><i class="fas fa-clock text-cyan-600"></i></div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $formatInt($kpi['pendingOrders']) }}</p>
                <p class="text-xs text-gray-500 mt-1 font-medium">Pending Orders</p>
                <p class="text-[11px] text-gray-400 mt-1">Needs action</p>
            </div>

            <div class="dashboard-card kpi-card p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 flex items-center justify-center"><i class="fas fa-triangle-exclamation text-rose-600"></i></div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $formatInt($kpi['lowStockItems']) }}</p>
                <p class="text-xs text-gray-500 mt-1 font-medium">Low Stock</p>
                <p class="text-[11px] text-gray-400 mt-1">At or below reorder point</p>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
            <div class="dashboard-card xl:col-span-2 p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-lg font-bold text-gray-900">Revenue Analytics</h2>
                    <span class="text-xs text-gray-500">Last 12 months</span>
                </div>
                <div class="h-[310px]">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <div class="dashboard-card p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-lg font-bold text-gray-900">Revenue Sources</h2>
                    <span class="text-xs text-gray-500">By order source</span>
                </div>
                @if($hasRevenueSource)
                    <div class="h-[210px] mb-5">
                        <canvas id="revenueSourcesChart"></canvas>
                    </div>
                    <div class="space-y-3">
                        @foreach($sourceAmounts as $index => $source)
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background: {{ $revenueBySource['colors'][$index] ?? '#64748B' }}"></span>
                                    <span class="font-medium text-gray-700 truncate">{{ $source['label'] }}</span>
                                </div>
                                <span class="font-bold text-gray-900">{{ $formatMoney($source['amount']) }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="h-[260px] flex items-center justify-center text-sm text-gray-500 border border-dashed border-gray-200 rounded-xl">No revenue source data yet.</div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
            <div class="dashboard-card p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-5">Delivery Overview</h2>
                @if($hasDeliveryData)
                    <div class="h-[230px]">
                        <canvas id="deliveryChart"></canvas>
                    </div>
                @else
                    <div class="h-[230px] flex items-center justify-center text-sm text-gray-500 border border-dashed border-gray-200 rounded-xl">No delivery data yet.</div>
                @endif
            </div>

            <div class="dashboard-card p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-5">Inventory Summary</h2>
                <div class="space-y-4">
                    @foreach([
                        ['label' => 'In Stock', 'value' => $inventorySummary['inStock'], 'color' => 'bg-emerald-500'],
                        ['label' => 'Low Stock', 'value' => $inventorySummary['lowStock'], 'color' => 'bg-amber-500'],
                        ['label' => 'Out of Stock', 'value' => $inventorySummary['outOfStock'], 'color' => 'bg-rose-500'],
                    ] as $row)
                        @php $percent = $inventorySummary['totalStockItems'] > 0 ? round(($row['value'] / $inventorySummary['totalStockItems']) * 100) : 0; @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1.5">
                                <span class="font-medium text-gray-700">{{ $row['label'] }}</span>
                                <span class="font-bold text-gray-900">{{ $formatInt($row['value']) }}</span>
                            </div>
                            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full {{ $row['color'] }}" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-gray-500 mt-5">{{ $formatInt($inventorySummary['totalStockItems']) }} tracked stock records</p>
            </div>

            <div class="dashboard-card p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-5">Recent Activity</h2>
                <div class="space-y-4">
                    @forelse($recentActivities as $activity)
                        <div class="flex gap-3">
                            <div class="w-9 h-9 rounded-xl bg-gray-100 flex items-center justify-center shrink-0">
                                <i class="fas fa-{{ $activity['icon'] }} text-gray-600 text-sm"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $activity['description'] }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $activity['user'] }} - {{ $activity['created_at'] }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="h-[180px] flex items-center justify-center text-sm text-gray-500 border border-dashed border-gray-200 rounded-xl">No recent activity yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
            <div class="dashboard-card overflow-hidden">
                <div class="px-6 py-5 flex items-center justify-between border-b border-gray-100">
                    <h2 class="text-lg font-bold text-gray-900">Recent Orders</h2>
                    <a href="{{ route('orders.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">View all</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-6 py-3 text-left">Order</th>
                                <th class="px-6 py-3 text-left">Customer</th>
                                <th class="px-6 py-3 text-left">Status</th>
                                <th class="px-6 py-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($recentOrders as $order)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">#{{ $order['order_number'] }}</div>
                                        <div class="text-xs text-gray-500">{{ $order['created_at'] }} - {{ $order['source'] }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-700">{{ $order['customer'] }}</td>
                                    <td class="px-6 py-4">
                                        <span class="status-badge status-{{ $order['status'] }}">{{ str_replace('_', ' ', $order['status']) }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold text-gray-900">{{ $formatMoney($order['grand_total']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-10 text-center text-gray-500">No orders found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="dashboard-card overflow-hidden">
                <div class="px-6 py-5 flex items-center justify-between border-b border-gray-100">
                    <h2 class="text-lg font-bold text-gray-900">Top Selling Products</h2>
                    <a href="{{ route('products.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">Products</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-6 py-3 text-left">Product</th>
                                <th class="px-6 py-3 text-right">Qty</th>
                                <th class="px-6 py-3 text-right">Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($topSellingProducts as $product)
                                <tr>
                                    <td class="px-6 py-4 font-semibold text-gray-900">{{ $product['product_name'] }}</td>
                                    <td class="px-6 py-4 text-right text-gray-700">{{ $formatInt($product['total_quantity']) }}</td>
                                    <td class="px-6 py-4 text-right font-bold text-gray-900">{{ $formatMoney($product['total_revenue']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-6 py-10 text-center text-gray-500">No sold products found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="dashboard-card overflow-hidden">
            <div class="px-6 py-5 flex items-center justify-between border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900">Low Stock Products</h2>
                <a href="{{ route('inventory-stock.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">Inventory</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-6 py-3 text-left">Product</th>
                            <th class="px-6 py-3 text-left">Variant</th>
                            <th class="px-6 py-3 text-left">SKU</th>
                            <th class="px-6 py-3 text-right">Available</th>
                            <th class="px-6 py-3 text-right">Reorder Point</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($lowStockProducts as $stock)
                            <tr>
                                <td class="px-6 py-4 font-semibold text-gray-900">{{ $stock['product_name'] }}</td>
                                <td class="px-6 py-4 text-gray-700">{{ $stock['variant_name'] }}</td>
                                <td class="px-6 py-4 text-gray-500">{{ $stock['sku'] ?? '-' }}</td>
                                <td class="px-6 py-4 text-right font-bold {{ $stock['available'] <= 0 ? 'text-rose-600' : 'text-amber-600' }}">{{ $formatInt($stock['available']) }}</td>
                                <td class="px-6 py-4 text-right text-gray-700">{{ $formatInt($stock['reorder_point']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-10 text-center text-gray-500">No low stock products found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const chartDefaults = {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { boxWidth: 12, color: '#475569', font: { size: 12, weight: '600' } } }
                    }
                };

                const revenueCanvas = document.getElementById('revenueChart');
                if (revenueCanvas) {
                    new Chart(revenueCanvas, {
                        type: 'line',
                        data: {
                            labels: @json($monthlyRevenue['labels']),
                            datasets: [
                                {
                                    label: @json($monthlyRevenue['currentYearLabel']),
                                    data: @json($monthlyRevenue['currentYear']),
                                    borderColor: '#4F46E5',
                                    backgroundColor: 'rgba(79, 70, 229, 0.12)',
                                    fill: true,
                                    tension: 0.35,
                                    pointRadius: 3
                                },
                                {
                                    label: @json($monthlyRevenue['lastYearLabel']),
                                    data: @json($monthlyRevenue['lastYear']),
                                    borderColor: '#94A3B8',
                                    backgroundColor: 'rgba(148, 163, 184, 0.08)',
                                    borderDash: [6, 4],
                                    fill: false,
                                    tension: 0.35,
                                    pointRadius: 3
                                }
                            ]
                        },
                        options: {
                            ...chartDefaults,
                            scales: {
                                y: { beginAtZero: true, ticks: { callback: value => '৳' + Number(value).toLocaleString() }, grid: { color: '#F1F5F9' } },
                                x: { grid: { display: false } }
                            }
                        }
                    });
                }

                const sourceCanvas = document.getElementById('revenueSourcesChart');
                if (sourceCanvas) {
                    new Chart(sourceCanvas, {
                        type: 'doughnut',
                        data: {
                            labels: @json($revenueBySource['labels']),
                            datasets: [{ data: @json($revenueBySource['data']), backgroundColor: @json($revenueBySource['colors']), borderWidth: 0 }]
                        },
                        options: { ...chartDefaults, cutout: '64%' }
                    });
                }

                const deliveryCanvas = document.getElementById('deliveryChart');
                if (deliveryCanvas) {
                    new Chart(deliveryCanvas, {
                        type: 'bar',
                        data: {
                            labels: @json($deliveryOverview['labels']),
                            datasets: [{ label: 'Deliveries', data: @json($deliveryOverview['data']), backgroundColor: @json($deliveryOverview['colors']), borderRadius: 8 }]
                        },
                        options: {
                            ...chartDefaults,
                            plugins: { legend: { display: false } },
                            scales: {
                                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#F1F5F9' } },
                                x: { grid: { display: false } }
                            }
                        }
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>
