<x-app-layout>
<div class="py-6 px-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Barcode & Label Printing</h1>
            <p class="text-sm text-gray-500 mt-0.5">Search a product, its variants load below. All pre-selected. Deselect any you don't want.</p>
        </div>
        <button id="printBarcodes" disabled
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-primary to-primary-hover text-white text-sm font-semibold rounded-lg shadow-sm hover:from-primary-hover hover:to-primary-hover disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-150">
            <i class="fas fa-print text-xs"></i> Print Selected
        </button>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
        <div class="xl:col-span-3 space-y-5">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wider">Find Product</h3>
                </div>
                <div class="p-4 space-y-4">
                    <div class="relative" id="searchWrapper">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Product Name</label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                            <input type="text" id="searchInput" placeholder="Type to search..."
                                class="w-full pl-9 pr-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary" autocomplete="off">
                            <div id="autocompleteDropdown" class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-50 hidden max-h-64 overflow-y-auto"></div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Brand</label>
                        <select id="brandFilter" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5">
                            <option value="">All</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Category</label>
                        <select id="categoryFilter" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5">
                            <option value="">All</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button id="searchBtn" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">
                        <i class="fas fa-search mr-1.5 text-xs"></i> Search
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wider">Selected</h3>
                </div>
                <div class="p-4 text-center">
                    <div id="selectedCount" class="text-3xl font-bold text-primary">0</div>
                    <p class="text-xs text-gray-500 mt-1">variants</p>
                    <button id="clearSelection" class="mt-2 text-xs text-red-500 hover:text-red-700 font-medium hidden">
                        <i class="fas fa-times mr-1"></i> Clear All
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wider">Print Settings</h3>
                </div>
                <div class="p-4 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Paper</label>
                        <select id="paperSize" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5">
                            <option value="letter">Letter</option>
                            <option value="a4" selected>A4</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Label Size</label>
                        <select id="labelSize" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5">
                            <option value="1x1">1" x 1"</option>
                            <option value="1x2">1" x 2"</option>
                            <option value="2x2" selected>2" x 2"</option>
                            <option value="2x3">2" x 3"</option>
                            <option value="3x4">3" x 4"</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Copies</label>
                        <input type="number" id="defaultCopies" value="1" min="1" max="100" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5">
                    </div>
                </div>
            </div>
        </div>

        <div class="xl:col-span-9">
            <div id="productsContainer" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div id="emptyState" class="flex flex-col items-center justify-center py-16 px-6">
                    <div class="w-20 h-20 bg-primary-light rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-qrcode text-3xl text-primary-light"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-1">Search for a Product</h3>
                    <p class="text-sm text-gray-400">Search and click a product to see its variants</p>
                </div>

                <div id="productsList" class="hidden">
                    <div id="selectedProductInfo" class="px-5 py-3 bg-primary-light border-b border-primary-light flex items-center gap-3"></div>
                    <div class="flex items-center justify-between px-5 py-3 bg-gray-50 border-b border-gray-200">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" id="selectAll" class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary cursor-pointer">
                            <label for="selectAll" class="text-sm font-medium text-gray-700 cursor-pointer select-none">Select All</label>
                        </div>
                        <span id="resultCount" class="text-xs text-gray-500 bg-white px-2.5 py-1 rounded-full border border-gray-200"></span>
                    </div>
                    <div id="productItems" class="divide-y divide-gray-100 max-h-[550px] overflow-y-auto"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="selectionBar" class="fixed bottom-0 left-52 right-0 bg-white border-t border-gray-200 shadow-lg px-6 py-3 hidden z-40">
    <div class="max-w-7xl mx-auto flex items-center justify-between">
        <div>
            <span class="text-sm font-semibold text-primary"><span id="selectedCountBar">0</span> variants selected</span>
        </div>
        <div class="flex items-center gap-3">
            <button id="clearSelectionBottom" class="text-sm text-gray-500 hover:text-red-600 font-medium">Clear</button>
            <button id="printBarcodesBottom" class="inline-flex items-center gap-2 px-5 py-2 bg-gradient-to-r from-primary to-primary-hover text-white text-sm font-semibold rounded-lg shadow-sm hover:from-primary-hover hover:to-primary-hover">
                <i class="fas fa-print text-xs"></i> Print
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    const selectedVariants = new Map();
    let autocompleteTimeout = null;

    function getCopies() { return parseInt($('#defaultCopies').val()) || 1; }

    // Autocomplete
    $('#searchInput').on('input', function() {
        clearTimeout(autocompleteTimeout);
        const val = $(this).val();
        if (val.length < 1) { $('#autocompleteDropdown').addClass('hidden'); return; }
        autocompleteTimeout = setTimeout(() => {
            $.get('{{ route("barcode-print.autocomplete") }}', { q: val }, function(data) {
                const dd = $('#autocompleteDropdown'); dd.empty();
                if (!data.length) { dd.addClass('hidden'); return; }
                data.forEach(p => {
                    dd.append(`<div class="px-4 py-2.5 hover:bg-primary-light cursor-pointer border-b border-gray-50 flex items-center gap-3 autocomplete-item" data-id="${p.id}">
                        <i class="fas fa-box text-gray-300 text-xs"></i>
                        <div><div class="text-sm font-medium text-gray-800">${p.name}</div>${p.brand ? `<div class="text-xs text-gray-400">${p.brand}</div>` : ''}</div>
                    </div>`);
                });
                dd.removeClass('hidden');
            });
        }, 300);
    });

    $(document).on('click', '.autocomplete-item', function() {
        const id = $(this).data('id');
        $('#searchInput').val($(this).find('.text-sm.font-medium').text().trim());
        $('#autocompleteDropdown').addClass('hidden');
        loadProductVariants(id);
    });

    $(document).click(function(e) {
        if (!$(e.target).closest('#searchWrapper').length) $('#autocompleteDropdown').addClass('hidden');
    });

    // Search
    function searchProducts() {
        $.ajax({
            url: '{{ route("barcode-print.search") }}',
            method: 'GET',
            data: { search: $('#searchInput').val(), brand_id: $('#brandFilter').val(), category_id: $('#categoryFilter').val() },
            success: function(products) {
                if (products.length) {
                    $('#emptyState').addClass('hidden'); $('#productsList').removeClass('hidden');
                    $('#selectedProductInfo').html(`<i class="fas fa-list text-primary"></i><span class="text-sm text-primary">Click a product below to see its variants</span>`);
                    $('#resultCount').text(`${products.length} product(s)`);
                    $('#productItems').empty();
                    products.forEach(p => {
                        $('#productItems').append(`
                            <div class="flex items-center gap-4 px-5 py-4 hover:bg-primary-light cursor-pointer select-product" data-id="${p.id}">
                                <i class="fas fa-box text-gray-300"></i>
                                <div class="flex-1"><span class="text-sm font-semibold text-gray-800">${p.name}</span>${p.brand ? `<span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full ml-2">${p.brand.name}</span>` : ''}</div>
                                <i class="fas fa-chevron-right text-gray-300 text-xs"></i>
                            </div>
                        `);
                    });
                } else { $('#emptyState').removeClass('hidden'); $('#productsList').addClass('hidden'); }
            }
        });
    }

    $(document).on('click', '.select-product', function() {
        const id = $(this).data('id');
        $('#searchInput').val($(this).find('.text-sm.font-semibold').text().trim());
        loadProductVariants(id);
    });

    function loadProductVariants(productId) {
        selectedVariants.clear();
        $.get(`/barcode-print/variants/${productId}`, function(data) {
            $('#emptyState').addClass('hidden'); $('#productsList').removeClass('hidden');
            $('#selectedProductInfo').html(`<i class="fas fa-box text-primary"></i><span class="text-sm font-semibold text-primary-hover">${data.name}</span>${data.brand ? `<span class="text-xs bg-primary-light text-primary px-2 py-0.5 rounded-full ml-2">${data.brand.name}</span>` : ''}<span class="text-xs bg-primary-light text-primary px-2 py-0.5 rounded-full ml-auto">${data.variants.length} variant(s)</span>`);
            renderVariants(data.variants);
        });
    }

    function renderVariants(variants) {
        $('#productItems').empty();
        const copies = getCopies();

        // Pre-select ALL variants automatically
        variants.forEach(v => {
            const id = v.id;
            selectedVariants.set(id, { variant_id: id, copies });
            
            $('#productItems').append(`
                <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50 bg-primary-light/50">
                    <input type="checkbox" class="variant-checkbox w-4 h-4 rounded border-gray-300 text-primary" checked data-id="${id}">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-gray-800">${v.name}</span>
                            <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">${v.barcode || v.sku || 'VAR-'+id}</span>
                        </div>
                        <div class="text-xs text-gray-400">SKU: ${v.sku}${v.sale_price ? ' | $'+parseFloat(v.sale_price).toFixed(2) : ''}</div>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-xs text-gray-500">Copies:</label>
                        <input type="number" class="copies-input w-16 border border-gray-300 rounded-lg px-2 py-1.5 text-sm text-center" value="${copies}" min="1" max="100" data-id="${id}">
                    </div>
                </div>
            `);
        });

        $('#selectAll').prop('checked', true);
        $('#resultCount').text(`${variants.length} variant(s) - all selected`);
        updateSelection();
    }

    function updateSelection() {
        const count = selectedVariants.size;
        $('#selectedCount').text(count); $('#selectedCountBar').text(count);
        $('#selectionBar').toggleClass('hidden', count === 0);
        $('#printBarcodes').prop('disabled', count === 0);
        $('#clearSelection').toggleClass('hidden', count === 0);
    }

    $(document).on('change', '.variant-checkbox', function() {
        const id = parseInt($(this).data('id'));
        if ($(this).is(':checked')) {
            selectedVariants.set(id, { variant_id: id, copies: parseInt($(this).closest('.flex').find('.copies-input').val()) || 1 });
            $(this).closest('.flex').addClass('bg-primary-light/50');
        } else {
            selectedVariants.delete(id);
            $(this).closest('.flex').removeClass('bg-primary-light/50');
        }
        updateSelection();
    });

    $(document).on('change', '.copies-input', function() {
        const id = parseInt($(this).data('id'));
        const copies = parseInt($(this).val()) || 1;
        if (selectedVariants.has(id)) selectedVariants.set(id, { variant_id: id, copies });
    });

    $('#selectAll').change(function() {
        $('.variant-checkbox').each(function() { $(this).prop('checked', $('#selectAll').is(':checked')).trigger('change'); });
    });

    $('#clearSelection, #clearSelectionBottom').click(function() {
        selectedVariants.clear();
        $('.variant-checkbox').prop('checked', false).closest('.flex').removeClass('bg-primary-light/50');
        $('#selectAll').prop('checked', false);
        updateSelection();
    });

    $('#searchBtn').click(searchProducts);
    $('#searchInput').keypress(function(e) { if (e.which === 13) searchProducts(); });

    function printBarcodes() {
        if (!selectedVariants.size) return;
        const items = Array.from(selectedVariants.values());
        const form = $('<form>', { method: 'POST', action: '{{ route("barcode-print.print") }}', target: '_blank' });
        form.append($('<input>', { type: 'hidden', name: '_token', value: '{{ csrf_token() }}' }));
        form.append($('<input>', { type: 'hidden', name: 'paper_size', value: $('#paperSize').val() }));
        form.append($('<input>', { type: 'hidden', name: 'label_size', value: $('#labelSize').val() }));
        items.forEach((v, i) => {
            form.append($('<input>', { type: 'hidden', name: `variants[${i}][variant_id]`, value: v.variant_id }));
            form.append($('<input>', { type: 'hidden', name: `variants[${i}][copies]`, value: v.copies }));
        });
        form.appendTo('body').submit().remove();
    }

    $('#printBarcodes, #printBarcodesBottom').click(printBarcodes);
});
</script>
@endpush
</x-app-layout>