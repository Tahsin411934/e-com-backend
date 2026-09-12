@props([
    'id' => null,
    'edit' => null,        // JavaScript function name (e.g., 'categoryEdit')
    'delete' => null,      // JavaScript function name (e.g., 'categoryDelete')
    'duplicate' => null,   // JavaScript function name (e.g., 'productDuplicate')
    'showUrl' => null,     // URL for "View" button (e.g., route('purchase-orders.show', ':id'))
    'editUrl' => null,     // URL for "Edit" button (e.g., route('purchase-orders.edit', ':id'))
    'deleteUrl' => null,   // URL for AJAX delete (e.g., route('purchase-orders.destroy', ':id'))
    'show' => false,       // Show "View" button
    'permission' => null,  // Permission prefix (e.g., 'units', 'inventory-locations') -> gates edit/delete dynamically
    'entityLabel' => null, // Human label for the permission tooltip (e.g., 'Unit', 'Inventory Location')
    'adminOnly' => false,  // When true, edit/delete are restricted to Super Admin/Admin roles only
])

@php
    // Dynamic permission gating — buttons stay visible but become disabled
    // when the user's role lacks the matching permission (admin grants via Role Management UI).
    // Categories & brands are admin-managed reference data: with adminOnly=true
    // store owners can view and create, but only Super Admin/Admin may edit or delete.
    $isAdmin = auth()->user()->hasAnyRole(['Super Admin', 'Admin']);
    $canEdit   = (! $adminOnly) && (! $permission || auth()->user()->hasPermission($permission.'.edit'))
                 || ($adminOnly && $isAdmin);
    $canDelete = (! $adminOnly) && (! $permission || auth()->user()->hasPermission($permission.'.delete'))
                 || ($adminOnly && $isAdmin);

    $subject = $entityLabel ? 'this '.$entityLabel : 'this record';
    $editMsg   = $adminOnly
        ? "Only administrators can edit {$subject}."
        : "You don't have authority to edit {$subject}. Connect with administrator.";
    $deleteMsg = $adminOnly
        ? "Only administrators can delete {$subject}."
        : "You don't have authority to delete {$subject}. Connect with administrator.";
@endphp

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
        @if($canEdit)
        <button type="button" class="js-crud-action btn-primary px-2 py-1 rounded text-sm transition" data-crud-action="edit" data-crud-callback="{{ $edit }}" data-crud-id="{{ $id }}"
            title="Edit">
            <i class="fa fa-pencil"></i>
        </button>
        @else
        <button type="button" disabled
            class="btn-primary px-2 py-1 rounded text-sm transition opacity-40 cursor-not-allowed"
            title="{{ $editMsg }}"
            aria-disabled="true">
            <i class="fa fa-pencil"></i>
        </button>
        @endif
    @endif

    {{-- Edit Button (URL-based link) --}}
    @if($editUrl)
        @if($canEdit)
        <a href="{{ str_replace(':id', $id, $editUrl) }}"
            class="btn-primary px-2 py-1 rounded text-sm transition inline-flex items-center"
            title="Edit">
            <i class="fa fa-pencil"></i>
        </a>
        @else
        <span
            class="btn-primary px-2 py-1 rounded text-sm inline-flex items-center opacity-40 cursor-not-allowed"
            title="{{ $editMsg }}"
            aria-disabled="true">
            <i class="fa fa-pencil"></i>
        </span>
        @endif
    @endif

    {{-- Duplicate Button (JavaScript function) --}}
    @if($duplicate)
        <button type="button" class="js-crud-action bg-amber-500 text-white px-2 py-1 rounded text-sm hover:bg-amber-400 transition" data-crud-action="duplicate" data-crud-callback="{{ $duplicate }}" data-crud-id="{{ $id }}"
            title="Duplicate">
            <i class="fa fa-copy"></i>
        </button>
    @endif

    {{-- Delete Button --}}
    @if($deleteUrl)
        @if($canDelete)
        <button type="button" class="js-crud-action bg-red-500 text-white px-2 py-1 rounded text-sm hover:bg-red-600 transition" data-crud-action="delete-url" data-crud-url="{{ str_replace(':id', $id, $deleteUrl) }}" data-crud-label="{{ $delete ?? 'delete' }}"
            title="Delete">
            <i class="fa fa-trash"></i>
        </button>
        @else
        <button type="button" disabled
            class="bg-red-500 text-white px-2 py-1 rounded text-sm opacity-40 cursor-not-allowed"
            title="{{ $deleteMsg }}"
            aria-disabled="true">
            <i class="fa fa-trash"></i>
        </button>
        @endif
    @elseif($delete)
        @if($canDelete)
        <button type="button" class="js-crud-action bg-red-500 text-white px-2 py-1 rounded text-sm hover:bg-red-600 transition" data-crud-action="delete" data-crud-callback="{{ $delete }}" data-crud-id="{{ $id }}"
            title="Delete">
            <i class="fa fa-trash"></i>
        </button>
        @else
        <button type="button" disabled
            class="bg-red-500 text-white px-2 py-1 rounded text-sm opacity-40 cursor-not-allowed"
            title="{{ $deleteMsg }}"
            aria-disabled="true">
            <i class="fa fa-trash"></i>
        </button>
        @endif
    @endif
</div>
