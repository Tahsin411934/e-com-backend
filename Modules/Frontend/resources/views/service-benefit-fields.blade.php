@php
    $formKey = $benefit?->id ?? 'new';
    $prefix = 'benefit-'.$formKey;
    $restore = (string) old('_benefit_id') === (string) $formKey;
    $value = fn ($key, $default = null) => $restore ? old($key, $default) : ($benefit?->{$key} ?? $default);
@endphp
<input type="hidden" name="_benefit_id" value="{{ $formKey }}">
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @if($storeId === null)
        <div><label for="{{ $prefix }}-store" class="block text-sm mb-1">Store *</label><select id="{{ $prefix }}-store" name="store_id" required class="w-full rounded-lg border-gray-300 dark:bg-gray-900"><option value="">Select a store</option>@foreach($stores as $store)<option value="{{ $store->id }}" @selected((string) $value('store_id', request('store_id')) === (string) $store->id)>{{ $store->name }}</option>@endforeach</select></div>
    @endif
    <div><label for="{{ $prefix }}-title" class="block text-sm mb-1">Title *</label><input id="{{ $prefix }}-title" name="title" value="{{ $value('title') }}" maxlength="100" required class="w-full rounded-lg border-gray-300 dark:bg-gray-900" placeholder="Your benefit title"></div>
    <div><label for="{{ $prefix }}-description" class="block text-sm mb-1">Description (optional)</label><input id="{{ $prefix }}-description" name="description" value="{{ $value('description') }}" maxlength="255" class="w-full rounded-lg border-gray-300 dark:bg-gray-900" placeholder="A short explanation of your benefit"></div>
    <div><label for="{{ $prefix }}-icon" class="block text-sm mb-1">Icon *</label><select id="{{ $prefix }}-icon" name="icon" required class="w-full rounded-lg border-gray-300 dark:bg-gray-900"><option value="">Select an icon</option>@foreach($icons as $key => $icon)<option value="{{ $key }}" @selected($value('icon') === $key)>{{ $icon['label'] }}</option>@endforeach</select></div>
    <div><label for="{{ $prefix }}-order" class="block text-sm mb-1">Sort Order *</label><input id="{{ $prefix }}-order" type="number" name="sort_order" min="0" max="1000000" value="{{ $value('sort_order', 0) }}" required class="w-full rounded-lg border-gray-300 dark:bg-gray-900"><p class="text-xs text-gray-500 mt-1">Lower numbers appear first.</p></div>
    <div><label for="{{ $prefix }}-active" class="block text-sm mb-1">Visibility *</label><select id="{{ $prefix }}-active" name="is_active" class="w-full rounded-lg border-gray-300 dark:bg-gray-900"><option value="1" @selected((bool) $value('is_active', true))>Active</option><option value="0" @selected(!(bool) $value('is_active', true))>Inactive</option></select></div>
</div>
