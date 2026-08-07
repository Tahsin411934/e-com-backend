<!-- ========== FLOATING THEME CUSTOMIZER ========== -->
<!-- Styles are in resources/css/theme.css -->
<!-- JS logic is in public/js/theme.js (ThemeManager) -->

<!-- Floating Button -->
<button id="themeToggleBtn"
    class="fixed bottom-6 right-6 w-12 h-12 rounded-full btn-primary shadow-lg flex items-center justify-center z-50 hover:scale-110 transition-transform"
    title="Theme Customizer"
    aria-label="Open theme customizer"
    aria-expanded="false"
    aria-controls="themeCustomizer">
    <i class="fas fa-palette text-xl" aria-hidden="true"></i>
</button>

<!-- Theme Customizer Panel -->
<div id="themeCustomizer"
    class="fixed top-0 right-0 h-full w-80 bg-white dark:bg-gray-800 shadow-2xl z-[60] flex flex-col border-l border-gray-200 dark:border-gray-700"
    role="dialog"
    aria-modal="true"
    aria-label="Theme customizer panel">

    <!-- Header -->
    <div class="theme-navbar text-white px-5 py-4 flex items-center justify-between flex-shrink-0">
        <div>
            <h3 class="font-bold text-sm">Theme Customizer</h3>
            <p class="text-[11px] opacity-80">Customize your dashboard</p>
        </div>
        <button data-customizer-close
            class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors"
            aria-label="Close theme customizer">
            <i class="fas fa-times text-sm" aria-hidden="true"></i>
        </button>
    </div>

    <div class="flex-1 overflow-y-auto p-5 space-y-6">

        <!-- Mode Toggle -->
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">Mode</p>
            <div class="grid grid-cols-2 gap-3" role="radiogroup" aria-label="Color mode">
                <button id="lightModeBtn"
                    class="mode-btn flex flex-col items-center gap-2 p-4 rounded-xl border-2 transition-all"
                    role="radio"
                    aria-checked="true"
                    aria-label="Light mode">
                    <i class="fas fa-sun text-2xl" aria-hidden="true"></i>
                    <span class="text-sm font-semibold">Light</span>
                </button>
                <button id="darkModeBtn"
                    class="mode-btn flex flex-col items-center gap-2 p-4 rounded-xl border-2 transition-all"
                    role="radio"
                    aria-checked="false"
                    aria-label="Dark mode">
                    <i class="fas fa-moon text-2xl" aria-hidden="true"></i>
                    <span class="text-sm font-semibold">Dark</span>
                </button>
            </div>
        </div>

        <!-- Predefined Colors -->
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">Preset Colors</p>
            <div class="flex flex-wrap gap-2.5" role="group" aria-label="Preset colors">
                <button class="color-swatch" style="background:#1e3a8a" data-color="#1e3a8a" title="Navy" aria-label="Navy color"></button>
                <button class="color-swatch" style="background:#2563eb" data-color="#2563eb" title="Blue" aria-label="Blue color"></button>
                <button class="color-swatch" style="background:#7c3aed" data-color="#7c3aed" title="Violet" aria-label="Violet color"></button>
                <button class="color-swatch" style="background:#0d9488" data-color="#0d9488" title="Teal" aria-label="Teal color"></button>
                <button class="color-swatch" style="background:#059669" data-color="#059669" title="Emerald" aria-label="Emerald color"></button>
                <button class="color-swatch" style="background:#d97706" data-color="#d97706" title="Amber" aria-label="Amber color"></button>
                <button class="color-swatch" style="background:#dc2626" data-color="#dc2626" title="Red" aria-label="Red color"></button>
                <button class="color-swatch" style="background:#db2777" data-color="#db2777" title="Pink" aria-label="Pink color"></button>
                <button class="color-swatch" style="background:#334155" data-color="#334155" title="Slate" aria-label="Slate color"></button>
                <button class="color-swatch" style="background:#0f172a" data-color="#0f172a" title="Dark" aria-label="Dark color"></button>
            </div>
        </div>

        <!-- Custom Color Picker -->
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">Custom Color</p>
            <div class="flex items-center gap-3">
                <label for="customColorPicker" class="sr-only">Custom color picker</label>
                <input type="color" id="customColorPicker"
                    class="w-12 h-10 rounded-lg cursor-pointer border-0 bg-transparent p-0"
                    value="#1e3a8a" />
                <label for="customColorHex" class="sr-only">Custom color hex code</label>
                <input type="text" id="customColorHex"
                    class="flex-1 px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary"
                    value="#1E3A8A" maxlength="7" placeholder="#1E3A8A" />
            </div>
            <p class="text-[11px] text-gray-400 mt-2">Type a hex code or pick from the color wheel</p>
        </div>

        <!-- Reset -->
        <div>
            <button id="resetThemeBtn"
                class="w-full py-2.5 px-4 rounded-lg border-2 border-gray-200 dark:border-gray-600 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-primary"
                aria-label="Reset theme to default">
                <i class="fas fa-undo mr-2" aria-hidden="true"></i>Reset to Default
            </button>
        </div>
    </div>

    <!-- Footer -->
    <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 flex-shrink-0">
        <p class="text-[11px] text-gray-400 text-center">Changes are saved automatically</p>
    </div>
</div>