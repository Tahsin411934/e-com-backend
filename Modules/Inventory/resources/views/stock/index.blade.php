<x-app-layout>
    <x-entity-crud
        id="inventory-stock"
        title="Inventory Stock"
        icon="fa-solid fa-boxes"
        :columns="['Location','Store','Product','Variant','Qty On Hand','Qty Reserved','Available','Reorder Point','Low Stock','Last Updated','Action']"
        :dtColumns="[
            ['data' => 'location_name'],
            ['data' => 'store_name'],
            ['data' => 'product_name'],
            ['data' => 'variant_name'],
            ['data' => 'quantity_on_hand'],
            ['data' => 'quantity_reserved'],
            ['data' => 'available_quantity'],
            ['data' => 'reorder_point'],
            ['data' => 'low_stock'],
            ['data' => 'updated_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        ajaxUrl="{{ route('inventory-stock.dataTable') }}"
        storeUrl="{{ route('inventory-stock.store') }}"
        updateUrl="{{ route('inventory-stock.update', ':id') }}"
        showUrl="{{ route('inventory-stock.show', ':id') }}"
        destroyUrl="{{ route('inventory-stock.destroy', ':id') }}"
        drawerTitle="Stock Record"
        dataKey="stock"
        idField="stock_id"
        :order="[[7, 'desc']]"
    >
        <div class="mb-4">
            <x-form-select label="Variant" name="variant_id" id="stock_variant_id">
                <option value="" disabled selected>Select a variant</option>
                @foreach($variants ?? [] as $variant)
                    <option value="{{ $variant->id }}" data-options='@json($variant->options->map(fn($option) => ["id" => $option->id, "name" => $option->color_name, "sku" => $option->sku]))'>{{ $variant->product?->name ?? 'Unknown Product' }} - {{ $variant->name }} ({{ $variant->sku }})</option>
                @endforeach
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-select label="Color Option" name="variant_option_id" id="stock_variant_option_id">
                <option value="">Parent variant stock</option>
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-select label="Location" name="location_id" id="stock_location_id">
                <option value="" disabled selected>Select a location</option>
                @foreach($locations ?? [] as $location)
                    <option value="{{ $location['id'] }}">{{ $location['name'] }} ({{ $location['store']['name'] ?? '' }})</option>
                @endforeach
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-input label="Quantity On Hand" name="quantity_on_hand" id="stock_quantity_on_hand" type="number" min="0" placeholder="0" required />
        </div>
        <div class="mb-4">
            <x-form-input label="Quantity Reserved" name="quantity_reserved" id="stock_quantity_reserved" type="number" min="0" placeholder="0" required />
        </div>
        <div class="mb-4">
            <x-form-input label="Reorder Point" name="reorder_point" id="stock_reorder_point" type="number" min="0" placeholder="0" required />
        </div>
    </x-entity-crud>

    @push('scripts')
    <script>
        window.fillInventoryStockForm = function(data) {
            $('#stock_variant_id').val(data.variant_id);
            refreshStockOptions(data.variant_id, data.variant_option_id);
            $('#stock_location_id').val(data.location_id);
            $('#stock_quantity_on_hand').val(data.quantity_on_hand);
            $('#stock_quantity_reserved').val(data.quantity_reserved);
            $('#stock_reorder_point').val(data.reorder_point);
        };

        function refreshStockOptions(variantId, selectedId = null) {
            const option = $('#stock_variant_id option[value="' + variantId + '"]');
            const options = option.data('options') || [];
            const select = $('#stock_variant_option_id').empty().append('<option value="">Parent variant stock</option>');
            options.forEach(item => select.append(new Option(item.name + (item.sku ? ' (' + item.sku + ')' : ''), item.id, false, String(item.id) === String(selectedId))));
        }

        $('#stock_variant_id').on('change', function() { refreshStockOptions(this.value); });
    </script>
    @endpush
</x-app-layout>