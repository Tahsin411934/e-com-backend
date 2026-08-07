<x-app-layout>
    <x-confirm-delete />

    <div class="p-4">
        <x-entity-crud
            id="taxRate"
            title="Tax Rates"
            icon="fa-solid fa-percent"
            :columns="['Name','Rate','Type','Default','Status','Created At','Action']"
            :dtColumns="[
                ['data' => 'name'],
                ['data' => 'rate'],
                ['data' => 'type', 'orderable' => false],
                ['data' => 'is_default', 'orderable' => false, 'searchable' => false],
                ['data' => 'status'],
                ['data' => 'created_at'],
                ['data' => 'action', 'orderable' => false, 'searchable' => false],
            ]"
            ajaxUrl="{{ route('tax-rates.dataTable') }}"
            storeUrl="{{ route('tax-rates.store') }}"
            updateUrl="{{ route('tax-rates.update', ':id') }}"
            showUrl="{{ route('tax-rates.show', ':id') }}"
            destroyUrl="{{ route('tax-rates.destroy', ':id') }}"
            drawerTitle="Tax Rate"
            dataKey="tax_rate"
            idField="tax_rate_id"
            :order="[[5, 'desc']]"
        >
            <div class="mb-4">
                <x-form-input label="Tax Name" name="name" id="taxRate_name" placeholder="e.g. VAT 5%, Service Charge" required />
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <x-form-input label="Rate" name="rate" id="taxRate_rate" type="number" step="0.01" min="0" placeholder="e.g. 5.00" required />
                </div>
                <div>
                    <x-form-select label="Type" name="type" id="taxRate_type">
                        <option value="percentage">Percentage (%)</option>
                        <option value="fixed">Fixed (৳)</option>
                    </x-form-select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <x-form-select label="Applies To" name="applies_to" id="taxRate_applies_to">
                        <option value="all">All</option>
                        <option value="products">Products</option>
                        <option value="services">Services</option>
                        <option value="digital">Digital</option>
                    </x-form-select>
                </div>
                <div>
                    <x-form-select label="Status" name="status" id="taxRate_status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </x-form-select>
                </div>
            </div>
            <div class="mb-4">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_default" id="taxRate_is_default" value="1" class="rounded border-gray-300 text-primary focus:ring-primary">
                    Set as default tax rate
                </label>
            </div>
            <div class="mb-4">
                <label for="taxRate_description" class="block text-sm font-medium text-gray-700 mb-2">Description (optional)</label>
                <textarea name="description" id="taxRate_description" rows="2" placeholder="Brief description of this tax rate..."
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-sm"></textarea>
            </div>
        </x-entity-crud>
    </div>

    @push('scripts')
    <script>
        window.fillTaxRateForm = function(data) {
            $('#taxRate_name').val(data.name);
            $('#taxRate_rate').val(data.rate);
            $('#taxRate_type').val(data.type);
            $('#taxRate_applies_to').val(data.applies_to);
            $('#taxRate_status').val(data.status);
            $('#taxRate_is_default').prop('checked', data.is_default == 1 || data.is_default == true);
            $('#taxRate_description').val(data.description);
        };
    </script>
    @endpush
</x-app-layout>