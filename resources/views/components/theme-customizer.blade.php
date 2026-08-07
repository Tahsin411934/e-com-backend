<style>
    :root {
        --primary-color: #1e3a8a;
        --primary-hover: #1e40af;
        --primary-light: #dbeafe;
        --primary-soft: #eff6ff;
        --primary-text: #ffffff;
    }

    /* Apply primary color */
    .bg-primary { background-color: var(--primary-color) !important; }
    .bg-primary-hover:hover { background-color: var(--primary-hover) !important; }
    .text-primary { color: var(--primary-color) !important; }
    .border-primary { border-color: var(--primary-color) !important; }
    .bg-primary-light { background-color: var(--primary-light) !important; }
    .bg-primary-soft { background-color: var(--primary-soft) !important; }

    /* Navbar & Sidebar header */
    .theme-navbar { background-color: var(--primary-color) !important; }
    .theme-navbar-btn { background-color: var(--primary-hover) !important; }
    .theme-navbar-btn:hover { background-color: var(--primary-color) !important; }

    /* Primary buttons */
    .btn-primary {
        background-color: var(--primary-color) !important;
        border-color: var(--primary-color) !important;
        color: var(--primary-text) !important;
    }
    .btn-primary:hover {
        background-color: var(--primary-hover) !important;
        border-color: var(--primary-hover) !important;
    }

    /* Active nav item */
    .nav-item.active {
        background-color: var(--primary-color) !important;
        color: #fff !important;
    }

    /* Submenu active links */
    .submenu a.text-blue-600 { color: var(--primary-color) !important; }
    .submenu a.bg-blue-50 { background-color: var(--primary-light) !important; }

    /* Gradients */
    .bg-gradient-to-br, .bg-gradient-to-r, .bg-gradient-to-t,
    .bg-gradient-to-b, .bg-gradient-to-l, .bg-gradient-to-tr,
    .bg-gradient-to-tl, .bg-gradient-to-bl {
        background-image: linear-gradient(to bottom right, var(--primary-color), var(--primary-hover)) !important;
    }
    .bg-gradient-to-r { background-image: linear-gradient(to right, var(--primary-color), var(--primary-hover)) !important; }
    .bg-gradient-to-l { background-image: linear-gradient(to left, var(--primary-color), var(--primary-hover)) !important; }
    .bg-gradient-to-t { background-image: linear-gradient(to top, var(--primary-color), var(--primary-hover)) !important; }
    .bg-gradient-to-b { background-image: linear-gradient(to bottom, var(--primary-color), var(--primary-hover)) !important; }
    .bg-gradient-to-tr { background-image: linear-gradient(to top right, var(--primary-color), var(--primary-hover)) !important; }
    .bg-gradient-to-tl { background-image: linear-gradient(to top left, var(--primary-color), var(--primary-hover)) !important; }
    .bg-gradient-to-bl { background-image: linear-gradient(to bottom left, var(--primary-color), var(--primary-hover)) !important; }

    /* Blue buttons auto-map to primary */
    .bg-blue-900, .bg-blue-800, .bg-blue-700, .bg-blue-600, .bg-blue-500,
    .bg-blue-950, .bg-blue-400, .bg-blue-300 {
        background-color: var(--primary-color) !important;
    }
    .hover\:bg-blue-700:hover, .hover\:bg-blue-600:hover, .hover\:bg-blue-800:hover {
        background-color: var(--primary-hover) !important;
    }

    /* Theme customizer panel */
    #themeCustomizer {
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s;
        transform: translateX(110%);
        opacity: 0;
        pointer-events: none;
    }
    #themeCustomizer.open {
        transform: translateX(0);
        opacity: 1;
        pointer-events: auto;
    }

    /* Floating button pulse */
    @keyframes themePulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(30, 58, 138, 0.4); }
        50% { box-shadow: 0 0 0 12px rgba(30, 58, 138, 0); }
    }
    #themeToggleBtn { animation: themePulse 2.5s infinite; }

    /* Color swatches */
    .color-swatch {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.2s;
        position: relative;
    }
    .color-swatch:hover { transform: scale(1.15); }
    .color-swatch.active {
        border-color: #fff;
        box-shadow: 0 0 0 2px var(--primary-color), 0 2px 6px rgba(0,0,0,0.2);
    }
    .color-swatch.active::after {
        content: '✓';
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 12px;
        font-weight: bold;
        text-shadow: 0 1px 2px rgba(0,0,0,0.4);
    }

    /* Smooth transitions */
    body, .dashboard-card, .bg-white, .bg-gray-50, .bg-gray-100 {
        transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
    }

    /* Dark mode overrides */
    .dark .dashboard-card { background: #1f2937 !important; border-color: #374151 !important; }
    .dark .bg-white.shadow-md.rounded-xl { background: #1f2937 !important; border-color: #374151 !important; }
    .dark .bg-gray-50\/50 { background-color: #1f2937 !important; }
    .dark table { color: #e5e7eb !important; }
    .dark table thead th { color: #9ca3af !important; }
    .dark table tbody td { color: #e5e7eb !important; }
    .dark table tbody tr { border-color: #374151 !important; }
    .dark table tbody tr:hover { background-color: #374151 !important; }
    .dark .divide-y.divide-gray-100 > * { border-color: #374151 !important; }
    .dark .bg-white { background-color: #1f2937 !important; }
    .dark .bg-gray-50 { background-color: #111827 !important; }
    .dark .bg-gray-100 { background-color: #1f2937 !important; }
    .dark .bg-gray-200 { background-color: #374151 !important; }
    .dark .bg-gray-300 { background-color: #4b5563 !important; }
    .dark .bg-gray-800 { background-color: #1f2937 !important; }
    .dark .bg-gray-900 { background-color: #111827 !important; }
    .dark .text-gray-900 { color: #f9fafb !important; }
    .dark .text-gray-800 { color: #f3f4f6 !important; }
    .dark .text-gray-700 { color: #e5e7eb !important; }
    .dark .text-gray-600 { color: #d1d5db !important; }
    .dark .text-gray-500 { color: #9ca3af !important; }
    .dark .text-gray-400 { color: #6b7280 !important; }
    .dark .border-gray-200 { border-color: #374151 !important; }
    .dark .border-gray-100 { border-color: #374151 !important; }
    .dark .divide-gray-100 > * { border-color: #374151 !important; }
    .dark .hover\:bg-gray-50:hover { background-color: #1f2937 !important; }
    .dark .hover\:bg-gray-100:hover { background-color: #1f2937 !important; }
    .dark .hover\:text-gray-800:hover { color: #f3f4f6 !important; }
    .dark .hover\:text-gray-700:hover { color: #e5e7eb !important; }
    .dark .hover\:text-gray-600:hover { color: #d1d5db !important; }
    .dark .bg-indigo-50 { background-color: #1e3a8a !important; }
    .dark .bg-emerald-50 { background-color: #064e3b !important; }
    .dark .bg-rose-50 { background-color: #881337 !important; }
    .dark .bg-amber-50 { background-color: #78350f !important; }
    .dark .bg-violet-50 { background-color: #4c1d95 !important; }
    .dark .bg-cyan-50 { background-color: #164e63 !important; }
    .dark .bg-indigo-100 { background-color: #1e3a8a !important; }
    .dark .bg-emerald-100 { background-color: #064e3b !important; }
    .dark .bg-rose-100 { background-color: #881337 !important; }
    .dark .bg-amber-100 { background-color: #78350f !important; }
    .dark .bg-violet-100 { background-color: #4c1d95 !important; }
    .dark .bg-cyan-100 { background-color: #164e63 !important; }
    .dark .text-indigo-600 { color: #818cf8 !important; }
    .dark .text-emerald-600 { color: #34d399 !important; }
    .dark .text-rose-600 { color: #fb7185 !important; }
    .dark .text-amber-600 { color: #fbbf24 !important; }
    .dark .text-violet-600 { color: #a78bfa !important; }
    .dark .text-cyan-600 { color: #22d3ee !important; }
    .dark .text-indigo-700 { color: #a5b4fc !important; }
    .dark .text-emerald-700 { color: #6ee7b7 !important; }
    .dark .text-rose-700 { color: #fda4af !important; }
    .dark .text-amber-700 { color: #fcd34d !important; }
    .dark .text-violet-700 { color: #c4b5fd !important; }
    .dark .text-cyan-700 { color: #67e8f9 !important; }
</style>

<!-- Floating Button -->
<button id="themeToggleBtn"
    class="fixed bottom-6 right-6 w-12 h-12 rounded-full btn-primary shadow-lg flex items-center justify-center z-50 hover:scale-110 transition-transform" title="Theme Customizer">
    <i class="fas fa-palette text-xl"></i>
</button>

<!-- Theme Customizer Panel -->
<div id="themeCustomizer"
    class="fixed top-0 right-0 h-full w-80 bg-white dark:bg-gray-800 shadow-2xl z-[60] flex flex-col border-l border-gray-200 dark:border-gray-700">

    <!-- Header -->
    <div class="theme-navbar text-white px-5 py-4 flex items-center justify-between flex-shrink-0">
        <div>
            <h3 class="font-bold text-sm">Theme Customizer</h3>
            <p class="text-[11px] opacity-80">Customize your dashboard</p>
        </div>
        <button onclick="document.getElementById('themeCustomizer').classList.remove('open')"
            class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors">
            <i class="fas fa-times text-sm"></i>
        </button>
    </div>

    <div class="flex-1 overflow-y-auto p-5 space-y-6">

        <!-- Mode Toggle -->
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">Mode</p>
            <div class="grid grid-cols-2 gap-3">
                <button id="lightModeBtn"
                    class="mode-btn flex flex-col items-center gap-2 p-4 rounded-xl border-2 transition-all">
                    <i class="fas fa-sun text-2xl"></i>
                    <span class="text-sm font-semibold">Light</span>
                </button>
                <button id="darkModeBtn"
                    class="mode-btn flex flex-col items-center gap-2 p-4 rounded-xl border-2 bg-gray-900 text-gray-100 border-gray-900 transition-all">
                    <i class="fas fa-moon text-2xl"></i>
                    <span class="text-sm font-semibold">Dark</span>
                </button>
            </div>
        </div>

        <!-- Predefined Colors -->
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">Preset Colors</p>
            <div class="flex flex-wrap gap-2.5">
                <button class="color-swatch" style="background:#1e3a8a" data-color="#1e3a8a" title="Navy"></button>
                <button class="color-swatch" style="background:#2563eb" data-color="#2563eb" title="Blue"></button>
                <button class="color-swatch" style="background:#7c3aed" data-color="#7c3aed" title="Violet"></button>
                <button class="color-swatch" style="background:#0d9488" data-color="#0d9488" title="Teal"></button>
                <button class="color-swatch" style="background:#059669" data-color="#059669" title="Emerald"></button>
                <button class="color-swatch" style="background:#d97706" data-color="#d97706" title="Amber"></button>
                <button class="color-swatch" style="background:#dc2626" data-color="#dc2626" title="Red"></button>
                <button class="color-swatch" style="background:#db2777" data-color="#db2777" title="Pink"></button>
                <button class="color-swatch" style="background:#334155" data-color="#334155" title="Slate"></button>
                <button class="color-swatch" style="background:#0f172a" data-color="#0f172a" title="Dark"></button>
            </div>
        </div>

        <!-- Custom Color Picker -->
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">Custom Color</p>
            <div class="flex items-center gap-3">
                <input type="color" id="customColorPicker"
                    class="w-12 h-10 rounded-lg cursor-pointer border-0 bg-transparent p-0"
                    value="#1e3a8a" />
                <input type="text" id="customColorHex"
                    class="flex-1 px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    value="#1E3A8A" maxlength="7" />
            </div>
            <p class="text-[11px] text-gray-400 mt-2">Type a hex code or pick from the color wheel</p>
        </div>

        <!-- Reset -->
        <div>
            <button id="resetThemeBtn"
                class="w-full py-2.5 px-4 rounded-lg border-2 border-gray-200 dark:border-gray-600 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <i class="fas fa-undo mr-2"></i>Reset to Default
            </button>
        </div>
    </div>

    <!-- Footer -->
    <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 flex-shrink-0">
        <p class="text-[11px] text-gray-400 text-center">Changes are saved automatically</p>
    </div>
</div>

<script>
(function() {
    const DEFAULT_PRIMARY = '#1e3a8a';
    const DEFAULT_HOVER = '#1e40af';
    const DEFAULT_LIGHT = '#dbeafe';
    const DEFAULT_SOFT = '#eff6ff';

    function shade(hex, percent) {
        const num = parseInt(hex.replace('#', ''), 16);
        const amt = Math.round(2.55 * percent);
        const r = Math.min(255, Math.max(0, (num >> 16) + amt));
        const g = Math.min(255, Math.max(0, ((num >> 8) & 0x00FF) + amt));
        const b = Math.min(255, Math.max(0, (num & 0x0000FF) + amt));
        return '#' + (0x1000000 + (r << 16) + (g << 8) + b).toString(16).slice(1);
    }

    function applyPrimary(hex) {
        const root = document.documentElement;
        root.style.setProperty('--primary-color', hex);
        root.style.setProperty('--primary-hover', shade(hex, -10));
        root.style.setProperty('--primary-light', shade(hex, 80));
        root.style.setProperty('--primary-soft', shade(hex, 90));

        // Custom picker sync
        const picker = document.getElementById('customColorPicker');
        const hexInput = document.getElementById('customColorHex');
        if (picker) picker.value = hex;
        if (hexInput) hexInput.value = hex.toUpperCase();

        // Active swatch highlight
        document.querySelectorAll('.color-swatch').forEach(sw => {
            sw.classList.toggle('active', sw.dataset.color === hex);
        });

        saveTheme(hex, document.documentElement.classList.contains('dark'));
    }

    function saveTheme(color, dark) {
        try {
            localStorage.setItem('theme-primary', color);
            localStorage.setItem('theme-dark', dark ? '1' : '0');
        } catch(e) {}
    }

    function loadTheme() {
        let color = DEFAULT_PRIMARY;
        let dark = false;
        try {
            color = localStorage.getItem('theme-primary') || DEFAULT_PRIMARY;
            dark = localStorage.getItem('theme-dark') === '1';
        } catch(e) {}

        const lightBtn = document.getElementById('lightModeBtn');
        const darkBtn = document.getElementById('darkModeBtn');

        if (dark) {
            document.documentElement.classList.add('dark');
            darkBtn.classList.add('bg-gray-900', 'text-gray-100', 'border-gray-900');
            darkBtn.classList.remove('border-gray-300');
            lightBtn.classList.remove('bg-gray-900', 'text-gray-100', 'border-gray-900');
            lightBtn.classList.add('border-gray-300');
        } else {
            document.documentElement.classList.remove('dark');
            lightBtn.classList.add('bg-gray-900', 'text-gray-100', 'border-gray-900');
            lightBtn.classList.remove('border-gray-300');
            darkBtn.classList.remove('bg-gray-900', 'text-gray-100', 'border-gray-900');
            darkBtn.classList.add('border-gray-300');
        }
        applyPrimary(color);
    }

    document.addEventListener('DOMContentLoaded', function() {
        loadTheme();

        // Toggle panel
        document.getElementById('themeToggleBtn').addEventListener('click', function() {
            document.getElementById('themeCustomizer').classList.toggle('open');
        });

        // Mode buttons
        document.getElementById('lightModeBtn').addEventListener('click', function() {
            document.documentElement.classList.remove('dark');
            this.classList.add('bg-gray-900', 'text-gray-100', 'border-gray-900');
            this.classList.remove('border-gray-300');
            document.getElementById('darkModeBtn').classList.remove('bg-gray-900', 'text-gray-100', 'border-gray-900');
            document.getElementById('darkModeBtn').classList.add('border-gray-300');
            saveTheme(document.documentElement.style.getPropertyValue('--primary-color') || DEFAULT_PRIMARY, false);
        });

        document.getElementById('darkModeBtn').addEventListener('click', function() {
            document.documentElement.classList.add('dark');
            this.classList.add('bg-gray-900', 'text-gray-100', 'border-gray-900');
            this.classList.remove('border-gray-300');
            document.getElementById('lightModeBtn').classList.remove('bg-gray-900', 'text-gray-100', 'border-gray-900');
            document.getElementById('lightModeBtn').classList.add('border-gray-300');
            saveTheme(document.documentElement.style.getPropertyValue('--primary-color') || DEFAULT_PRIMARY, true);
        });

        // Preset swatches
        document.querySelectorAll('.color-swatch').forEach(function(swatch) {
            swatch.addEventListener('click', function() {
                applyPrimary(this.dataset.color);
            });
        });

        // Color picker
        const picker = document.getElementById('customColorPicker');
        const hexInput = document.getElementById('customColorHex');
        picker.addEventListener('input', function() {
            hexInput.value = this.value.toUpperCase();
            applyPrimary(this.value);
        });
        hexInput.addEventListener('change', function() {
            let val = this.value.replace('#', '').toUpperCase();
            if (/^[0-9A-F]{6}$/.test(val)) {
                applyPrimary('#' + val);
            }
        });
        hexInput.addEventListener('input', function() {
            let val = this.value.replace('#', '');
            if (/^[0-9A-Fa-f]{6}$/.test(val)) {
                picker.value = '#' + val.toUpperCase();
                applyPrimary('#' + val.toUpperCase());
            }
        });

        // Reset
        document.getElementById('resetThemeBtn').addEventListener('click', function() {
            document.documentElement.classList.remove('dark');
            document.getElementById('darkModeBtn').classList.remove('bg-gray-900', 'text-gray-100', 'border-gray-900');
            document.getElementById('darkModeBtn').classList.add('border-gray-300');
            document.getElementById('lightModeBtn').classList.add('bg-gray-900', 'text-gray-100', 'border-gray-900');
            document.getElementById('lightModeBtn').classList.remove('border-gray-300');
            applyPrimary(DEFAULT_PRIMARY);
            try {
                localStorage.removeItem('theme-primary');
                localStorage.removeItem('theme-dark');
            } catch(e) {}
        });
    });
})();
</script>