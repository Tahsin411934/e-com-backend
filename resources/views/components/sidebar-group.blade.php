@props(['id', 'title', 'active' => false])

<section data-sidebar-group data-active="{{ $active ? 'true' : 'false' }}" data-open="{{ $active ? 'true' : 'false' }}" class="mb-0.5">
    <button type="button"
        aria-controls="{{ $id }}"
        aria-label="{{ $title }}"
        title="{{ $title }}"
        data-label="{{ $title }}"
        data-sidebar-group-toggle
        data-sub="{{ $id }}"
        aria-expanded="{{ $active ? 'true' : 'false' }}"
        @class([
            'nav-item has-sub w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150',
            'text-primary bg-primary-light' => $active,
            'text-gray-500 hover:bg-gray-50 hover:text-gray-800' => ! $active,
        ])>
        {{ $icon }}
        <span class="nav-label flex-1 text-left">{{ $title }}</span>
        <i class="nav-chevron fas fa-chevron-down text-[10px] flex-shrink-0"></i>
    </button>
    <div id="{{ $id }}" data-sidebar-submenu class="submenu sub-indent {{ $active ? 'open' : '' }}">
        {{ $slot }}
    </div>
</section>
