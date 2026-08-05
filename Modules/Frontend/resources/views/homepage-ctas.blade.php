<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Homepage CTAs') }}
        </h2>
    </x-slot>

    <x-entity-crud
        id="cta"
        title="Homepage CTAs"
        icon="fa-solid fa-bullhorn"
        :columns="['Image','Title','Style','Button Text','Sort Order','Status','Created At','Action']"
        :dtColumns="[
            ['data' => 'image', 'orderable' => false, 'searchable' => false],
            ['data' => 'title'],
            ['data' => 'cta_style'],
            ['data' => 'button_text'],
            ['data' => 'sort_order'],
            ['data' => 'status'],
            ['data' => 'created_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        ajaxUrl="{{ route('frontend.ctas.dataTable') }}"
        storeUrl="{{ route('frontend.ctas.store') }}"
        updateUrl="{{ route('frontend.ctas.update', ':id') }}"
        showUrl="{{ route('frontend.ctas.show', ':id') }}"
        destroyUrl="{{ route('frontend.ctas.destroy', ':id') }}"
        drawerTitle="CTA"
        dataKey="cta"
        idField="cta_id"
        :order="[[5, 'asc']]"
    >
        <!-- CTA Style Selector -->
        <div class="mb-4">
            <x-form-select label="CTA Style" name="cta_style" id="cta_style">
                <option value="style1">Style 1 - Standard (Left aligned with image)</option>
                <option value="style2">Style 2 - Banner with overlay (Full width background)</option>
                <option value="style3">Style 3 - Centered with badge</option>
                <option value="style4">Style 4 - Split layout (Image right, content left)</option>
                <option value="style5">Style 5 - Features grid with CTA</option>
            </x-form-select>
        </div>

        <div class="mb-4">
            <x-form-input label="Title" name="title" id="cta_title" placeholder="CTA Title (optional)" />
        </div>
        <div class="mb-4">
            <x-form-input label="Subtitle" name="subtitle" id="cta_subtitle" placeholder="CTA Subtitle (optional)" />
        </div>
        <div class="mb-4">
            <x-form-textarea label="Description" name="description" id="cta_description" placeholder="CTA Description (optional)" rows="3" />
        </div>

        <!-- Image Upload -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1" for="cta_image">CTA Image (optional)</label>
            <input type="file" name="image" id="cta_image" accept="image/*"
                class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
            <div id="ctaImagePreview" class="hidden mt-2">
                <img src="" alt="CTA preview" class="w-32 h-20 object-cover rounded border" />
            </div>
        </div>

        <!-- Banner Image Upload -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1" for="cta_banner_image">Banner Image (optional - for Style 2)</label>
            <input type="file" name="banner_image" id="cta_banner_image" accept="image/*"
                class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
            <div id="ctaBannerImagePreview" class="hidden mt-2">
                <img src="" alt="Banner preview" class="w-40 h-24 object-cover rounded border" />
            </div>
        </div>

        <div class="mb-4">
            <x-form-input label="Button Text" name="button_text" id="cta_button_text" placeholder="e.g. Shop Now (optional)" />
        </div>
        <div class="mb-4">
            <x-form-input label="Button Link" name="button_link" id="cta_button_link" placeholder="e.g. /sale/summer (optional)" />
        </div>

        <!-- Color fields in a 2-column grid -->
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="cta_background_color">Background Color</label>
                <div class="flex items-center gap-2">
                    <input type="color" name="background_color" id="cta_background_color" value="#f8f9fa"
                        class="w-10 h-10 p-0.5 border border-gray-300 rounded cursor-pointer" />
                    <input type="text" id="cta_background_color_hex" value="#f8f9fa" maxlength="20"
                        class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" />
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="cta_text_color">Text Color</label>
                <div class="flex items-center gap-2">
                    <input type="color" name="text_color" id="cta_text_color" value="#1f2937"
                        class="w-10 h-10 p-0.5 border border-gray-300 rounded cursor-pointer" />
                    <input type="text" id="cta_text_color_hex" value="#1f2937" maxlength="20"
                        class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" />
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="cta_button_color">Button Color</label>
                <div class="flex items-center gap-2">
                    <input type="color" name="button_color" id="cta_button_color" value="#1e3a8a"
                        class="w-10 h-10 p-0.5 border border-gray-300 rounded cursor-pointer" />
                    <input type="text" id="cta_button_color_hex" value="#1e3a8a" maxlength="20"
                        class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" />
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="cta_button_text_color">Button Text Color</label>
                <div class="flex items-center gap-2">
                    <input type="color" name="button_text_color" id="cta_button_text_color" value="#ffffff"
                        class="w-10 h-10 p-0.5 border border-gray-300 rounded cursor-pointer" />
                    <input type="text" id="cta_button_text_color_hex" value="#ffffff" maxlength="20"
                        class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" />
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="cta_overlay_color">Overlay Color (Style 2)</label>
                <div class="flex items-center gap-2">
                    <input type="color" name="overlay_color" id="cta_overlay_color" value="#000000"
                        class="w-10 h-10 p-0.5 border border-gray-300 rounded cursor-pointer" />
                    <input type="text" id="cta_overlay_color_hex" value="rgba(0,0,0,0.4)" maxlength="20"
                        class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" />
                </div>
            </div>
        </div>

        <!-- Badge Fields (Style 3) -->
        <div class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">Badge Settings (Style 3)</h4>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-form-input label="Badge Text" name="badge_text" id="cta_badge_text" placeholder="e.g. SALE!" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="cta_badge_color">Badge Color</label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="badge_color" id="cta_badge_color" value="#ef4444"
                            class="w-10 h-10 p-0.5 border border-gray-300 rounded cursor-pointer" />
                        <input type="text" id="cta_badge_color_hex" value="#ef4444" maxlength="20"
                            class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Secondary Button (Style 4) -->
        <div class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">Secondary Button (Style 4)</h4>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-form-input label="Secondary Button Text" name="secondary_button_text" id="cta_secondary_button_text" placeholder="e.g. Learn More" />
                </div>
                <div>
                    <x-form-input label="Secondary Button Link" name="secondary_button_link" id="cta_secondary_button_link" placeholder="e.g. /about" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="cta_secondary_button_color">Secondary Button Color</label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="secondary_button_color" id="cta_secondary_button_color" value="#ffffff"
                            class="w-10 h-10 p-0.5 border border-gray-300 rounded cursor-pointer" />
                        <input type="text" id="cta_secondary_button_color_hex" value="#ffffff" maxlength="20"
                            class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="cta_secondary_button_text_color">Secondary Button Text Color</label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="secondary_button_text_color" id="cta_secondary_button_text_color" value="#1f2937"
                            class="w-10 h-10 p-0.5 border border-gray-300 rounded cursor-pointer" />
                        <input type="text" id="cta_secondary_button_text_color_hex" value="#1f2937" maxlength="20"
                            class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Grid (Style 5) -->
        <div class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">Features (Style 5)</h4>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <x-form-input label="Feature 1 Icon" name="feature_icon_1" id="cta_feature_icon_1" placeholder="e.g. 🚚" />
                    <x-form-input label="Feature 1 Text" name="feature_text_1" id="cta_feature_text_1" placeholder="e.g. Free Shipping" class="mt-2" />
                </div>
                <div>
                    <x-form-input label="Feature 2 Icon" name="feature_icon_2" id="cta_feature_icon_2" placeholder="e.g. 🔒" />
                    <x-form-input label="Feature 2 Text" name="feature_text_2" id="cta_feature_text_2" placeholder="e.g. Secure Payment" class="mt-2" />
                </div>
                <div>
                    <x-form-input label="Feature 3 Icon" name="feature_icon_3" id="cta_feature_icon_3" placeholder="e.g. 💬" />
                    <x-form-input label="Feature 3 Text" name="feature_text_3" id="cta_feature_text_3" placeholder="e.g. 24/7 Support" class="mt-2" />
                </div>
            </div>
        </div>

        <!-- Dynamic Positioning & Margin -->
        <div class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">Button Positioning & Margin (All Styles)</h4>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-form-select label="Primary Button Position" name="button_position" id="cta_button_position">
                        <option value="">Default (Center)</option>
                        <option value="top-left">Top Left</option>
                        <option value="top-middle">Top Middle</option>
                        <option value="top-right">Top Right</option>
                        <option value="middle-left">Middle Left</option>
                        <option value="center">Center</option>
                        <option value="middle-right">Middle Right</option>
                        <option value="bottom-left">Bottom Left</option>
                        <option value="bottom-middle">Bottom Middle</option>
                        <option value="bottom-right">Bottom Right</option>
                    </x-form-select>
                </div>
                <div>
                    <x-form-select label="Secondary Button Position" name="secondary_button_position" id="cta_secondary_button_position">
                        <option value="">Default (Same as Primary)</option>
                        <option value="top-left">Top Left</option>
                        <option value="top-middle">Top Middle</option>
                        <option value="top-right">Top Right</option>
                        <option value="middle-left">Middle Left</option>
                        <option value="center">Center</option>
                        <option value="middle-right">Middle Right</option>
                        <option value="bottom-left">Bottom Left</option>
                        <option value="bottom-middle">Bottom Middle</option>
                        <option value="bottom-right">Bottom Right</option>
                    </x-form-select>
                </div>
                <div>
                    <x-form-input label="Primary Button Margin Top (px)" name="button_margin_top" id="cta_button_margin_top" type="number" placeholder="e.g. 10" />
                </div>
                <div>
                    <x-form-input label="Primary Button Margin Bottom (px)" name="button_margin_bottom" id="cta_button_margin_bottom" type="number" placeholder="e.g. 10" />
                </div>
                <div>
                    <x-form-input label="Primary Button Margin Left (px)" name="button_margin_left" id="cta_button_margin_left" type="number" placeholder="e.g. 10" />
                </div>
                <div>
                    <x-form-input label="Primary Button Margin Right (px)" name="button_margin_right" id="cta_button_margin_right" type="number" placeholder="e.g. 10" />
                </div>
                <div>
                    <x-form-input label="Secondary Button Margin Top (px)" name="secondary_button_margin_top" id="cta_secondary_button_margin_top" type="number" placeholder="e.g. 10" />
                </div>
                <div>
                    <x-form-input label="Secondary Button Margin Bottom (px)" name="secondary_button_margin_bottom" id="cta_secondary_button_margin_bottom" type="number" placeholder="e.g. 10" />
                </div>
                <div>
                    <x-form-input label="Secondary Button Margin Left (px)" name="secondary_button_margin_left" id="cta_secondary_button_margin_left" type="number" placeholder="e.g. 10" />
                </div>
                <div>
                    <x-form-input label="Secondary Button Margin Right (px)" name="secondary_button_margin_right" id="cta_secondary_button_margin_right" type="number" placeholder="e.g. 10" />
                </div>
            </div>
        </div>

        <!-- Content Alignment -->
        <div class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">Content Alignment & Margin</h4>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-form-select label="Content Alignment" name="content_alignment" id="cta_content_alignment">
                        <option value="">Default (Center)</option>
                        <option value="top-left">Top Left</option>
                        <option value="top-middle">Top Middle</option>
                        <option value="top-right">Top Right</option>
                        <option value="middle-left">Middle Left</option>
                        <option value="center">Center</option>
                        <option value="middle-right">Middle Right</option>
                        <option value="bottom-left">Bottom Left</option>
                        <option value="bottom-middle">Bottom Middle</option>
                        <option value="bottom-right">Bottom Right</option>
                    </x-form-select>
                </div>
                <div></div>
                <div>
                    <x-form-input label="Content Margin Top (px)" name="content_margin_top" id="cta_content_margin_top" type="number" placeholder="e.g. 20" />
                </div>
                <div>
                    <x-form-input label="Content Margin Bottom (px)" name="content_margin_bottom" id="cta_content_margin_bottom" type="number" placeholder="e.g. 20" />
                </div>
                <div>
                    <x-form-input label="Content Margin Left (px)" name="content_margin_left" id="cta_content_margin_left" type="number" placeholder="e.g. 20" />
                </div>
                <div>
                    <x-form-input label="Content Margin Right (px)" name="content_margin_right" id="cta_content_margin_right" type="number" placeholder="e.g. 20" />
                </div>
            </div>
        </div>

        <div class="mb-4">
            <x-form-input label="Sort Order" name="sort_order" id="cta_sort_order" type="number" value="0" />
        </div>
        <div class="mb-4">
            <x-form-select label="Status" name="status" id="cta_status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </x-form-select>
        </div>
    </x-entity-crud>

    @push('scripts')
    <script>
        // ===== CTA form fill =====
        window.fillCtaForm = function(data) {
            $('#cta_style').val(data.cta_style || 'style1');
            $('#cta_title').val(data.title || '');
            $('#cta_subtitle').val(data.subtitle || '');
            $('#cta_description').val(data.description || '');
            $('#cta_button_text').val(data.button_text || '');
            $('#cta_button_link').val(data.button_link || '');
            $('#cta_background_color').val(data.background_color || '#f8f9fa');
            $('#cta_background_color_hex').val(data.background_color || '#f8f9fa');
            $('#cta_text_color').val(data.text_color || '#1f2937');
            $('#cta_text_color_hex').val(data.text_color || '#1f2937');
            $('#cta_button_color').val(data.button_color || '#1e3a8a');
            $('#cta_button_color_hex').val(data.button_color || '#1e3a8a');
            $('#cta_button_text_color').val(data.button_text_color || '#ffffff');
            $('#cta_button_text_color_hex').val(data.button_text_color || '#ffffff');
            $('#cta_overlay_color').val(data.overlay_color || '#000000');
            $('#cta_overlay_color_hex').val(data.overlay_color || 'rgba(0,0,0,0.4)');
            $('#cta_badge_text').val(data.badge_text || '');
            $('#cta_badge_color').val(data.badge_color || '#ef4444');
            $('#cta_badge_color_hex').val(data.badge_color || '#ef4444');
            $('#cta_secondary_button_text').val(data.secondary_button_text || '');
            $('#cta_secondary_button_link').val(data.secondary_button_link || '');
            $('#cta_secondary_button_color').val(data.secondary_button_color || '#ffffff');
            $('#cta_secondary_button_color_hex').val(data.secondary_button_color || '#ffffff');
            $('#cta_secondary_button_text_color').val(data.secondary_button_text_color || '#1f2937');
            $('#cta_secondary_button_text_color_hex').val(data.secondary_button_text_color || '#1f2937');
            $('#cta_feature_icon_1').val(data.feature_icon_1 || '');
            $('#cta_feature_text_1').val(data.feature_text_1 || '');
            $('#cta_feature_icon_2').val(data.feature_icon_2 || '');
            $('#cta_feature_text_2').val(data.feature_text_2 || '');
            $('#cta_feature_icon_3').val(data.feature_icon_3 || '');
            $('#cta_feature_text_3').val(data.feature_text_3 || '');
            // Dynamic positioning & margin
            $('#cta_button_position').val(data.button_position || '');
            $('#cta_button_margin_top').val(data.button_margin_top ?? '');
            $('#cta_button_margin_bottom').val(data.button_margin_bottom ?? '');
            $('#cta_button_margin_left').val(data.button_margin_left ?? '');
            $('#cta_button_margin_right').val(data.button_margin_right ?? '');
            $('#cta_secondary_button_position').val(data.secondary_button_position || '');
            $('#cta_secondary_button_margin_top').val(data.secondary_button_margin_top ?? '');
            $('#cta_secondary_button_margin_bottom').val(data.secondary_button_margin_bottom ?? '');
            $('#cta_secondary_button_margin_left').val(data.secondary_button_margin_left ?? '');
            $('#cta_secondary_button_margin_right').val(data.secondary_button_margin_right ?? '');
            $('#cta_content_alignment').val(data.content_alignment || '');
            $('#cta_content_margin_top').val(data.content_margin_top ?? '');
            $('#cta_content_margin_bottom').val(data.content_margin_bottom ?? '');
            $('#cta_content_margin_left').val(data.content_margin_left ?? '');
            $('#cta_content_margin_right').val(data.content_margin_right ?? '');

            $('#cta_sort_order').val(data.sort_order || 0);
            $('#cta_status').val(data.status);

            // Show image preview if available
            if (data.image_url) {
                $('#ctaImagePreview img').attr('src', data.image_url);
                $('#ctaImagePreview').removeClass('hidden');
            } else {
                $('#ctaImagePreview').addClass('hidden');
            }

            // Show banner image preview if available
            if (data.banner_image_url) {
                $('#ctaBannerImagePreview img').attr('src', data.banner_image_url);
                $('#ctaBannerImagePreview').removeClass('hidden');
            } else {
                $('#ctaBannerImagePreview').addClass('hidden');
            }
        };

        $(document).ready(function() {
            // Sync color picker with hex input
            function syncColorPicker(pickerId, hexId) {
                $('#' + pickerId).on('input', function() {
                    $('#' + hexId).val(this.value);
                });
                $('#' + hexId).on('input', function() {
                    if (/^#[0-9a-fA-F]{6}$/.test(this.value)) {
                        $('#' + pickerId).val(this.value);
                    }
                });
            }
            syncColorPicker('cta_background_color', 'cta_background_color_hex');
            syncColorPicker('cta_text_color', 'cta_text_color_hex');
            syncColorPicker('cta_button_color', 'cta_button_color_hex');
            syncColorPicker('cta_button_text_color', 'cta_button_text_color_hex');
            syncColorPicker('cta_overlay_color', 'cta_overlay_color_hex');
            syncColorPicker('cta_badge_color', 'cta_badge_color_hex');
            syncColorPicker('cta_secondary_button_color', 'cta_secondary_button_color_hex');
            syncColorPicker('cta_secondary_button_text_color', 'cta_secondary_button_text_color_hex');

            // Preview image on file select
            $('#cta_image').on('change', function() {
                var file = this.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#ctaImagePreview img').attr('src', e.target.result);
                        $('#ctaImagePreview').removeClass('hidden');
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Preview banner image on file select
            $('#cta_banner_image').on('change', function() {
                var file = this.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#ctaBannerImagePreview img').attr('src', e.target.result);
                        $('#ctaBannerImagePreview').removeClass('hidden');
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
    @endpush
</x-app-layout>