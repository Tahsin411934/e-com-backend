<div class="flex flex-col w-full md:w-1/3">
    <label for="{{ $id }}"
        class="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1 uppercase tracking-wide">
        {{ $label }}
    </label>

    <select id="{{ $id }}"
        class="dt-filter-{{ $id }} w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg px-3 py-2 text-sm
               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">

        <option value="">{{ $placeholder }} {{ $label }}</option>

        @foreach ($options as $value => $text)
            <option value="{{ $value }}">{{ $text }}</option>
        @endforeach
    </select>
</div>
