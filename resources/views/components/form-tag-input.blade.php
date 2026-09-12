@props([
    'label',
    'name',
    'placeholder' => 'Type and press Enter...',
    'value' => '[]'
])

<div class="mb-4 tag-input-wrapper" data-field-name="{{ $name }}">
    @if(isset($label))
        <label class="block text-sm font-semibold text-slate-700 mb-1">{{ $label }}</label>
    @endif
    
    <div class="tag-container flex flex-wrap gap-2 p-2 border border-slate-300 rounded-md bg-white focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500 min-h-[42px] cursor-text">
        <input type="text" 
               class="tag-field-input flex-1 min-w-[150px] outline-none border-none p-0 text-sm focus:ring-0" 
               placeholder="{{ $placeholder }}">
    </div>
    
    <input type="hidden" id="{{ $name }}" name="{{ $name }}" value="{{ old($name, $value ?? '[]') }}">
</div>

{{-- Inject the structural script once globally --}}
@once
    @push('scripts')
        <script>
            (function () {
            function renderTagComponent(fieldName) {
                let hiddenInput = $('#' + fieldName);
                let wrapper = hiddenInput.closest('.tag-input-wrapper');
                let container = wrapper.find('.tag-container');
                let textInput = wrapper.find('.tag-field-input');
                
                container.find('.tag-badge').remove();
                
                let tags = [];
                try {
                    let rawVal = hiddenInput.val();
                    tags = typeof rawVal === 'string' ? JSON.parse(rawVal || '[]') : (rawVal || []);
                    if (!Array.isArray(tags)) tags = [];
                } catch (e) {
                    tags = [];
                }

                tags.forEach((tag, index) => {
                    let badgeHtml = `
                        <span class="tag-badge flex items-center gap-1 bg-indigo-50 text-indigo-700 text-xs font-medium px-2.5 py-1 rounded border border-indigo-200">
                            <span>${tag}</span>
                            <button type="button" class="remove-component-tag text-indigo-400 hover:text-indigo-600 focus:outline-none font-bold text-sm leading-none" data-field="${fieldName}" data-index="${index}">&times;</button>
                        </span>
                    `;
                    textInput.before(badgeHtml);
                });
            }

            $(document).ready(function() {
                // Initialize existing values on page load
                $('.tag-input-wrapper').each(function() {
                    renderTagComponent($(this).data('field-name'));
                });

                // Listen for Enter key
                $(document).on('keydown', '.tag-field-input', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        
                        let input = $(this);
                        let val = input.val().trim();
                        let wrapper = input.closest('.tag-input-wrapper');
                        let fieldName = wrapper.data('field-name');
                        let hiddenInput = $('#' + fieldName);

                        if (val !== '') {
                            let tags = [];
                            try { tags = JSON.parse(hiddenInput.val() || '[]'); } catch(e) { tags = []; }
                            
                            if (!tags.includes(val)) {
                                tags.push(val);
                                hiddenInput.val(JSON.stringify(tags));
                                renderTagComponent(fieldName);
                            }
                            input.val('');
                        }
                    }
                });

                // Listen for badge removals
                $(document).on('click', '.remove-component-tag', function(e) {
                    e.stopPropagation();
                    let fieldName = $(this).data('field');
                    let index = $(this).data('index');
                    let hiddenInput = $('#' + fieldName);
                    
                    let tags = [];
                    try { tags = JSON.parse(hiddenInput.val() || '[]'); } catch(e) { tags = []; }
                    
                    tags.splice(index, 1);
                    hiddenInput.val(JSON.stringify(tags));
                    renderTagComponent(fieldName);
                });

                // Container tracking click helper
                $(document).on('click', '.tag-container', function() {
                    $(this).find('.tag-field-input').focus();
                });
            });
            })();
        </script>
    @endpush
@endonce
