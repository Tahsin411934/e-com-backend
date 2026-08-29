<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Navbar Items') }}
        </h2>
    </x-slot>

    <x-entity-crud
        id="navbar_item"
        title="Navbar Items"
        icon="fa-solid fa-bars"
        :columns="['Name','Slug','URL','Icon','Sort Order','Status','Created At','Action']"
        :dtColumns="[
            ['data' => 'name'],
            ['data' => 'slug'],
            ['data' => 'url'],
            ['data' => 'icon'],
            ['data' => 'sort_order'],
            ['data' => 'status'],
            ['data' => 'created_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        ajaxUrl="{{ route('frontend.nav-items.navbar.dataTable') }}"
        storeUrl="{{ route('frontend.nav-items.navbar.store') }}"
        updateUrl="{{ route('frontend.nav-items.navbar.update', ':id') }}"
        showUrl="{{ route('frontend.nav-items.navbar.show', ':id') }}"
        destroyUrl="{{ route('frontend.nav-items.navbar.destroy', ':id') }}"
        drawerTitle="Navbar Item"
        dataKey="data"
        idField="navbar_item_id"
        :order="[[6, 'desc']]"
    >
        <div class="mb-4">
            <x-form-input label="Name" name="name" id="navbar_item_name" placeholder="Item Name" required />
        </div>
        <div class="mb-4">
            <x-form-input label="Slug" name="slug" id="navbar_item_slug" placeholder="Item Slug" required />
        </div>
        <div class="mb-4">
            <x-form-input label="URL (optional)" name="url" id="navbar_item_url" placeholder="/example" />
        </div>
        <div class="mb-4">
            <x-form-input label="Icon Class (optional)" name="icon" id="navbar_item_icon" placeholder="fa-solid fa-home" />
        </div>
        <div class="mb-4">
            <x-form-input label="Sort Order" name="sort_order" id="navbar_item_sort_order" type="number" value="0" />
        </div>
        <div class="mb-4">
            <x-form-select label="Status" name="status" id="navbar_item_status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </x-form-select>
        </div>
    </x-entity-crud>

    @push('scripts')
    <script>
        // ===== Navbar Item form fill =====
        window.fillNavbaritemForm = function(data) {
            $('#navbar_item_name').val(data.name);
            $('#navbar_item_slug').val(data.slug);
            $('#navbar_item_url').val(data.url || '');
            $('#navbar_item_icon').val(data.icon || '');
            $('#navbar_item_sort_order').val(data.sort_order || 0);
            $('#navbar_item_status').val(data.status);
        };

        $(document).ready(function() {
            // Auto-generate slug from name
            $('#navbar_item_name').on('input', function() {
                if ($('#navbar_item_id').val() === '') {
                    let slug = $(this).val().toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
                    $('#navbar_item_slug').val(slug);
                }
            });
        });
    </script>
    @endpush
</x-app-layout>