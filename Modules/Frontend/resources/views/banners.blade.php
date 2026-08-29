<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Banners') }}
        </h2>
    </x-slot>

    <x-entity-crud
        id="banner"
        title="Banners"
        icon="fa-solid fa-images"
        :columns="['Image','Title','Subtitle','Badge','Sort Order','Status','Created At','Action']"
        :dtColumns="[
            ['data' => 'banner_image', 'orderable' => false, 'searchable' => false],
            ['data' => 'title'],
            ['data' => 'subtitle'],
            ['data' => 'smtag'],
            ['data' => 'sort_order'],
            ['data' => 'status'],
            ['data' => 'created_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        ajaxUrl="{{ route('frontend.banners.dataTable') }}"
        storeUrl="{{ route('frontend.banners.store') }}"
        updateUrl="{{ route('frontend.banners.update', ':id') }}"
        showUrl="{{ route('frontend.banners.show', ':id') }}"
        destroyUrl="{{ route('frontend.banners.destroy', ':id') }}"
        drawerTitle="Banner"
        dataKey="data"
        idField="banner_id"
        :order="[[6, 'desc']]"
    >
        <div class="mb-4">
            <x-form-input label="Title" name="title" id="banner_title" placeholder="Banner Title" required />
        </div>
        <div class="mb-4">
            <x-form-input label="Subtitle" name="subtitle" id="banner_subtitle" placeholder="Banner Subtitle" />
        </div>
        <div class="mb-4">
            <x-form-input label="Badge / Tag (smtag)" name="smtag" id="banner_smtag" placeholder="e.g. New Arrival, Sale" />
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1" for="banner_image">Banner Image</label>
            <input type="file" name="banner_image" id="banner_image" accept="image/*"
                class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
            <div id="bannerImagePreview" class="hidden mt-2">
                <img src="" alt="Banner preview" class="w-32 h-20 object-cover rounded border" />
            </div>
        </div>
        <div class="mb-4">
            <x-form-input label="Primary Button Text" name="primary_btn" id="banner_primary_btn" placeholder="e.g. Shop Now" />
        </div>
        <div class="mb-4">
            <x-form-input label="Primary Button URL" name="primary_btn_url" id="banner_primary_btn_url" placeholder="e.g. /shop" />
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1" for="banner_primary_btn_color">Primary Button Color</label>
            <div class="flex items-center gap-3">
                <input type="color" name="primary_btn_color" id="banner_primary_btn_color" value="#1A462F"
                    class="h-10 w-16 rounded border border-gray-300 cursor-pointer p-1" />
                <span id="primary_btn_color_hex" class="text-sm text-gray-500">#1A462F</span>
            </div>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1" for="banner_primary_btn_text_color">Primary Button Text Color</label>
            <div class="flex items-center gap-3">
                <input type="color" name="primary_btn_text_color" id="banner_primary_btn_text_color" value="#ffffff"
                    class="h-10 w-16 rounded border border-gray-300 cursor-pointer p-1" />
                <span id="primary_btn_text_color_hex" class="text-sm text-gray-500">#ffffff</span>
            </div>
        </div>
        <div class="mb-4">
            <x-form-input label="Secondary Button Text" name="secondary_btn" id="banner_secondary_btn" placeholder="e.g. Learn More" />
        </div>
        <div class="mb-4">
            <x-form-input label="Secondary Button URL" name="secondary_btn_url" id="banner_secondary_btn_url" placeholder="e.g. /about" />
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1" for="banner_secondary_btn_color">Secondary Button Color</label>
            <div class="flex items-center gap-3">
                <input type="color" name="secondary_btn_color" id="banner_secondary_btn_color" value="#ffffff"
                    class="h-10 w-16 rounded border border-gray-300 cursor-pointer p-1" />
                <span id="secondary_btn_color_hex" class="text-sm text-gray-500">#ffffff</span>
            </div>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1" for="banner_secondary_btn_text_color">Secondary Button Text Color</label>
            <div class="flex items-center gap-3">
                <input type="color" name="secondary_btn_text_color" id="banner_secondary_btn_text_color" value="#1f2937"
                    class="h-10 w-16 rounded border border-gray-300 cursor-pointer p-1" />
                <span id="secondary_btn_text_color_hex" class="text-sm text-gray-500">#1f2937</span>
            </div>
        </div>
        <div class="mb-4">
            <x-form-input label="Sort Order" name="sort_order" id="banner_sort_order" type="number" value="0" />
        </div>
        <div class="mb-4">
            <x-form-select label="Status" name="status" id="banner_status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </x-form-select>
        </div>
    </x-entity-crud>

    @push('scripts')
    <script>
        // ===== Banner form fill =====
        window.fillBannerForm = function(data) {
            $('#banner_title').val(data.title);
            $('#banner_subtitle').val(data.subtitle || '');
            $('#banner_smtag').val(data.smtag || '');
            $('#banner_primary_btn').val(data.primary_btn || '');
            $('#banner_primary_btn_url').val(data.primary_btn_url || '');
            $('#banner_primary_btn_color').val(data.primary_btn_color || '#1A462F');
            $('#banner_primary_btn_text_color').val(data.primary_btn_text_color || '#ffffff');
            $('#banner_secondary_btn').val(data.secondary_btn || '');
            $('#banner_secondary_btn_url').val(data.secondary_btn_url || '');
            $('#banner_secondary_btn_color').val(data.secondary_btn_color || '#ffffff');
            $('#banner_secondary_btn_text_color').val(data.secondary_btn_text_color || '#1f2937');
            $('#banner_sort_order').val(data.sort_order || 0);
            $('#banner_status').val(data.status);

            // Show image preview if available
            if (data.banner_image_url) {
                $('#bannerImagePreview img').attr('src', data.banner_image_url);
                $('#bannerImagePreview').removeClass('hidden');
            } else {
                $('#bannerImagePreview').addClass('hidden');
            }
        };

        // Preview image on file select
        $(document).ready(function() {
            $('#banner_image').on('change', function() {
                var file = this.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#bannerImagePreview img').attr('src', e.target.result);
                        $('#bannerImagePreview').removeClass('hidden');
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
    @endpush
</x-app-layout>