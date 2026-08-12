<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            📣 Marketing - Google Tag Manager
        </h2>
    </x-slot>

    <div class="p-4 lg:p-6 max-w-[900px] mx-auto">
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
            <span>Frontend</span>
            <i class="fas fa-chevron-right text-[10px]"></i>
            <span class="text-gray-800 font-medium">Marketing</span>
            <i class="fas fa-chevron-right text-[10px]"></i>
            <span class="text-gray-800 font-medium">GTM</span>
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

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 lg:p-8">
            <div class="flex items-start gap-4 mb-6 pb-6 border-b border-gray-100">
                <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
                    <i class="fas fa-tags text-amber-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Google Tag Manager</h3>
                    <p class="text-sm text-gray-500 mt-0.5">
                        Configure GTM container details. The GTM scripts will be injected dynamically into the frontend.
                        If <strong>Enable GTM</strong> is off, no scripts will load.
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('frontend.marketing.gtm.update') }}">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                    @foreach($items as $item)
                        <div class="form-group {{ in_array($item['type'], ['textarea']) ? 'md:col-span-2' : '' }}">
                            <label for="setting_{{ $item['key'] }}">{{ $item['label'] }}</label>

                            @if($item['key'] === 'gtm_enabled')
                                {{-- Toggle switch for GTM enabled/disabled --}}
                                <div class="flex items-center gap-3 mt-1">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="hidden" name="gtm_enabled" value="0">
                                        <input type="checkbox" name="gtm_enabled" value="1"
                                            id="gtm_enabled_toggle"
                                            class="sr-only peer"
                                            {{ old('gtm_enabled', $item['value'] ?? '0') == '1' ? 'checked' : '' }}
                                            onchange="document.getElementById('gtm_status_badge').textContent = this.checked ? 'ENABLED' : 'DISABLED'; document.getElementById('gtm_status_badge').className = this.checked ? 'text-xs font-bold px-2.5 py-1 rounded-full ' + 'bg-green-100 text-green-700' : 'text-xs font-bold px-2.5 py-1 rounded-full bg-gray-100 text-gray-500';">
                                        <span class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></span>
                                        <span class="ms-3 text-sm font-medium text-gray-600" id="gtm_status_badge"
                                            class="text-xs font-bold px-2.5 py-1 rounded-full {{ old('gtm_enabled', $item['value'] ?? '0') == '1' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                            {{ old('gtm_enabled', $item['value'] ?? '0') == '1' ? 'ENABLED' : 'DISABLED' }}
                                        </span>
                                    </label>
                                </div>
                            @elseif($item['type'] === 'textarea')
                                <textarea name="{{ $item['key'] }}" id="setting_{{ $item['key'] }}" rows="5"
                                    class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-mono focus:border-primary focus:ring-2 focus:ring-primary-light transition-all"
                                    placeholder="{{ $item['key'] === 'gtm_header_code' ? 'Paste the full <script> snippet from GTM here...' : 'Paste the <noscript> snippet from GTM here...' }}">{{ old($item['key'], $item['value']) }}</textarea>
                            @else
                                <input type="{{ $item['type'] === 'url' ? 'url' : 'text' }}"
                                    name="{{ $item['key'] }}" id="setting_{{ $item['key'] }}"
                                    value="{{ old($item['key'], $item['value']) }}"
                                    placeholder="{{ $item['key'] === 'gtm_id' ? 'e.g. GTM-XXXXXXX' : ($item['key'] === 'gtm_container_url' ? 'https://www.googletagmanager.com/gtm.js?id=GTM-XXXXXXX' : '') }}"
                                    class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary-light transition-all"
                                    {{ $item['key'] === 'gtm_id' || $item['key'] === 'gtm_container_url' ? "oninput=\"updateContainerPreview(this)\"" : '' }}>
                            @endif

                            @if($item['key'] === 'gtm_id')
                                <p class="hint mt-1 text-xs text-gray-400">Your GTM Container ID, e.g. <code>GTM-ABCDEFG</code></p>
                            @elseif($item['key'] === 'gtm_container_url')
                                <p class="hint mt-1 text-xs text-gray-400">Optional. If left empty, the default GTM URL will be generated from the Container ID.</p>
                            @elseif($item['key'] === 'gtm_header_code')
                                <p class="hint mt-1 text-xs text-gray-400">This is injected in the <code><head></code> of the frontend.</p>
                            @elseif($item['key'] === 'gtm_body_code')
                                <p class="hint mt-1 text-xs text-gray-400">This is injected at the start of the <code><body></code> of the frontend.</p>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-code text-gray-400 text-sm"></i>
                        <span class="text-sm font-medium text-gray-700">Live Preview</span>
                    </div>
                    <pre class="text-xs text-gray-500 font-mono whitespace-pre-wrap" id="gtm_preview">Enter a GTM Container ID above to see the script preview.</pre>
                </div>

                <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-100">
                    <a href="{{ route('frontend.site-settings.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Site Settings
                    </a>
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-hover transition-all shadow-sm">
                        <i class="fas fa-save"></i> Save GTM Settings
                    </button>
                </div>
            </form>
        </div>

        {{-- How it works --}}
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-2xl p-5">
            <div class="flex items-start gap-3">
                <i class="fas fa-circle-info text-blue-500 text-lg mt-0.5"></i>
                <div class="text-sm text-blue-900">
                    <strong class="font-semibold">How it works:</strong>
                    <ol class="list-decimal ml-4 mt-2 space-y-1 text-blue-800">
                        <li>Paste your GTM Container ID (e.g. <code>GTM-XXXXXXX</code>).</li>
                        <li>The standard GTM script is generated dynamically and injected into the frontend automatically.</li>
                        <li>Alternatively, paste your full GTM header/body snippets from Google Tag Manager for full control.</li>
                        <li>Toggle <strong>Enable GTM</strong> off anytime to stop all GTM scripts from loading.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function updateContainerPreview(input) {
            const gtmId = document.querySelector('input[name="gtm_id"]').value.trim();
            const containerUrl = document.querySelector('input[name="gtm_container_url"]').value.trim();
            const preview = document.getElementById('gtm_preview');

            if (!gtmId) {
                preview.textContent = 'Enter a GTM Container ID above to see the script preview.';
                return;
            }

            const url = containerUrl || `https://www.googletagmanager.com/gtm.js?id=${gtmId}`;
            preview.textContent = `<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'${url}'+i;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','${gtmId}');<\/script>
<!-- End Google Tag Manager -->

<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=${gtmId}"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->`;
        }
    </script>
    @endpush
</x-app-layout>