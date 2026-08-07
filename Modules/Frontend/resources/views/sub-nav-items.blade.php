<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Subnavbar Items') }} @if(request('navbar_item_id')) - <span id="parentNavName" class="text-primary"></span> @endif
        </h2>
    </x-slot>

    <div class="mb-4">
        <a href="{{ route('frontend.nav-items.index') }}" class="text-sm text-primary hover:text-primary-hover">
            <i class="fas fa-arrow-left mr-1"></i> Back to Navbar Items
        </a>
    </div>

    <x-entity-crud
        id="subnavbar_item"
        title="Subnavbar Items"
        icon="fa-solid fa-list"
        :columns="['Parent','Name','Slug','URL','Icon','Sort Order','Status','Created At','Action']"
        :dtColumns="[
            ['data' => 'parent_navbar'],
            ['data' => 'name'],
            ['data' => 'slug'],
            ['data' => 'url'],
            ['data' => 'icon'],
            ['data' => 'sort_order'],
            ['data' => 'status'],
            ['data' => 'created_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        ajaxUrl="{{ route('frontend.nav-items.subnavbar.dataTable') }}{{ request('navbar_item_id') ? '?navbar_item_id=' . request('navbar_item_id') : '' }}"
        storeUrl="{{ route('frontend.nav-items.subnavbar.store') }}"
        updateUrl="{{ route('frontend.nav-items.subnavbar.update', ':id') }}"
        showUrl="{{ route('frontend.nav-items.subnavbar.show', ':id') }}"
        destroyUrl="{{ route('frontend.nav-items.subnavbar.destroy', ':id') }}"
        drawerTitle="Subnavbar Item"
        dataKey="subnavbar_item"
        idField="subnavbar_item_id"
        :order="[[7, 'desc']]"
    >
        <div class="mb-4">
            <x-form-select label="Parent Navbar Item" name="navbar_item_id" id="subnavbar_item_navbar_item_id" required>
                <option value="">Select Parent Item</option>
            </x-form-select>
        </div>
        <div class="mb-4">
            <x-form-input label="Name" name="name" id="subnavbar_item_name" placeholder="Sub Item Name" required />
        </div>
        <div class="mb-4">
            <x-form-input label="Slug" name="slug" id="subnavbar_item_slug" placeholder="Sub Item Slug" required />
        </div>
        <div class="mb-4">
            <x-form-input label="URL (optional)" name="url" id="subnavbar_item_url" placeholder="/example" />
        </div>
        <div class="mb-4">
            <x-form-input label="Icon Class (optional)" name="icon" id="subnavbar_item_icon" placeholder="fa-solid fa-star" />
        </div>
        <div class="mb-4">
            <x-form-input label="Sort Order" name="sort_order" id="subnavbar_item_sort_order" type="number" value="0" />
        </div>
        <div class="mb-4">
            <x-form-select label="Status" name="status" id="subnavbar_item_status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </x-form-select>
        </div>
    </x-entity-crud>

    @push('scripts')
    <script>
        // ===== Subnavbar Item form fill =====
        window.fillSubnavbaritemForm = function(data) {
            $('#subnavbar_item_navbar_item_id').val(data.navbar_item_id);
            $('#subnavbar_item_name').val(data.name);
            $('#subnavbar_item_slug').val(data.slug);
            $('#subnavbar_item_url').val(data.url || '');
            $('#subnavbar_item_icon').val(data.icon || '');
            $('#subnavbar_item_sort_order').val(data.sort_order || 0);
            $('#subnavbar_item_status').val(data.status);
        };

        // ===== Load navbar items into select dropdown =====
        function loadNavbarItemsSelect() {
            $.get('{{ route("frontend.nav-items.navbar.list") }}', function(res) {
                if (res.status === 'success' && res.navbar_items) {
                    const $select = $('#subnavbar_item_navbar_item_id');
                    const currentVal = $select.val();
                    $select.find('option:not(:first)').remove();
                    res.navbar_items.forEach(function(item) {
                        $select.append(`<option value="${item.id}">${item.name}</option>`);
                    });
                    if (currentVal) $select.val(currentVal);

                    // Auto-select filtered navbar_item_id if present
                    const urlParams = new URLSearchParams(window.location.search);
                    const navbarItemId = urlParams.get('navbar_item_id');
                    if (navbarItemId) {
                        $select.val(navbarItemId);
                        // Show parent name in header
                        const selected = res.navbar_items.find(i => i.id == navbarItemId);
                        if (selected) {
                            $('#parentNavName').text('(' + selected.name + ')');
                        }
                    }
                }
            });
        }

        $(document).ready(function() {
            // Auto-generate slug from name
            $('#subnavbar_item_name').on('input', function() {
                if ($('#subnavbar_item_id').val() === '') {
                    let slug = $(this).val().toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
                    $('#subnavbar_item_slug').val(slug);
                }
            });

            // Load navbar items dropdown
            loadNavbarItemsSelect();
        });
    </script>
    @endpush
</x-app-layout>