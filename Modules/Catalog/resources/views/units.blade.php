<x-app-layout>
    <x-entity-crud
        id="unit"
        title="Unit Catalog"
        icon="fa-solid fa-ruler-combined"
        :columns="['Name','Slug','Short Name','Type','Status','Created At','Action']"
        :dtColumns="[
            ['data' => 'name'],
            ['data' => 'slug'],
            ['data' => 'short_name'],
            ['data' => 'type'],
            ['data' => 'status'],
            ['data' => 'created_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        ajaxUrl="{{ route('units.dataTable') }}"
        storeUrl="{{ route('units.store') }}"
        updateUrl="{{ route('units.update', ':id') }}"
        showUrl="{{ route('units.show', ':id') }}"
        destroyUrl="{{ route('units.destroy', ':id') }}"
        drawerTitle="Unit"
        dataKey="data"
        idField="unit_id"
        :order="[[5, 'desc']]"
    >
        <div class="mb-4">
            <x-form-input label="Name" name="name" id="unit_name" placeholder="Unit Name" required />
        </div>
        <div class="mb-4">
            <x-form-input label="Slug" name="slug" id="unit_slug" placeholder="Unit Slug" required />
        </div>
        <div class="mb-4">
            <x-form-input label="Short Name" name="short_name" id="unit_short_name" placeholder="e.g. pcs, kg, m" required />
        </div>
        <div class="mb-4">
            <x-form-select label="Type" name="type" id="unit_type">
                <option value="quantity">Quantity</option>
                <option value="weight">Weight</option>
                <option value="volume">Volume</option>
                <option value="length">Length</option>
                <option value="area">Area</option>
                <option value="time">Time</option>
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-select label="Status" name="status" id="unit_status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </x-form-select>
        </div>
    </x-entity-crud>

    @push('scripts')
<script>
    window.fillUnitForm = function(data) {
        $('#unit_name').val(data.name);
        $('#unit_slug').val(data.slug);
        $('#unit_short_name').val(data.short_name);
        $('#unit_type').val(data.type);
        $('#unit_status').val(data.status);
    };

    $(document).ready(function() {
        // We use delegation on the body so it works even if the 
        // form is dynamically loaded/replaced by your CRUD component
        $(document).on('keyup input', '#unit_name', function() {
            // Only auto-generate if we are in "Create" mode.
            // Check your x-entity-crud component: it likely puts the ID 
            // in a hidden field. We check if that value is missing/empty.
            let idValue = $('input[name="unit_id"]').val() || $('#unit_id').val();
            
            if (!idValue) { 
                let text = $(this).val();
                let slug = text.toLowerCase()
                    .replace(/[^a-z0-9- ]/g, '') // Keep alphanumeric, hyphens, and spaces
                    .replace(/\s+/g, '-')        // Replace spaces with -
                    .replace(/-+/g, '-')         // Replace multiple - with single -
                    .replace(/^-+|-+$/g, '');    // Trim - from ends
                
                $('#unit_slug').val(slug);
            }
        });
    });
</script>
@endpush
</x-app-layout>