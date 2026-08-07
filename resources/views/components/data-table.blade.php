@props([
    'id' => 'customTable',
    'title' => 'Management',
    'icon' => 'fa-solid fa-list',
    'buttonId' => 'btnAddNew',
    'buttonText' => 'Add New',
    'buttonLink' => null,
    'columns' => [],
    'ajaxUrl' => '',
    'dtColumns' => [],
    'exportButtons' => true,
    'filters' => [],
    'order' => [[0, 'desc']],
])

<div class="bg-white dark:bg-gray-800 shadow-md rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    {{-- TABLE HEADER --}}
    <div
        class="flex flex-col sm:flex-row sm:items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800 gap-4">
        <div class="flex items-center space-x-2.5">
            <i class="{{ $icon }} text-blue-600 dark:text-blue-400 text-lg"></i>
            <span class="font-bold text-gray-800 dark:text-gray-200 tracking-tight text-base">{{ $title }}</span>
        </div>

        <div class="flex flex-wrap items-center gap-3 justify-end">

            @if ($buttonLink)
                <a href="{{ $buttonLink }}"
                    class="bg-blue-900 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-sm flex items-center gap-2 transition-all duration-200 text-sm font-medium whitespace-nowrap active:scale-95">
                    <i class="fa fa-plus-circle"></i> {{ $buttonText }}
                </a>
            @elseif ($buttonId)
                <button id="{{ $buttonId }}"
                    class="bg-blue-900 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-sm flex items-center gap-2 transition-all duration-200 text-sm font-medium whitespace-nowrap active:scale-95">
                    <i class="fa fa-plus-circle"></i> {{ $buttonText }}
                </button>
            @endif
        </div>
    </div>

    {{-- TABLE BODY --}}
    <div class="p-6">
        <div class="overflow-x-auto">
            <table id="{{ $id }}" class="w-full border-collapse rounded-lg text-sm text-gray-700">
                <thead>
                    <tr>
                        @foreach ($columns as $column)
                            <th 
                                class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider
                                       {{ $column === 'Action' || $column === 'Actions' ? 'text-center' : '' }}">
                                {{ $column }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function() {
            const tableId = "{{ $id }}";

            function buildAjaxData(filters) {
                return function(d) {
                    Object.keys(filters || {}).forEach(key => {
                        let selector = filters[key];
                        let value = $(selector).val();
                        d[key] = value;
                    });
                }
            }
            let tableButtons = [];

            @if ($exportButtons)
                tableButtons = [{
                        extend: 'excelHtml5',
                        text: '<i class="fa-solid fa-file-excel mr-1.5 text-green-500"></i> Excel',
                        className: 'inline-flex items-center px-3 py-1.5 bg-white border border-gray-200 text-gray-700 text-xs font-semibold rounded-lg hover:bg-gray-50 hover:border-gray-300 transition duration-150 shadow-sm',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fa-solid fa-file-pdf mr-1.5 text-red-500"></i> PDF',
                        className: 'inline-flex items-center px-3 py-1.5 bg-white border border-gray-200 text-gray-700 text-xs font-semibold rounded-lg hover:bg-gray-50 hover:border-gray-300 transition duration-150 shadow-sm',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fa-solid fa-print mr-1.5 text-gray-500"></i> Print',
                        className: 'inline-flex items-center px-3 py-1.5 bg-white border border-gray-200 text-gray-700 text-xs font-semibold rounded-lg hover:bg-gray-50 hover:border-gray-300 transition duration-150 shadow-sm',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    },
                    {
                        extend: 'colvis',
                        text: '<i class="fa-solid fa-table-columns mr-1.5 text-blue-500"></i> Columns',
                        className: 'inline-flex items-center px-3 py-1.5 bg-white border border-gray-200 text-gray-700 text-xs font-semibold rounded-lg hover:bg-gray-50 hover:border-gray-300 transition duration-150 shadow-sm'
                    },
                ];
            @endif

            const table = $('#{{ $id }}').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                ajax: {
                    url: "{!! $ajaxUrl !!}",
                    data: buildAjaxData(@json($filters ?? []))
                },
                columns: {!! json_encode($dtColumns) !!},
                dom: '<"flex flex-col md:flex-row items-center justify-between gap-4 mb-4"lBf>rt<"flex flex-col md:flex-row items-center justify-between gap-4 mt-4"ip>',
                buttons: tableButtons,
                ScrollX: true,
                responsive: true,
                order: @json($order),
                language: {
                    search: "",
                    searchPlaceholder: "🔎︎ Search records...",
                    lengthMenu: "Show _MENU_ entries"
                }
            });

            $(document).on('change', '.dt-filter-' + tableId, function() {
                table.ajax.reload();
            });

            @if ($exportButtons)
                setTimeout(function() {
                    if (table.buttons().container().length) {
                        table.buttons().container().appendTo('#{{ $id }}_buttons_container');
                        $('.dt-button').removeClass('dt-button');
                    }
                }, 50);
            @endif
        });
    </script>
