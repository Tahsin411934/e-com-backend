@props([
    'label', 
    'name', 
    'id' => null, 
    'placeholder' => '', 
    'rows' => 3,
    'value' => ''
])

<div>
    <label class="font-semibold text-sm text-slate-700 dark:text-slate-300 block mb-1">{{ $label }}</label>
    <textarea id="{{ $id ?? $name }}" 
              name="{{ $name }}" 
              rows="{{ $rows }}" 
              placeholder="{{ $placeholder }}"
              {{ $attributes->merge([
                  'class' => 'w-full border border-slate-300 dark:border-slate-600 rounded-md p-2 bg-white dark:bg-gray-700 text-slate-800 dark:text-slate-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all placeholder:text-slate-400 dark:placeholder:text-slate-500'
              ]) }}>{{ old($name, $value) }}</textarea>
</div>
