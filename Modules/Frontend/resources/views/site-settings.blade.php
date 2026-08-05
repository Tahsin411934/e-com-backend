<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            ⚙️ Site Settings
        </h2>
    </x-slot>

    @push('head')
    <style>
        .settings-card { border-radius: 16px; border: 1px solid #e5e7eb; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
        .settings-section-title { font-size: 14px; font-weight: 700; color: #374151; padding-bottom: 12px; border-bottom: 2px solid #f3f4f6; display: flex; align-items: center; gap: 8px; }
        .settings-section-title .icon { width: 32px; height: 32px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 14px; }
        .tab-btn { padding: 10px 20px; border-radius: 10px; font-size: 14px; font-weight: 600; transition: all 0.2s; border: none; cursor: pointer; }
        .tab-btn.active { background: #1e3a8a; color: white; box-shadow: 0 2px 8px rgba(30, 58, 138, 0.25); }
        .tab-btn:not(.active) { background: #f3f4f6; color: #6b7280; }
        .tab-btn:not(.active):hover { background: #e5e7eb; color: #374151; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .logo-preview { width: 120px; height: 120px; border-radius: 16px; border: 2px dashed #d1d5db; object-fit: contain; background: #f9fafb; padding: 8px; }
        .logo-upload-area { display: flex; align-items: center; gap: 20px; padding: 16px; background: #f9fafb; border-radius: 12px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
        .form-group .hint { font-size: 11px; color: #9ca3af; margin-top: 4px; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 10px; font-size: 14px; transition: border-color 0.2s; }
        .form-group input:focus, .form-group textarea:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); outline: none; }
        .form-group textarea { min-height: 80px; resize: vertical; }
        .btn-primary { padding: 12px 32px; background: linear-gradient(135deg, #1e3a8a, #2563eb); color: white; border: none; border-radius: 12px; font-size: 15px; font-weight: 700; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(30, 58, 138, 0.3); }
        .btn-secondary { padding: 12px 24px; background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .btn-secondary:hover { background: #e5e7eb; }
        .save-bar { position: sticky; bottom: 0; background: rgba(255,255,255,0.95); backdrop-filter: blur(8px); border-top: 1px solid #e5e7eb; padding: 16px 24px; display: flex; justify-content: flex-end; gap: 12px; border-radius: 0 0 16px 16px; margin-top: 24px; }
        .empty-state { text-align: center; padding: 40px 20px; color: #9ca3af; }
    </style>
    @endpush

    <div class="p-4 lg:p-6 max-w-[900px] mx-auto">
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
            <span>Frontend</span>
            <i class="fas fa-chevron-right text-[10px]"></i>
            <span class="text-gray-800 font-medium">Site Settings</span>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm flex items-center gap-3">
                <i class="fas fa-check-circle text-green-500"></i> {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm">
                <ul class="list-disc pl-4 space-y-1">
                    @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        <div class="settings-card p-6 lg:p-8">
            <form method="POST" action="{{ route('frontend.site-settings.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="flex flex-wrap gap-2 mb-8 pb-6 border-b border-gray-100">
                    @foreach($groups as $group)
                        <button type="button" class="tab-btn {{ $loop->first ? 'active' : '' }}"
                            onclick="switchTab('{{ $group }}')">
                            @switch($group)
                                @case('general') <i class="fas fa-globe"></i> @break
                                @case('social')  <i class="fas fa-share-nodes"></i> @break
                                @case('contact') <i class="fas fa-address-card"></i> @break
                                @case('seo')     <i class="fas fa-search"></i> @break
                            @endswitch
                            {{ ucfirst($group) }}
                        </button>
                    @endforeach
                </div>

                @foreach($groups as $group)
                    <div id="tab-{{ $group }}" class="tab-content {{ $loop->first ? 'active' : '' }}">
                        @php $items = $grouped[$group] ?? []; @endphp

                        @if(empty($items))
                            <div class="empty-state">
                                <div class="text-4xl mb-3">📭</div>
                                <p>No settings in this section yet.</p>
                                <a href="{{ route('frontend.site-settings.seed') }}" class="btn-secondary mt-4">
                                    <i class="fas fa-seedling"></i> Seed Defaults
                                </a>
                            </div>
                        @else
                            <div class="settings-section-title mb-6">
                                <span class="icon" style="background: {{ match($group) { 'general' => '#dbeafe', 'social' => '#fce7f3', 'contact' => '#d1fae5', 'seo' => '#ede9fe', default => '#f3f4f6' } }};">
                                    @switch($group) @case('general') 🌐 @break @case('social') 📱 @break @case('contact') 📞 @break @case('seo') 🔍 @break @endswitch
                                </span>
                                {{ ucfirst($group) }} Settings
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                                @foreach($items as $item)
                                    <div class="form-group {{ in_array($item['type'], ['textarea','image']) ? 'md:col-span-2' : '' }}">
                                        <label for="setting_{{ $item['key'] }}">{{ $item['label'] }}</label>

                                        @switch($item['type'])
                                            @case('textarea')
                                                <textarea name="{{ $item['key'] }}" id="setting_{{ $item['key'] }}" rows="3">{{ old($item['key'], $item['value']) }}</textarea>
                                                @break
                                            @case('image')
                                                <div class="logo-upload-area">
                                                    <div class="shrink-0">
                                                        @if($item['value'])
                                                            <img src="{{ $item['value'] }}" alt="Logo" class="logo-preview" id="logo_preview">
                                                        @else
                                                            <div class="logo-preview flex items-center justify-center text-gray-300" id="logo_preview">
                                                                <i class="fas fa-image text-3xl"></i>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="flex-1">
                                                        <div class="flex flex-wrap items-center gap-3">
                                                            <label class="btn-secondary cursor-pointer">
                                                                <i class="fas fa-upload"></i> Choose Image
                                                                <input type="file" name="{{ $item['key'] }}" accept="image/*" class="hidden" onchange="previewLogo(this, 'logo_preview')">
                                                            </label>
                                                            @if($item['value'])
                                                                <label class="btn-remove cursor-pointer" style="padding: 12px 24px; background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px;">
                                                                    <i class="fas fa-trash"></i> Remove
                                                                    <input type="checkbox" name="remove_{{ $item['key'] }}" value="1" class="hidden" onchange="removeLogo(this, 'logo_preview')">
                                                                </label>
                                                            @endif
                                                        </div>
                                                        <p class="hint">Recommended: 200x60px. PNG, JPG, WebP. Max 2MB.</p>
                                                    </div>
                                                </div>
                                                @break
                                            @case('color')
                                                <div class="flex items-center gap-4">
                                                    <input type="color" name="{{ $item['key'] }}" id="setting_{{ $item['key'] }}_picker"
                                                        value="{{ old($item['key'], $item['value'] ?? '#22C55E') }}"
                                                        class="h-12 w-20 rounded-lg border border-gray-200 cursor-pointer"
                                                        oninput="document.getElementById('setting_{{ $item['key'] }}').value = this.value">
                                                    <input type="text" name="{{ $item['key'] }}" id="setting_{{ $item['key'] }}"
                                                        value="{{ old($item['key'], $item['value'] ?? '#22C55E') }}"
                                                        class="form-group w-32" placeholder="#22C55E" maxlength="7"
                                                        oninput="document.getElementById('setting_{{ $item['key'] }}_picker').value = this.value">
                                                </div>
                                                @break
                                            @default
                                                <input type="{{ match($item['type']) { 'url' => 'url', 'tel' => 'tel', 'email' => 'email', default => 'text' } }}"
                                                    name="{{ $item['key'] }}" id="setting_{{ $item['key'] }}"
                                                    value="{{ old($item['key'], $item['value']) }}" placeholder="{{ $item['label'] }}">
                                        @endswitch
                                        <p class="hint">{{ match($item['type']) { 'url' => 'Enter full URL including https://', 'tel' => 'Enter phone number with country code', 'email' => 'Enter a valid email address', default => '' } }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach

                <div class="save-bar">
                    <a href="{{ route('frontend.site-settings.seed') }}" class="btn-secondary"
                        onclick="return confirm('This will create any missing default settings. Continue?')">
                        <i class="fas fa-seedling"></i> Reset Defaults
                    </a>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function switchTab(tab) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            document.getElementById('tab-' + tab).classList.add('active');
            document.querySelector(`.tab-btn[onclick*="'${tab}'"]`).classList.add('active');
        }
        function previewLogo(input, previewId) {
            const preview = document.getElementById(previewId);
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) { preview.src = e.target.result; preview.classList.remove('flex', 'items-center', 'justify-center'); }
                reader.readAsDataURL(file);
            }
        }
        function removeLogo(checkbox, previewId) {
            const preview = document.getElementById(previewId);
            if (checkbox.checked) {
                // Show placeholder icon
                preview.src = '';
                preview.classList.add('flex', 'items-center', 'justify-center');
                preview.innerHTML = '<i class="fas fa-image text-3xl"></i>';
                // Disable the file input so a new file can't be selected while removing
                const fileInput = checkbox.closest('.logo-upload-area').querySelector('input[type="file"]');
                if (fileInput) fileInput.disabled = true;
            } else {
                // Re-enable file input
                const fileInput = checkbox.closest('.logo-upload-area').querySelector('input[type="file"]');
                if (fileInput) fileInput.disabled = false;
            }
        }
    </script>
    @endpush
</x-app-layout>