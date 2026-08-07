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
    'filters' => [],
    'order' => [[0, 'desc']],
    'exportButtons' => true,
    'drawerTitle' => 'Add New',
    'drawerId' => null,
    'overlayId' => null,
    'formId' => null,
    'idField' => 'id',
    'dataKey' => 'data',
])

@php
    // STABLE identifiers — safe for JS (no hyphens)
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
    @if(count($filters) > 0)
    <div class="flex flex-col md:flex-row md:items-end gap-4 mb-5 bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
        @foreach ($filters as $label => $options)
            <div class="flex flex-col w-full md:w-1/4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $label }}</label>
                <select id="filter_{{ $safeId }}_{{ Str::slug($label) }}" class="dt-filter-{{ $safeTableId }} block w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    {!! $options !!}
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

    <x-data-table :id="$safeTableId" :title="$title" :icon="$icon" :buttonId="$safeButtonId" :buttonText="'Add New ' . $title" :columns="$columns" :ajaxUrl="$ajaxUrl" :dtColumns="$dtColumns" :exportButtons="$exportButtons" :order="$order" />
</div>

<x-drawer :id="$safeDrawerId" :overlayId="$safeOverlayId" :title="$drawerTitle" :submitOnClick="'save' . $safeUcId . 'Form()'">
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
        entityId: '{{ $id }}',
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

    // Namespace for safe access
    if (!window.__crud) window.__crud = {};
    var ns = window.__crud;

    // Public API
    ns[CFG.safeId] = {
        getTable: function() {
            if (!dtInstance) dtInstance = $('#' + CFG.tableId).DataTable();
            return dtInstance;
        },
        reloadTable: function() { this.getTable().ajax.reload(null, false); },
        resetFilters: function() {
            $('[id^="filter_{{ $safeId }}_"]').val('');
            this.reloadTable();
        }
    };

    // Filter changes (document-level to bridge component boundaries)
    $(document).on('change', '.dt-filter-' + CFG.tableId, function() {
        ns[CFG.safeId].reloadTable();
    });

    // Open Drawer
    window['open' + CFG.safeUcId + 'Drawer'] = function(mode) {
        if (mode === 'edit') {
            $titleEl.text('Update ' + CFG.drawerTitle);
            $btnText.text('Update ' + CFG.drawerTitle);
        } else {
            window['reset' + CFG.safeUcId + 'Form']();
            $titleEl.text('Add New ' + CFG.drawerTitle);
            $btnText.text('Save ' + CFG.drawerTitle);
        }
        openGlobalDrawer(CFG.drawerId, CFG.overlayId);
    };

    // Reset Form (clears everything including checkboxes)
    window['reset' + CFG.safeUcId + 'Form'] = function() {
        var form = document.getElementById(CFG.formId);
        if (!form) return;
        form.reset();
        Array.from(form.querySelectorAll('input[type="hidden"]')).forEach(function(el) { el.value = ''; });
        Array.from(form.querySelectorAll('input[type="checkbox"]')).forEach(function(el) { el.checked = el.defaultChecked; });
        Array.from(form.querySelectorAll('input[type="file"]')).forEach(function(el) { el.value = ''; });
        // Clear image previews
        form.querySelectorAll('[id$="Preview"], [id$="preview"]').forEach(function(el) {
            el.classList.add('hidden');
            var img = el.querySelector('img');
            if (img) img.src = '';
        });
    };

    // Edit
    window[CFG.entityId + 'Edit'] = function(id) {
        Swal.fire({
            title: 'Loading...',
            text: 'Fetching details',
            allowOutsideClick: false,
            didOpen: function() { Swal.showLoading(); }
        });

        window['reset' + CFG.safeUcId + 'Form']();
        var fetchUrl = CFG.showUrl.replace(':id', id);

        $.get(fetchUrl, function(res) {
            Swal.close();
            if (res.status === 'success') {
                var data = res[CFG.dataKey];
                $('#' + CFG.hiddenId).val(data.id);
                if (typeof window['fill' + CFG.safeUcId + 'Form'] === 'function') {
                    window['fill' + CFG.safeUcId + 'Form'](data);
                }
                window['open' + CFG.safeUcId + 'Drawer']('edit');
            } else {
                Swal.fire('Error', res.message || 'Failed to fetch data.', 'error');
            }
        }).fail(function() {
            Swal.close();
            Swal.fire('Error', 'Server communication error.', 'error');
        });
    };

    // Save
    window['save' + CFG.safeUcId + 'Form'] = function() {
        if (isSaving) return;

        var id = $('#' + CFG.hiddenId).val();
        var url = id ? CFG.updateUrl.replace(':id', id) : CFG.storeUrl;
        var formData = new FormData(document.getElementById(CFG.formId));
        if (id) formData.append('_method', 'PUT');

        isSaving = true;
        $btnText.text('Saving...');
        $saveBtn.prop('disabled', true).addClass('opacity-70 cursor-not-allowed');

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                isSaving = false;
                $saveBtn.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                if (res.status === 'success') {
                    Toastify({
                        text: res.message || 'Saved successfully',
                        duration: 3000,
                        gravity: 'bottom',
                        position: 'right',
                        style: { background: 'linear-gradient(135deg, #16a34a, #4ade80)' }
                    }).showToast();
                    closeGlobalDrawer(CFG.drawerId, CFG.overlayId);
                    ns[CFG.safeId].reloadTable();
                } else {
                    Swal.fire('Error', res.message || 'Something went wrong', 'error');
                    $btnText.text(id ? 'Update ' + CFG.drawerTitle : 'Save ' + CFG.drawerTitle);
                }
            },
            error: function(xhr) {
                isSaving = false;
                $saveBtn.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                $btnText.text(id ? 'Update ' + CFG.drawerTitle : 'Save ' + CFG.drawerTitle);
                var errorMsg = 'Server error occurred';
                if (xhr.responseJSON && xhr.responseJSON.errors) errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                else if (xhr.responseJSON && xhr.responseJSON.message) errorMsg = xhr.responseJSON.message;
                Swal.fire({ icon: 'error', title: 'Validation Error', html: errorMsg });
            }
        });
    };

    // Delete
    window[CFG.entityId + 'Delete'] = function(id) {
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
                            ns[CFG.safeId].reloadTable();
                        } else {
                            Swal.fire('Error', res.message || 'Error deleting', 'error');
                        }
                    },
                    error: function() { Swal.fire('Error', 'Server communication error.', 'error'); }
                });
            }
        });
    };

    // Init
    $(function() {
        $('#' + '{{ $safeResetId }}').on('click', function(e) {
            e.preventDefault();
            ns[CFG.safeId].resetFilters();
        });
        $('#' + '{{ $safeButtonId }}').on('click', function() {
            window['open' + CFG.safeUcId + 'Drawer']('add');
        });
    });
})();

