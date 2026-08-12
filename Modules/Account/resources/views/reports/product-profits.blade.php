<x-app-layout>
    <div class="p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3 mb-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">From</label>
                <input type="date" id="profit_from" name="from" value="{{ request('from') }}" class="rounded-lg border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">To</label>
                <input type="date" id="profit_to" name="to" value="{{ request('to') }}" class="rounded-lg border-gray-300 text-sm">
            </div>
            <button class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-semibold">
                <i class="fa fa-filter mr-1"></i> Filter
            </button>
        </form>

        <x-data-table
            id="accountProductProfitsTable"
            title="Product Profit Report"
            icon="fa-solid fa-chart-simple"
            :buttonId="null"
            :columns="['Product','Variant','SKU','Qty Sold','Sales','Cost','Profit/Loss','Margin']"
            :dtColumns="[
                ['data' => 'product_name'],
                ['data' => 'variant_name'],
                ['data' => 'sku'],
                ['data' => 'quantity_sold'],
                ['data' => 'net_sales'],
                ['data' => 'cost_total'],
                ['data' => 'gross_profit'],
                ['data' => 'profit_margin'],
            ]"
            ajaxUrl="{{ route('account-product-profits.dataTable', ['from' => request('from'), 'to' => request('to')]) }}"
            :order="[[6, 'desc']]"
        />
    </div>
</x-app-layout>
