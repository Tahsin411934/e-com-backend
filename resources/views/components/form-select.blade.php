@props([
    'label', 
    'name' => null, 
    'id' => null, 
    'placeholder' => 'Select an option',
    'required' => false,
    // Every select rendered through this component becomes a searchable
    // Select2 (client-side, no AJAX) by default. Opt out per usage with
    // :searchable="false" for plain native selects.
    'searchable' => true,
])

<div>
    <label class="font-semibold text-sm text-slate-700 dark:text-slate-300 block mb-1">{{ $label }}
         @if($required)
            <span class="text-rose-500 font-bold" aria-hidden="true">*</span>
        @endif
    </label>
    <select id="{{ $id ?? $name }}" 
            name="{{ $name }}"
            @if ($searchable) data-local-select2 @endif
            {{ $attributes->merge([
                'class' => 'w-full border border-slate-300 dark:border-slate-600 rounded-md p-2 bg-white dark:bg-gray-700 text-slate-800 dark:text-slate-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all cursor-pointer'
            ]) }}>
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        {{ $slot }}
    </select>
</div>