@endpush

<style>
    #{{ $id }} tbody tr:nth-child(even) {
        background-color: #f8fafc !important;
    }

    #{{ $id }} tbody tr:nth-child(odd) {
        background-color: #ffffff !important;
    }

    #{{ $id }} tbody tr {
        transition: all 0.15s ease-in-out;
        border-bottom: 1px solid #13293f;
    }

    #{{ $id }} tbody tr:hover {
        background-color: #f1f5f9 !important;
    }

    #{{ $id }} thead th {
        background-color: #f1f5f9 !important;
        color: #475569 !important;
        border-bottom: 2px solid #cbd5e1 !important;
        padding: 12px 16px !important;

    }

    #{{ $id }} tbody td {
        padding: 12px 16px !important;
        color: #334155;
        vertical-align: middle;
        border: 1px solid #ced5df !important;
    }

    /* Dark mode overrides for DataTable */
    .dark #{{ $id }} tbody tr:nth-child(even) {
        background-color: #1f2937 !important;
    }

    .dark #{{ $id }} tbody tr:nth-child(odd) {
        background-color: #111827 !important;
    }

    .dark #{{ $id }} tbody tr {
        border-bottom: 1px solid #374151 !important;
    }

    .dark #{{ $id }} tbody tr:hover {
        background-color: #374151 !important;
    }

    .dark #{{ $id }} thead th {
        background-color: #1f2937 !important;
        color: #9ca3af !important;
        border-bottom: 2px solid #4b5563 !important;
    }

    .dark #{{ $id }} tbody td {
        color: #e5e7eb !important;
        border: 1px solid #374151 !important;
    }

    .dark .dataTables_filter input {
        border: 1px solid #4b5563;
        background-color: #1f2937;
        color: #e5e7eb;
    }

    .dark .dataTables_filter input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }

    .dark .dataTables_length select {
        border: 1px solid #4b5563;
        background-color: #1f2937;
        color: #e5e7eb;
    }

    .dark .dataTables_length select option {
        background-color: #1f2937;
        color: #e5e7eb;
    }

    .dark .dataTables_info {
        color: #9ca3af !important;
    }

    .dark .dataTables_paginate .paginate_button {
        color: #9ca3af !important;
        border-color: #4b5563 !important;
        background: #1f2937 !important;
    }

    .dark .dataTables_paginate .paginate_button:hover {
        color: #e5e7eb !important;
        background: #374151 !important;
    }

    .dark .dataTables_paginate .paginate_button.current {
        color: #fff !important;
        background: var(--primary-color) !important;
        border-color: var(--primary-color) !important;
    }

    .dark .dataTables_wrapper .dataTables_length,
    .dark .dataTables_wrapper .dataTables_filter,
    .dark .dataTables_wrapper .dataTables_info,
    .dark .dataTables_wrapper .dataTables_paginate {
        color: #9ca3af !important;
    }

    .dark .dt-buttons .btn,
    .dark .dt-buttons button {
        background-color: #1f2937 !important;
        border-color: #4b5563 !important;
        color: #e5e7eb !important;
    }

    .dark .dt-buttons .btn:hover,
    .dark .dt-buttons button:hover {
        background-color: #374151 !important;
    }

    .dark .dataTables_processing {
        background-color: #1f2937 !important;
        color: #e5e7eb !important;
    }

    .dataTables_filter input {
        border: 1px solid #e2e8f0;
        padding: 0.45rem 0.85rem;
        border-radius: 0.5rem;
        outline: none;
        font-size: 0.875rem;
        transition: all 0.2s;
        width: 320px;
    }

    .dataTables_filter input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }

    /* dropdown tyle */
    .dataTables_length select {
        border: 1px solid #e2e8f0;
        padding: 0.4rem 0.9rem !important;
        border-radius: 0.5rem;
        outline: none;
        font-size: 0.875rem;
        -webkit-appearance: none;
        /* Chrome, Safari, Opera */
        -moz-appearance: none;
        /* Firefox */
        appearance: none;
        /* Modern Browsers */
    }

    .dataTables_length select::-ms-expand {
        display: none;
    }

    .dataTables_wrapper .dataTables_buttons {
        display: none !important;
    }
</style>