// Global status update function for Purchase Orders (inline workflow buttons)
window.updatePoStatus = function(id, status) {
    const labels = {
        'ordered': 'Are you sure you want to mark this order as <strong>Ordered</strong>?',
        'received': 'Are you sure you want to mark this order as <strong>Received</strong>? This will update inventory stock.',
        'cancelled': 'Are you sure you want to <strong>Cancel</strong> this order?',
    };
    Swal.fire({
        title: 'Update Status',
        html: labels[status] || 'Update status to ' + status + '?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#4b5563',
        confirmButtonText: 'Yes, proceed!'
    }).then(function(result) {
        if (result.isConfirmed) {
            $.ajax({
                url: '/purchase-orders/' + id + '/update-status',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: status
                },
                success: function(res) {
                    if (res.status === 'success') {
                        Toastify({
                            text: res.message || 'Status updated successfully!',
                            duration: 3000,
                            gravity: 'bottom',
                            position: 'right',
                            style: { background: 'linear-gradient(135deg, #16a34a, #4ade80)' }
                        }).showToast();
                        if (dtInstance) {
                            dtInstance.ajax.reload(null, false);
                        } else {
                            location.reload();
                        }
                    } else {
                        Swal.fire('Error', res.message || 'Failed to update status', 'error');
                    }
                },
                error: function(xhr) {
                    let msg = 'Server error';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    Swal.fire('Error', msg, 'error');
                }
            });
        }
    });
};

// Global delete function for URL-based action buttons (used by purchase-orders, etc.)
window.deleteEntity = function(url, label) {
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
                url: url,
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
                        // Reload DataTable if exists
                        if (dtInstance) {
                            dtInstance.ajax.reload(null, false);
                        } else {
                            location.reload();
                        }
                    } else {
                        Swal.fire('Error', res.message || 'Error deleting', 'error');
                    }
                },
                error: function() { Swal.fire('Error', 'Server communication error.', 'error'); }
            });
        }
    });
};
</script>
@endpush