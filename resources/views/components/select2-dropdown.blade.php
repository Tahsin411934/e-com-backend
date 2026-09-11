@props([
    'name',
    'route',
    'placeholder' => 'Search...',
    'id' => null,
    'label' => null,
    'multiple' => false,
    'required' => false,
    'allowClear' => false,
    'minimumInputLength' => 0,
    'limit' => 10,
    // When tags=true, the admin can also TYPE a brand-new value and press
    // Enter to create it (select2 tags) — not just pick from server results.
    'tags' => false,
    // Pre-selected value(s) for edit forms: ['id' => 3, 'text' => 'Foo']
    // or a list of those pairs / an Eloquent collection mapped the same way.
    'selected' => null,
])

@php
    $select2Id = $id ?? $name;

    // Normalise the pre-selected value(s) — accepts one pair, a list of
    // pairs, or an Eloquent/Support collection of pairs.
    $selectedOptions = [];
    if (! empty($selected)) {
        $selectedArray = $selected instanceof \Illuminate\Support\Collection ? $selected->all() : $selected;
        $selectedList = is_array($selectedArray) && array_is_list($selectedArray) ? $selectedArray : [$selectedArray];

        foreach ($selectedList as $option) {
            if (is_array($option) && array_key_exists('id', $option)) {
                $selectedOptions[] = ['id' => $option['id'], 'text' => $option['text'] ?? (string) $option['id']];
            }
        }
    }
@endphp

<div>
    @if (! empty($label))
        <label for="{{ $select2Id }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ $label }}
            @if ($required)
                <span class="text-rose-500 font-bold" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <select id="{{ $select2Id }}"
            name="{{ $name }}"
            @if ($multiple) multiple @endif
            @if ($required) required @endif
            data-ajax-select2
            data-route="{{ $route }}"
            data-placeholder="{{ $placeholder }}"
            data-limit="{{ $limit }}"
            @if ($allowClear) data-allow-clear="1" @endif
            @if ($tags) data-tags="1" @endif
            @if ($minimumInputLength > 0) data-minimum-input-length="{{ $minimumInputLength }}" @endif
            {{ $attributes->merge(['class' => 'w-full border border-slate-300 dark:border-slate-600 rounded-md p-2 bg-white dark:bg-gray-700 text-slate-800 dark:text-slate-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all']) }}>
        @if (! $multiple)
            {{-- Select2 needs an empty option to show the placeholder on single selects --}}
            <option value=""></option>
        @endif

        @foreach ($selectedOptions as $option)
            <option value="{{ $option['id'] }}" selected>{{ $option['text'] }}</option>
        @endforeach
    </select>
</div>
