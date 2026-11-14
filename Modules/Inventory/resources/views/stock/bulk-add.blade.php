<x-app-layout>
    <div class="p-4">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div>
                <nav class="mb-1 flex items-center gap-2 text-sm text-gray-500">
                    <a href="{{ route('inventory-stock.index') }}" class="transition-colors hover:text-primary">Inventory Stock</a>
                    <i class="fas fa-chevron-right text-[10px]"></i>
                    <span class="font-medium text-gray-800">Bulk Add</span>
                </nav>
                <h1 class="text-2xl font-bold text-gray-800">Bulk Add Stock</h1>
                <p class="mt-1 text-sm text-gray-500">Select a product, then enter stock quantities for its variants in one go.</p>
            </div>
            <a href="{{ route('inventory-stock.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-50">
                <i class="fas fa-arrow-left"></i> Back to Stock
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('inventory-stock.bulk-add.store') }}" id="bulkStockForm" class="space-y-5">
            @csrf
            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 bg-gray-50/70 px-5 py-4">
                    <h2 class="flex items-center gap-2 font-semibold text-gray-800"><i class="fas fa-sliders text-primary"></i> Entry Details</h2>
                </div>
                <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">
                    <div>
                        <label for="bulkLocation" class="mb-1 block text-sm font-medium text-gray-700">Inventory location <span class="text-red-500">*</span></label>
                        <select id="bulkLocation" name="location_id" required class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                            <option value="">Select a location</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}" @selected(old('location_id') == $location->id)>{{ $location->name }}{{ $location->store ? ' — '.$location->store->name : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="bulkProduct" class="mb-1 block text-sm font-medium text-gray-700">Product <span class="text-red-500">*</span></label>
                        <select id="bulkProduct" name="product_id" required class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                            <option value="">Select a product</option>
                            @if($oldProduct)<option value="{{ $oldProduct->id }}" selected>{{ $oldProduct->name }}</option>@endif
                        </select>
                    </div>
                </div>
            </section>

            <section id="bulkVariantSection" class="hidden overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 bg-gray-50/70 px-5 py-4">
                    <div>
                        <h2 class="font-semibold text-gray-800">Variant quantities</h2>
                        <p class="mt-1 text-xs text-gray-500">Enter how many units to add. Leave rows at 0 to skip them.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <input id="applyQuantity" type="number" min="0" step="1" placeholder="Same qty" class="w-28 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary focus:outline-none">
                        <button type="button" id="applyQuantityButton" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Apply to all</button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[650px] text-sm">
                        <thead class="bg-white text-left text-xs uppercase tracking-wide text-gray-500">
                            <tr class="border-b border-gray-200">
                                <th class="px-5 py-3 font-medium">Variant / Option</th>
                                <th class="px-5 py-3 font-medium">SKU</th>
                                <th class="px-5 py-3 text-right font-medium">Current stock</th>
                                <th class="w-40 px-5 py-3 text-right font-medium">Add quantity</th>
                            </tr>
                        </thead>
                        <tbody id="bulkVariantRows" class="divide-y divide-gray-100"></tbody>
                    </table>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 bg-gray-50/50 px-5 py-4">
                    <p id="bulkSummary" class="text-sm text-gray-600">Choose a product to see its variants.</p>
                    <button id="bulkSubmit" type="submit" class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50">
                        <i class="fas fa-plus"></i> Add Stock
                    </button>
                </div>
            </section>

            <p class="rounded-lg border border-blue-100 bg-blue-50 px-4 py-3 text-sm leading-6 text-blue-800">
                <strong>Stock entry vs purchase:</strong> This page increases on-hand stock and records an inventory adjustment. To record a supplier, purchase cost, invoice, or payment, use <a href="{{ route('purchase-orders.create') }}" class="font-semibold underline">Purchase Orders</a>.
            </p>
        </form>
    </div>

    @push('scripts')
    <script>
        (() => {
            const productSelect = document.getElementById('bulkProduct');
            const locationSelect = document.getElementById('bulkLocation');
            const section = document.getElementById('bulkVariantSection');
            const rows = document.getElementById('bulkVariantRows');
            const summary = document.getElementById('bulkSummary');
            const submit = document.getElementById('bulkSubmit');
            const oldItems = @json(old('items', []));
            const searchProductsUrl = @json(route('inventory-stock.bulk-add.products'));
            const productDetailsUrl = @json(route('inventory-stock.bulk-add.products.show', ['product' => '__PRODUCT__']));
            let selectedProductId = productSelect.value || '';
            let requestVersion = 0;

            const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
            }[char]));

            function renderRows() {
                const thisRequest = ++requestVersion;
                rows.replaceChildren();
                selectedProductId = productSelect.value || '';
                section.classList.toggle('hidden', !selectedProductId);
                if (!selectedProductId || !locationSelect.value) {
                    summary.textContent = selectedProductId ? 'Choose an inventory location to see current stock.' : 'Choose a product to see its variants.';
                    submit.disabled = true;
                    return;
                }

                summary.textContent = 'Loading product variants...';
                submit.disabled = true;
                const url = productDetailsUrl.replace('__PRODUCT__', encodeURIComponent(selectedProductId)) + `?location_id=${encodeURIComponent(locationSelect.value)}`;
                fetch(url, { headers: { Accept: 'application/json' } })
                    .then((response) => {
                        if (!response.ok) throw new Error('Could not load product variants.');
                        return response.json();
                    })
                    .then((data) => {
                        if (thisRequest !== requestVersion || selectedProductId !== String(data.product.id)) return;
                        rows.replaceChildren();
                        data.variants.forEach((variant, index) => {
                            const optionId = variant.variant_option_id ?? '';
                            const oldItem = oldItems.find((item) => String(item.variant_id) === String(variant.variant_id) && String(item.variant_option_id || '') === String(optionId));
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td class="px-5 py-3.5 font-medium text-gray-800">${escapeHtml(variant.name)}<input type="hidden" name="items[${index}][variant_id]" value="${variant.variant_id}"><input type="hidden" name="items[${index}][variant_option_id]" value="${optionId}"></td>
                                <td class="px-5 py-3.5 text-gray-500">${escapeHtml(variant.sku || '—')}</td>
                                <td class="px-5 py-3.5 text-right tabular-nums text-gray-600">${Number(variant.current_stock).toLocaleString()}</td>
                                <td class="px-5 py-3.5"><input type="number" name="items[${index}][quantity_to_add]" value="${escapeHtml(oldItem?.quantity_to_add ?? 0)}" min="0" step="1" inputmode="numeric" class="bulk-quantity w-full rounded-lg border border-gray-300 px-3 py-2 text-right focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20" aria-label="Quantity to add for ${escapeHtml(variant.name)}"></td>`;
                            rows.appendChild(tr);
                        });
                        if (!data.variants.length) {
                            const tr = document.createElement('tr');
                            tr.innerHTML = '<td colspan="4" class="px-5 py-10 text-center text-gray-500">This product has no active variants.</td>';
                            rows.appendChild(tr);
                        }
                        updateSummary();
                    })
                    .catch((error) => {
                        if (thisRequest !== requestVersion) return;
                        rows.replaceChildren();
                        summary.textContent = error.message;
                        submit.disabled = true;
                    });
            }

            function updateSummary() {
                const quantities = [...rows.querySelectorAll('.bulk-quantity')].map((input) => Math.max(0, Number(input.value) || 0));
                const selected = quantities.filter((quantity) => quantity > 0);
                const total = selected.reduce((sum, quantity) => sum + quantity, 0);
                summary.textContent = selected.length
                    ? `${selected.length} variant option(s) selected · ${total.toLocaleString()} unit(s) to add`
                    : 'Enter an add quantity for at least one variant.';
                submit.disabled = selected.length === 0;
            }

            $('#bulkProduct').select2({
                width: '100%',
                placeholder: 'Search and select a product',
                allowClear: true,
                minimumInputLength: 2,
                ajax: {
                    url: searchProductsUrl,
                    dataType: 'json',
                    delay: 250,
                    data: (params) => ({ q: params.term || '' }),
                    processResults: (data) => ({ results: data.results }),
                    cache: true,
                },
            });
            $('#bulkProduct').on('select2:select select2:clear', renderRows);
            locationSelect.addEventListener('change', renderRows);
            document.getElementById('applyQuantityButton').addEventListener('click', () => {
                const quantity = Math.max(0, Number(document.getElementById('applyQuantity').value) || 0);
                rows.querySelectorAll('.bulk-quantity').forEach((input) => { input.value = quantity; });
                updateSummary();
            });
            rows.addEventListener('input', (event) => {
                if (event.target.matches('.bulk-quantity')) updateSummary();
            });
            document.getElementById('bulkStockForm').addEventListener('submit', (event) => {
                if (submit.disabled) {
                    event.preventDefault();
                    return;
                }
                submit.disabled = true;
                submit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving stock...';
            });

            if (productSelect.value) renderRows();
        })();
    </script>
    @endpush
</x-app-layout>
