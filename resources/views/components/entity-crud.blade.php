@props([
    'id' => 'entity',
    'title' => 'Management',
    'icon' => 'fa-solid fa-list',
    'columns' => [],
    'dtColumns' => [],
    'ajaxUrl' => '',
    'storeUrl' => '',
    'updateUrl' => '',
    'showUrl' => '',
    'destroyUrl' => '',
    'createPermission' => null, // Permission group (e.g., 'units') â€” hides the Add New button unless granted
    'filters' => [],
    'order' => [[0, 'desc']],
    'exportButtons' => true,
    'drawerTitle' => 'Add New',
    'idField' => 'id',
    'dataKey' => 'data',
])

@php
    // STABLE identifiers â€” safe for JS (no hyphens)
    $safeId = str_replace(['-', '_'], '', $id);
    $safeUcId = ucfirst($safeId);
    $safeDrawerId = $safeId . 'Drawer';
    $safeOverlayId = $safeId . 'Overlay';
    $safeFormId = $safeId . 'Form';
    $safeTableId = $safeId . 'Table';
    $safeButtonId = 'btnAdd' . $safeUcId;
    $safeResetId = 'reset' . $safeUcId . 'Filters';
    $safeHiddenId = $safeId . '_hid';
@endphp

<div class="p-4">
    @if (count($filters) > 0)
    <div class="flex flex-col md:flex-row md:items-end gap-4 mb-5 bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
        @foreach ($filters as $param => $filter)
            <div class="flex flex-col w-full md:w-1/4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $filter['label'] }}</label>
                <select id="filter_{{ $safeId }}_{{ $param }}" data-local-select2 class="dt-filter-{{ $safeTableId }} block w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    {!! $filter['options'] !!}
                </select>
            </div>
        @endforeach
        <div class="w-full md:w-auto flex items-end">
            <button id="{{ $safeResetId }}"
                class="px-4 py-2 text-sm font-medium text-white bg-gray-700 hover:bg-gray-800 rounded-lg transition active:scale-95">
                Reset
            </button>
        </div>
    </div>
    @endif

    @php
        $filterMap = [];
        foreach ($filters as $param => $filter) {
            $filterMap[$param] = '#filter_' . $safeId . '_' . $param;
        }
    @endphp
    <x-data-table :id="$safeTableId" :title="$title" :icon="$icon" :buttonId="$safeButtonId" :buttonText="'Add New ' . $title" :createPermission="$createPermission" :columns="$columns" :ajaxUrl="$ajaxUrl" :dtColumns="$dtColumns" :exportButtons="$exportButtons" :order="$order" :filters="$filterMap" />
</div>

<x-drawer :id="$safeDrawerId" :overlayId="$safeOverlayId" :title="$drawerTitle" :submitEntity="$safeId" submitAction="save">
    <form id="{{ $safeFormId }}" enctype="multipart/form-data">
        <input type="hidden" name="{{ $idField }}" id="{{ $safeHiddenId }}">
        {{ $slot }}
    </form>
</x-drawer>

