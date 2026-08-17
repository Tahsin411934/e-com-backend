@props([
    'id' => null,
    'edit' => null,        // JavaScript function name (e.g., 'categoryEdit')
    'delete' => null,      // JavaScript function name (e.g., 'categoryDelete')
    'duplicate' => null,   // JavaScript function name (e.g., 'productDuplicate')
    'showUrl' => null,     // URL for "View" button (e.g., route('purchase-orders.show', ':id'))
    'editUrl' => null,     // URL for "Edit" button (e.g., route('purchase-orders.edit', ':id'))
    'deleteUrl' => null,   // URL for AJAX delete (e.g., route('purchase-orders.destroy', ':id'))
    'show' => false,       // Show "View" button
])

<div class="flex space-x-1 justify-center items-center">
    {{-- View Button --}}
    @if($showUrl && $show)
        <a href="{{ str_replace(':id', $id, $showUrl) }}"
            class="bg-gray-100 text-gray-600 hover:bg-gray-200 hover:text-gray-800 p-1.5 rounded text-xs transition"
            title="View">
            <i class="fa fa-eye"></i>
        </a>
    @endif

    {{-- Edit Button (JavaScript function) --}}
    @if($edit)
        <button onclick="{{ $edit }}({{ $id }})"
            class="btn-primary px-2 py-1 rounded text-sm transition"
            title="Edit">
            <i class="fa fa-pencil"></i>
        </button>
    @endif

    {{-- Edit Button (URL-based link) --}}
    @if($editUrl)
        <a href="{{ str_replace(':id', $id, $editUrl) }}"
            class="btn-primary px-2 py-1 rounded text-sm transition inline-flex items-center"
            title="Edit">
            <i class="fa fa-pencil"></i>
        </a>
    @endif

    {{-- Duplicate Button (JavaScript function) --}}
    @if($duplicate)
        <button onclick="{{ $duplicate }}({{ $id }})"
            class="bg-amber-500 text-white px-2 py-1 rounded text-sm hover:bg-amber-400 transition"
            title="Duplicate">
            <i class="fa fa-copy"></i>
        </button>
    @endif

    {{-- Delete Button --}}
    @if($deleteUrl)
        <button onclick="deleteEntity('{{ $deleteUrl }}'.replace(':id', {{ $id }}), '{{ $delete ?? 'delete' }}')"
            class="bg-red-500 text-white px-2 py-1 rounded text-sm hover:bg-red-600 transition"
            title="Delete">
            <i class="fa fa-trash"></i>
        </button>
    @elseif($delete)
        <button onclick="{{ $delete }}({{ $id }})"
            class="bg-red-500 text-white px-2 py-1 rounded text-sm hover:bg-red-600 transition"
            title="Delete">
            <i class="fa fa-trash"></i>
        </button>
    @endif
</div>