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
            <label class="block text-sm font-medium text-gray-700 mb-1" for="banner_primary_btn_color_hex">Primary Button Color</label>
            <div class="flex items-center gap-3">
                <input type="color" id="banner_primary_btn_color" value="#1A462F" data-hex-input="banner_primary_btn_color_hex" aria-label="Pick primary button color"
                    class="h-10 w-16 shrink-0 rounded border border-gray-300 cursor-pointer p-1" />
                <input type="text" name="primary_btn_color" id="banner_primary_btn_color_hex" value="#1A462F"
                    data-color-input="banner_primary_btn_color" placeholder="#1A462F" maxlength="7" spellcheck="false" autocomplete="off"
                    pattern="#?([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})" title="Paste or type a hex color code, e.g. #1A462F"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono bg-white dark:bg-gray-700 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all" />
            </div>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1" for="banner_primary_btn_text_color_hex">Primary Button Text Color</label>
            <div class="flex items-center gap-3">
                <input type="color" id="banner_primary_btn_text_color" value="#ffffff" data-hex-input="banner_primary_btn_text_color_hex" aria-label="Pick primary button text color"
                    class="h-10 w-16 shrink-0 rounded border border-gray-300 cursor-pointer p-1" />
                <input type="text" name="primary_btn_text_color" id="banner_primary_btn_text_color_hex" value="#ffffff"
                    data-color-input="banner_primary_btn_text_color" placeholder="#ffffff" maxlength="7" spellcheck="false" autocomplete="off"
                    pattern="#?([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})" title="Paste or type a hex color code, e.g. #ffffff"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono bg-white dark:bg-gray-700 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all" />
            </div>
        </div>
        <div class="mb-4">
            <x-form-input label="Secondary Button Text" name="secondary_btn" id="banner_secondary_btn" placeholder="e.g. Learn More" />
        </div>
        <div class="mb-4">
            <x-form-input label="Secondary Button URL" name="secondary_btn_url" id="banner_secondary_btn_url" placeholder="e.g. /about" />
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1" for="banner_secondary_btn_color_hex">Secondary Button Color</label>
            <div class="flex items-center gap-3">
                <input type="color" id="banner_secondary_btn_color" value="#ffffff" data-hex-input="banner_secondary_btn_color_hex" aria-label="Pick secondary button color"
                    class="h-10 w-16 shrink-0 rounded border border-gray-300 cursor-pointer p-1" />
                <input type="text" name="secondary_btn_color" id="banner_secondary_btn_color_hex" value="#ffffff"
                    data-color-input="banner_secondary_btn_color" placeholder="#ffffff" maxlength="7" spellcheck="false" autocomplete="off"
                    pattern="#?([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})" title="Paste or type a hex color code, e.g. #ffffff"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono bg-white dark:bg-gray-700 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all" />
            </div>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1" for="banner_secondary_btn_text_color_hex">Secondary Button Text Color</label>
            <div class="flex items-center gap-3">
                <input type="color" id="banner_secondary_btn_text_color" value="#1f2937" data-hex-input="banner_secondary_btn_text_color_hex" aria-label="Pick secondary button text color"
                    class="h-10 w-16 shrink-0 rounded border border-gray-300 cursor-pointer p-1" />
                <input type="text" name="secondary_btn_text_color" id="banner_secondary_btn_text_color_hex" value="#1f2937"
                    data-color-input="banner_secondary_btn_text_color" placeholder="#1f2937" maxlength="7" spellcheck="false" autocomplete="off"
                    pattern="#?([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})" title="Paste or type a hex color code, e.g. #1f2937"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono bg-white dark:bg-gray-700 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all" />
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
        // ===== Hex color helpers (normalize + validate) =====
        function bannerNormalizeHex(value) {
            var v = (value || '').trim().toLowerCase();
            if (!v) return '';
            if (v.charAt(0) !== '#') v = '#' + v;
            if (/^#[0-9a-f]{3}$/.test(v)) {
                v = '#' + v.charAt(1) + v.charAt(1) + v.charAt(2) + v.charAt(2) + v.charAt(3) + v.charAt(3);
            }
            return v;
        }

        function bannerIsValidHex(value) {
            return /^#[0-9a-f]{6}$/.test(bannerNormalizeHex(value));
        }

        // ===== Banner form fill =====
        window.fillBannerForm = function(data) {
            $('#banner_title').val(data.title);
            $('#banner_subtitle').val(data.subtitle || '');
            $('#banner_smtag').val(data.smtag || '');
            $('#banner_primary_btn').val(data.primary_btn || '');
            $('#banner_primary_btn_url').val(data.primary_btn_url || '');
            var primaryColor = bannerNormalizeHex(data.primary_btn_color) || '#1A462F';
            var primaryTextColor = bannerNormalizeHex(data.primary_btn_text_color) || '#ffffff';
            $('#banner_primary_btn_color').val(primaryColor);
            $('#banner_primary_btn_color_hex').val(primaryColor);
            $('#banner_primary_btn_text_color').val(primaryTextColor);
            $('#banner_primary_btn_text_color_hex').val(primaryTextColor);
            $('#banner_secondary_btn').val(data.secondary_btn || '');
            $('#banner_secondary_btn_url').val(data.secondary_btn_url || '');
            var secondaryColor = bannerNormalizeHex(data.secondary_btn_color) || '#ffffff';
            var secondaryTextColor = bannerNormalizeHex(data.secondary_btn_text_color) || '#1f2937';
            $('#banner_secondary_btn_color').val(secondaryColor);
            $('#banner_secondary_btn_color_hex').val(secondaryColor);
            $('#banner_secondary_btn_text_color').val(secondaryTextColor);
            $('#banner_secondary_btn_text_color_hex').val(secondaryTextColor);
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

        // Preview image on file select + color picker <-> hex input sync
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

            // Two-way sync: color picker <-> hex text input (paste hex codes directly)
            var hexErrorClasses = 'border-rose-500 ring-2 ring-rose-200';
            $('#bannerForm input[type="color"][data-hex-input]').each(function() {
                var picker = $(this);
                var hexInput = $('#' + picker.data('hex-input'));
                if (!hexInput.length) return;

                // Color picker -> text input
                picker.on('input change', function() {
                    hexInput.val(picker.val()).removeClass(hexErrorClasses);
                });

                // Text input (typed/pasted) -> color picker when valid
                hexInput.on('input', function() {
                    var raw = hexInput.val().trim();
                    if (raw === '') {
                        hexInput.removeClass(hexErrorClasses);
                        return;
                    }
                    if (bannerIsValidHex(raw)) {
                        var normalized = bannerNormalizeHex(raw);
                        picker.val(normalized);
                        hexInput.val(normalized).removeClass(hexErrorClasses);
                    } else {
                        hexInput.addClass(hexErrorClasses);
                    }
                });

                // Normalize on leave: auto #, expand 3-digit, lowercase; revert if invalid
                hexInput.on('change', function() {
                    var raw = hexInput.val().trim();
                    if (raw !== '' && bannerIsValidHex(raw)) {
                        hexInput.val(bannerNormalizeHex(raw)).removeClass(hexErrorClasses);
                    } else if (raw === '') {
                        hexInput.removeClass(hexErrorClasses);
                    } else {
                        hexInput.val(picker.val()).removeClass(hexErrorClasses);
                    }
                });
            });

            // Keep picker + hex input in sync after "Add New" resets the form
            $('#bannerForm').on('reset', function() {
                window.setTimeout(function() {
                    $('#bannerForm input[type="color"][data-hex-input]').each(function() {
                        var picker = $(this);
                        var hexInput = $('#' + picker.data('hex-input'));
                        if (hexInput.length) {
                            hexInput.val(picker.val()).removeClass(hexErrorClasses);
                        }
                    });
                }, 0);
            });
        });
    </script>
    @endpush
</x-app-layout>