@push('scripts')
<script>
(function() {
    'use strict';
    var CFG = {
        safeId: '{{ $safeId }}',
        safeUcId: '{{ $safeUcId }}',
        tableId: '{{ $safeTableId }}',
        drawerId: '{{ $safeDrawerId }}',
        overlayId: '{{ $safeOverlayId }}',
        formId: '{{ $safeFormId }}',
        hiddenId: '{{ $safeHiddenId }}',
        dataKey: '{{ $dataKey }}',
        storeUrl: '{{ $storeUrl }}',
        updateUrl: '{{ $updateUrl }}',
        showUrl: '{{ $showUrl }}',
        destroyUrl: '{{ $destroyUrl }}',
        drawerTitle: '{{ $drawerTitle }}'
    };

    var isSaving = false;
    var dtInstance = null;

    // Scoped DOM refs
    var $drawer = $('#' + CFG.drawerId);
    var $titleEl = $drawer.find('#drawerTitle');
    var $btnText = $drawer.find('#drawerButtonText');
    var $saveBtn = $drawer.find('#saveBtn');

    var Crud = window.Crud;

    var setSavingState = function(isSaving, id) {
        $saveBtn
            .prop('disabled', isSaving)
            .toggleClass('opacity-70 cursor-not-allowed', isSaving);
        $btnText.text(isSaving ? 'Saving...' : (id ? 'Update ' : 'Save ') + CFG.drawerTitle);
    };

    // Public API
    var api = {
        getTable: function() {
            if (!dtInstance) dtInstance = $('#' + CFG.tableId).DataTable();
            return dtInstance;
        },
        reloadTable: function() { this.getTable().ajax.reload(null, false); },
        resetFilters: function() {
            // Repaint Select2 UI only â€” the namespaced event does NOT fire
            // plain change handlers, so this.reloadTable() below stays the
            // single data refresh (no double DataTable reload).
            $('[id^="filter_{{ $safeId }}_"]').val('').trigger('change.select2');
            this.reloadTable();
        }
    };

    // Filter changes (document-level to bridge component boundaries)
    $(document).on('change', '.dt-filter-' + CFG.tableId, function() {
        api.reloadTable();
    });

    // Drawer and form actions are registered per entity. Legacy global aliases
    // below keep existing drawer markup and module integrations compatible.
    var resetForm = function() {
        var form = document.getElementById(CFG.formId);
        if (!form) return;
        form.reset();
        form.querySelectorAll('input[type="hidden"]').forEach(function(el) { el.value = ''; });
        form.querySelectorAll('input[type="checkbox"]').forEach(function(el) { el.checked = el.defaultChecked; });
        form.querySelectorAll('input[type="file"]').forEach(function(el) { el.value = ''; });
        form.querySelectorAll('[id$="Preview"], [id$="preview"]').forEach(function(el) {
            el.classList.add('hidden');
            var image = el.querySelector('img');
            if (image) image.src = '';
        });
    };

    var openDrawer = function(mode) {
        if (mode === 'edit') {
            $titleEl.text('Update ' + CFG.drawerTitle);
            $btnText.text('Update ' + CFG.drawerTitle);
        } else {
            resetForm();
            $titleEl.text('Add New ' + CFG.drawerTitle);
            $btnText.text('Save ' + CFG.drawerTitle);
        }
        openGlobalDrawer(CFG.drawerId, CFG.overlayId);
    };
    Crud.register(CFG.safeId, 'reset', resetForm);
    Crud.register(CFG.safeId, 'open', openDrawer);

    // Edit
    var editHandler = function(id) {
        Swal.fire({
            title: 'Loading...',
            text: 'Fetching details',
            allowOutsideClick: false,
            didOpen: function() { Swal.showLoading(); }
        });

        resetForm();
        var fetchUrl = CFG.showUrl.replace(':id', id);

        $.get(fetchUrl, function(res) {
            Swal.close();
            if (res.status === 'success') {
                var data = res[CFG.dataKey];
                $('#' + CFG.hiddenId).val(data.id);
                var fillForm = Crud.get(CFG.safeId, 'fill');
                if (typeof fillForm === 'function') {
                    fillForm(data);
                }
                // Sync Select2 UI with the values the page's fill function
                // just set â€” plain .val() doesn't repaint the Select2
                // control, so the previously selected option looked unset.
                setTimeout(function () {
                    $('#' + CFG.formId).find('select[data-ajax-select2], select[data-local-select2]').each(function () {
                        var $el = $(this);

                        if ($el.hasClass('select2-hidden-accessible')) {
                            $el.trigger('change.select2');
                        }
                    });
                }, 0);
                openDrawer('edit');
            } else {
                Swal.fire('Error', res.message || 'Failed to fetch data.', 'error');
            }
        }).fail(function() {
            Swal.close();
            Swal.fire('Error', 'Server communication error.', 'error');
        });
    };
    Crud.register(CFG.safeId, 'edit', editHandler);

    // Save
    var saveForm = function() {
        if (isSaving) return;

        var id = $('#' + CFG.hiddenId).val();
        var url = id ? CFG.updateUrl.replace(':id', id) : CFG.storeUrl;
        var formData = new FormData(document.getElementById(CFG.formId));
        if (id) formData.append('_method', 'PUT');

        isSaving = true;
        setSavingState(true, id);

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                isSaving = false;
                setSavingState(false, id);
                if (res.status === 'success') {
                    Toastify({
                        text: res.message || 'Saved successfully',
                        duration: 3000,
                        gravity: 'bottom',
                        position: 'right',
                        style: { background: 'linear-gradient(135deg, #16a34a, #4ade80)' }
                    }).showToast();
                    closeGlobalDrawer(CFG.drawerId, CFG.overlayId);
                    api.reloadTable();
                } else {
                    Swal.fire('Error', res.message || 'Something went wrong', 'error');
                }
            },
            error: function(xhr) {
                isSaving = false;
                setSavingState(false, id);
                var errorMsg = 'Server error occurred';
                if (xhr.responseJSON && xhr.responseJSON.errors) errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                else if (xhr.responseJSON && xhr.responseJSON.message) errorMsg = xhr.responseJSON.message;
                Swal.fire({ icon: 'error', title: 'Validation Error', html: errorMsg });
            }
        });
    };
    Crud.register(CFG.safeId, 'save', saveForm);

    // Delete
    var deleteHandler = function(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: 'This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#4b5563',
            confirmButtonText: 'Yes, delete it!'
        }).then(function(result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: CFG.destroyUrl.replace(':id', id),
                    type: 'POST',
                    data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        if (res.status === 'success') {
                            Toastify({
                                text: res.message || 'Deleted successfully',
                                duration: 3000,
                                gravity: 'bottom',
                                position: 'right',
                                style: { background: 'linear-gradient(135deg, #dc2626, #f87171)' }
                            }).showToast();
                            api.reloadTable();
                        } else {
                            Swal.fire('Error', res.message || 'Error deleting', 'error');
                        }
                    },
                    error: function() { Swal.fire('Error', 'Server communication error.', 'error'); }
                });
            }
        });
    };
    Crud.register(CFG.safeId, 'delete', deleteHandler);

    // Init
    $(function() {
        $('#' + '{{ $safeResetId }}').on('click', function(e) {
            e.preventDefault();
            api.resetFilters();
        });
        $('#' + '{{ $safeButtonId }}').on('click', function() {
            openDrawer('add');
        });
    });
})();

</script>
@endpush
