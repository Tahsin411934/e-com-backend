@props(['active' => false, 'label' => null, 'nested' => false])

@php
    $accessibleLabel = $label ?: trim(strip_tags((string) $slot));
    $linkClass = (string) $attributes->get('class', '');
    $isActive = $active || str_contains($linkClass, 'bg-primary-light') || str_contains($linkClass, ' active');
@endphp

<a {{ $attributes->class($attributes->has('class') ? [] : [
    'sidebar-link relative flex items-center gap-2.5 rounded-lg text-sm font-medium transition-colors duration-150',
    'pl-9 pr-3 py-1.5 text-[13px]' => $nested,
    'px-3 py-2' => ! $nested,
    'text-primary bg-primary-light font-medium' => $active,
    'text-gray-400 hover:text-gray-700 hover:bg-gray-50' => ! $active,
]) }} @if ($accessibleLabel) aria-label="{{ $accessibleLabel }}" title="{{ $accessibleLabel }}" @endif @if ($isActive) aria-current="page" @endif>
    {{ $slot }}
</a>
