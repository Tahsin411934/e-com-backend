<x-app-layout>
    @php
        $money = fn ($value) => '৳' . number_format((float) $value, 2);
    @endphp

    <div class="p-4 space-y-5">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Account Dashboard</h1>
                <p class="text-sm text-gray-500">Income, cost, profit, cash, bank and product-level performance.</p>
            </div>
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">From</label>
                    <input type="date" name="from" value="{{ $from->toDateString() }}" class="rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">To</label>
                    <input type="date" name="to" value="{{ $to->toDateString() }}" class="rounded-lg border-gray-300 text-sm">
                </div>
                <button class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-semibold">
                    <i class="fa fa-filter mr-1"></i> Filter
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="text-xs font-semibold text-gray-500 uppercase">Total Income</div>
                <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $money($summary['total_income']) }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="text-xs font-semibold text-gray-500 uppercase">Product Cost</div>
                <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $money($summary['total_cost']) }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="text-xs font-semibold text-gray-500 uppercase">Gross Profit</div>
                <div class="mt-2 text-2xl font-bold {{ $summary['gross_profit'] < 0 ? 'text-red-600' : 'text-green-600' }}">{{ $money($summary['gross_profit']) }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="text-xs font-semibold text-gray-500 uppercase">Expenses + Refunds</div>
                <div class="mt-2 text-2xl font-bold text-red-600">{{ $money($summary['expenses'] + $summary['refunds']) }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="text-xs font-semibold text-gray-500 uppercase">Cash In Hand</div>
                <div class="mt-2 text-xl font-bold">{{ $money($summary['cash']) }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="text-xs font-semibold text-gray-500 uppercase">Bank Balance</div>
                <div class="mt-2 text-xl font-bold">{{ $money($summary['bank']) }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="text-xs font-semibold text-gray-500 uppercase">Mobile Banking</div>
                <div class="mt-2 text-xl font-bold">{{ $money($summary['mobile_banking']) }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="text-xs font-semibold text-gray-500 uppercase">Net Profit</div>
                <div class="mt-2 text-xl font-bold {{ $summary['net_profit'] < 0 ? 'text-red-600' : 'text-green-600' }}">{{ $money($summary['net_profit']) }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="text-xs font-semibold text-gray-500 uppercase">Cash Movement</div>
                <div class="mt-2 text-xl font-bold {{ $summary['cash_movement'] < 0 ? 'text-red-600' : 'text-green-600' }}">{{ $money($summary['cash_movement']) }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
            <div class="xl:col-span-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 font-bold">
                    <i class="fa fa-chart-line text-primary mr-2"></i> Product Profit
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500">
                            <tr>
                                <th class="text-left px-4 py-3">Product</th>
                                <th class="text-right px-4 py-3">Qty</th>
                                <th class="text-right px-4 py-3">Sales</th>
                                <th class="text-right px-4 py-3">Cost</th>
                                <th class="text-right px-4 py-3">Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProducts as $product)
                                <tr class="border-t">
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-gray-800">{{ $product->product_name }}</div>
                                        <div class="text-xs text-gray-400">{{ $product->sku ?: '-' }} {{ $product->variant_name ? ' / ' . $product->variant_name : '' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-right">{{ number_format((float) $product->quantity_sold, 2) }}</td>
                                    <td class="px-4 py-3 text-right">{{ $money($product->net_sales) }}</td>
                                    <td class="px-4 py-3 text-right">{{ $money($product->cost_total) }}</td>
                                    <td class="px-4 py-3 text-right font-bold {{ $product->gross_profit < 0 ? 'text-red-600' : 'text-green-600' }}">{{ $money($product->gross_profit) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">No product profit data yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 font-bold">
                    <i class="fa fa-wallet text-primary mr-2"></i> Account Balances
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($accountBalances as $account)
                        <div class="px-5 py-3 flex items-center justify-between">
                            <div>
                                <div class="font-semibold text-gray-800">{{ $account->name }}</div>
                                <div class="text-xs text-gray-400">{{ ucfirst(str_replace('_', ' ', $account->type)) }}</div>
                            </div>
                            <div class="font-bold">{{ $money($account->current_balance) }}</div>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-gray-400">No accounts created yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>