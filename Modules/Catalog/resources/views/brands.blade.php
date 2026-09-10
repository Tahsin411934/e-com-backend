<x-app-layout>
    <x-entity-crud
        id="brand"
        title="Brand Catalog"
        icon="fa-solid fa-star"
        :columns="['Logo','Name','Slug','Status','Created At','Action']"
        :dtColumns="[
            ['data' => 'logo', 'orderable' => false, 'searchable' => false],
            ['data' => 'name'],
            ['data' => 'slug'],
            ['data' => 'status'],
            ['data' => 'created_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        ajaxUrl="{{ route('brands.dataTable') }}"
        storeUrl="{{ route('brands.store') }}"
        updateUrl="{{ route('brands.update', ':id') }}"
        showUrl="{{ route('brands.show', ':id') }}"
        destroyUrl="{{ route('brands.destroy', ':id') }}"
        drawerTitle="Brand"
        dataKey="data"
        idField="brand_id"
        :order="[[4, 'desc']]"
    >
        <div class="mb-4">
            <x-form-input label="Name" name="name" id="brand_name" placeholder="Brand Name" required />
        </div>
        <div class="mb-4">
            <x-form-input label="Slug" name="slug" id="brand_slug" placeholder="Brand Slug" required />
        </div>
        <div class="mb-4">
            <label for="brand_logo" class="block text-sm font-medium text-gray-700 mb-2">Logo</label>
            <input type="file" name="logo" id="brand_logo" accept="image/*" class="block w-full text-sm text-gray-900 bg-white border border-gray-300 rounded-lg cursor-pointer focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            <div id="brandLogoPreview" class="mt-3 hidden">
                <img src="" alt="Brand logo" class="h-14 w-14 rounded-lg border border-gray-200 object-contain bg-white">
            </div>
        </div>
        <div class="mb-4">
            <x-form-select label="Status" name="status" id="brand_status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </x-form-select>
        </div>
    </x-entity-crud>

    @push('scripts')
    <script>
        window.fillBrandForm = function(data) {
            $('#brand_name').val(data.name);
            $('#brand_slug').val(data.slug);
            $('#brand_status').val(data.status);
            if (data.logo_url) {
                $('#brandLogoPreview').removeClass('hidden').find('img').attr('src', data.logo_url);
            }
        };

        $(document).ready(function() {
            $('#brand_logo').on('change', function() {
                let file = this.files[0];
                if (file) {
                    $('#brandLogoPreview').removeClass('hidden').find('img').attr('src', URL.createObjectURL(file));
                } else {
                    $('#brandLogoPreview').addClass('hidden').find('img').attr('src', '');
                }
            });

            $('#brand_name').on('input', function() {
                if ($('#brand_hid').val() === '') {
                    let slug = $(this).val().toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
                    $('#brand_slug').val(slug);
                }
            });
        });
    </script>
    @endpush
</x-app-layout>