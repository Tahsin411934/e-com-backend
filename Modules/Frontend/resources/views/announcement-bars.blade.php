<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Announcement Bars') }}
        </h2>
    </x-slot>

    <x-entity-crud
        id="announcement_bar"
        createPermission="frontend.announcements"
        title="Announcement Bars"
        icon="fa-solid fa-rectangle-ad"
        :columns="['Left Text','Center Text','Right Text','Background','Sort Order','Status','Created At','Action']"
        :dtColumns="[
            ['data' => 'left_text'],
            ['data' => 'center_text'],
            ['data' => 'right_text'],
            ['data' => 'background_color', 'orderable' => false, 'searchable' => false],
            ['data' => 'sort_order'],
            ['data' => 'status'],
            ['data' => 'created_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ]"
        ajaxUrl="{{ route('frontend.announcement-bars.dataTable') }}"
        storeUrl="{{ route('frontend.announcement-bars.store') }}"
        updateUrl="{{ route('frontend.announcement-bars.update', ':id') }}"
        showUrl="{{ route('frontend.announcement-bars.show', ':id') }}"
        destroyUrl="{{ route('frontend.announcement-bars.destroy', ':id') }}"
        drawerTitle="Announcement Bar"
        dataKey="data"
        idField="announcement_bar_id"
        :order="[[4, 'asc']]"
    >
        <div class="mb-4">
            <x-form-input label="Left Text" name="left_text" id="announcement_bar_left_text" placeholder="e.g. 🚚 Free Shipping on Orders Over ৳99" />
        </div>
        <div class="mb-4">
            <x-form-input label="Center Text" name="center_text" id="announcement_bar_center_text" placeholder="e.g. Summer Sale is Live! Up to 50% OFF 🔥" />
        </div>
        <div class="mb-4">
            <x-form-input label="Right Text" name="right_text" id="announcement_bar_right_text" placeholder="e.g. 📞 Support: (800) 123-4567" />
        </div>

        <!-- Color fields in a 2-column grid -->
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="announcement_bar_background_color">Background Color</label>
                <div class="flex items-center gap-2">
                    <input type="color" name="background_color" id="announcement_bar_background_color" value="#0F1115"
                        class="w-10 h-10 p-0.5 border border-gray-300 rounded cursor-pointer" />
                    <input type="text" id="announcement_bar_background_color_hex" value="#0F1115" maxlength="20"
                        class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" />
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="announcement_bar_text_color">Text Color</label>
                <div class="flex items-center gap-2">
                    <input type="color" name="text_color" id="announcement_bar_text_color" value="#ffffff"
                        class="w-10 h-10 p-0.5 border border-gray-300 rounded cursor-pointer" />
                    <input type="text" id="announcement_bar_text_color_hex" value="#ffffff" maxlength="20"
                        class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" />
                </div>
            </div>
        </div>

        <div class="mb-4">
            <x-form-input label="Sort Order" name="sort_order" id="announcement_bar_sort_order" type="number" value="0" />
        </div>
        <div class="mb-4">
            <x-form-select label="Status" name="status" id="announcement_bar_status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </x-form-select>
        </div>
    </x-entity-crud>

    @push('scripts')
    <script>
        // ===== Announcement Bar form fill (called by entity-crud component) =====
Crud.register('announcementbar', 'fill', function (data) {
            $('#announcement_bar_left_text').val(data.left_text || '');
            $('#announcement_bar_center_text').val(data.center_text || '');
            $('#announcement_bar_right_text').val(data.right_text || '');
            $('#announcement_bar_background_color').val(data.background_color || '#0F1115');
            $('#announcement_bar_background_color_hex').val(data.background_color || '#0F1115');
            $('#announcement_bar_text_color').val(data.text_color || '#ffffff');
            $('#announcement_bar_text_color_hex').val(data.text_color || '#ffffff');
            $('#announcement_bar_sort_order').val(data.sort_order || 0);
            $('#announcement_bar_status').val(data.status);
        });

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
            syncColorPicker('announcement_bar_background_color', 'announcement_bar_background_color_hex');
            syncColorPicker('announcement_bar_text_color', 'announcement_bar_text_color_hex');
        });
    </script>
    @endpush
</x-app-layout>
