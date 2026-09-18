<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Site Settings') }}
        </h2>
    </x-slot>

    @php
        // Platform-staff overview: every settings row (global + per-store
        // overrides) with a Store column and store filter. Editing happens in
        // the form editor (Edit button); store overrides can be deleted here
        // (the key then falls back to the global value).
        $settingColumns = ['Store','Group','Key','Label','Type','Value','Created At','Action'];
        $settingDtColumns = [
            ['data' => 'store_name', 'orderable' => false, 'searchable' => false],
            ['data' => 'group'],
            ['data' => 'key'],
            ['data' => 'label'],
            ['data' => 'type'],
            ['data' => 'value'],
            ['data' => 'created_at'],
            ['data' => 'action', 'orderable' => false, 'searchable' => false],
        ];
        $settingOrder = [[2, 'asc']];
        $settingFilters = ['store_id' => [
            'label' => 'Store',
            'options' => '<option value="">All Stores</option><option value="global">Global (Platform)</option>'
                . $stores->map(fn ($store) => '<option value="'.e($store->id).'">'.e($store->name).'</option>')->implode(''),
        ]];
    @endphp

    <x-entity-crud
        id="site_setting"
        createPermission="frontend.settings"
        title="Site Settings"
        icon="fa-solid fa-gear"
        :columns="$settingColumns"
        :dtColumns="$settingDtColumns"
        :filters="$settingFilters"
        ajaxUrl="{{ route('frontend.site-settings.dataTable') }}"
        storeUrl=""
        updateUrl=""
        showUrl=""
        destroyUrl="{{ route('frontend.site-settings.destroy', ':id') }}"
        drawerTitle="Site Setting"
        dataKey="data"
        idField="site_setting_id"
        :order="$settingOrder"
    >
    </x-entity-crud>

    @push('scripts')
    <script>
        // Open the form-based editor (global, or scoped to the row's store).
        function siteSettingEdit(id, storeId) {
            var base = '{{ route("frontend.site-settings.edit") }}';
            window.location.href = storeId ? base + '?store_id=' + storeId : base;
        }

        // Delete a store-scoped override (the key falls back to the global value).
        function siteSettingDelete(id) {
            Swal.fire({
                title: 'Delete this setting override?',
                text: 'The key will fall back to the global (platform) value.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#4b5563',
                confirmButtonText: 'Yes, delete it!'
            }).then(function (result) {
                if (! result.isConfirmed) return;

                $.ajax({
                    url: '{{ route('frontend.site-settings.destroy', ':id') }}'.replace(':id', id),
                    type: 'POST',
                    data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        if (res.status === 'success') {
                            Toastify({
                                text: res.message || 'Deleted successfully',
                                duration: 3000,
                                gravity: 'bottom',
                                position: 'right',
                                style: { background: 'linear-gradient(135deg, #dc2626, #f87171)' }
                            }).showToast();
                            $('#sitesettingTable').DataTable().ajax.reload(null, false);
                        } else {
                            Swal.fire('Error', res.message || 'Error deleting', 'error');
                        }
                    },
                    error: function (xhr) {
                        var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Server communication error.';
                        Swal.fire('Error', msg, 'error');
                    }
                });
            });
        }
    </script>
    @endpush
</x-app-layout>
