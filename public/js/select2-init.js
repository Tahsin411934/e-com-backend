/*
|--------------------------------------------------------------------------
| Select2 — Global Initializer (AJAX + local search modes)
|--------------------------------------------------------------------------
| Auto-attaches Select2 to opted-in selects across the whole project.
| Small static selects are NEVER touched unless they opt in.
|
| Two modes, chosen purely by data attributes (zero inline JS in views):
|
|   data-ajax-select2     Server-side search for LARGE datasets.
|                         Sends ?q=term&page=1&limit=10 (250ms debounce).
|                         Endpoint returns ApiResponse:
|                         { status, data: { results, pagination } }
|
|   data-local-select2    Client-side search for SMALL lists already
|                         rendered in the page. No server requests.
|
| Common attributes for both modes:
|
|   data-placeholder="..."         placeholder text (falls back to the
|                                  empty <option>'s own text)
|   data-allow-clear="1"           show the "x" clear button
|   data-minimum-input-length="3"  defer searching until N chars typed
|   data-limit="10"                AJAX page size (server clamps 1..50)
|   data-select2-dropdown-parent   CSS selector override for dropdownParent
|
| Inside custom drawers/modals ([id$="Drawer"], .modal, .offcanvas) the
| dropdown is anchored to that panel so it scrolls with the form.
*/
(function () {
    'use strict';

    var DEBOUNCE_MS = 250;

    /**
     * Resolve the container Select2 appends its dropdown to.
     * Inside custom drawers/modals we anchor to that panel so the dropdown
     * scrolls with the form instead of detaching from the control.
     */
    function resolveDropdownParent($el) {
        var $custom = $el.closest($el.data('select2-dropdown-parent') || null);
        if ($custom && $custom.length) {
            return $custom;
        }

        var $panel = $el.closest('[id$="Drawer"], .modal, .offcanvas');
        if ($panel.length) {
            return $panel;
        }

        return $(document.body);
    }

    function buildSelect2Options($el) {
        var options = {
            // null → Select2 falls back to the empty <option>'s own text
            // ("None", "All", "Select Account"...), preserving native labels.
            placeholder: $el.data('placeholder') || null,
            allowClear: String($el.data('allow-clear')) === '1',
            minimumInputLength: parseInt($el.data('minimum-input-length'), 10) || 0,
            width: 'style',
            dropdownParent: resolveDropdownParent($el),
        };

        if ($el.is('[data-ajax-select2]')) {
            options.ajax = {
                url: $el.data('route'),
                method: 'GET',
                dataType: 'json',
                delay: DEBOUNCE_MS, // 250ms debounce — fires only after typing pauses
                data: function (params) {
                    return {
                        q: params.term || '',
                        page: params.page || 1,
                        limit: parseInt($el.data('limit'), 10) || 10,
                    };
                },
                processResults: function (response) {
                    var payload = response && response.data ? response.data : response;

                    return {
                        results: (payload && payload.results) || [],
                        pagination: (payload && payload.pagination) || { more: false },
                    };
                },
            };
        }

        return options;
    }

    /** Attach Select2 to every opted-in select inside `scope`. Idempotent. */
    function init(scope) {
        $(scope || document).find('select[data-ajax-select2], select[data-local-select2]').each(function () {
            var $el = $(this);

            if ($el.hasClass('select2-hidden-accessible')) {
                return; // already initialized
            }

            $el.select2(buildSelect2Options($el));
        });
    }

    /**
     * Repaint Select2 UI so it matches the native <select> values.
     * Call after programmatically setting values (e.g. form fill in edit
     * drawers) — plain .val() doesn't update the Select2 control itself.
     */
    function sync(scope) {
        $(scope || document).find('select[data-ajax-select2], select[data-local-select2]').each(function () {
            var $el = $(this);

            if ($el.hasClass('select2-hidden-accessible')) {
                $el.trigger('change.select2');
            }
        });
    }

    // Keep the Select2 UI in sync after a native form.reset() — the shared
    // entity-crud drawer calls form.reset() when opening "Add New".
    $(document).on('reset', 'form', function () {
        var $form = $(this);

        window.setTimeout(function () {
            sync($form);
        }, 0);
    });

    // Safety net for filter-bar "Reset" buttons (any id starting with
    // "reset", e.g. resetFilters, resetBannerFilters). Page code clears the
    // native select values; we repaint the Select2 UI to match. The
    // namespaced event touches ONLY Select2's own listener, so no extra
    // DataTable reloads fire — the page's reset handler refreshes the data.
    $(document).on('click', '[id^="reset"]', function () {
        var $btn = $(this);

        window.setTimeout(function () {
            $btn.parents().filter(function () {
                return $(this).find('select.select2-hidden-accessible').length > 0;
            }).first().find('select[data-ajax-select2], select[data-local-select2]').each(function () {
                var $el = $(this);

                if ($el.hasClass('select2-hidden-accessible')) {
                    $el.trigger('change.select2');
                }
            });
        }, 0);
    });

    // Public API for dynamically injected content:
    // window.Select2Ajax.init($('#someContainer'));
    // window.Select2Ajax.sync($('#someForm'));   ← repaint after .val()
    window.Select2Ajax = { init: init, sync: sync };

    $(function () {
        init(document);
    });
})();
