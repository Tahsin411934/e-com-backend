/**
 * Theme Utility - Centralized theme management
 * Dark/Light mode + Primary color customization
 */

window.ThemeManager = (function() {
    'use strict';

    const DEFAULT_PRIMARY = '#1e3a8a';
    const STORAGE_COLOR_KEY = 'theme-primary';
    const STORAGE_DARK_KEY = 'theme-dark';

    /**
     * Shade a hex color by percentage (-100 to 100)
     */
    function shade(hex, percent) {
        const num = parseInt(hex.replace('#', ''), 16);
        if (isNaN(num)) return hex;
        const amt = Math.round(2.55 * percent);
        const r = Math.min(255, Math.max(0, (num >> 16) + amt));
        const g = Math.min(255, Math.max(0, ((num >> 8) & 0x00FF) + amt));
        const b = Math.min(255, Math.max(0, (num & 0x0000FF) + amt));
        return '#' + (0x1000000 + (r << 16) + (g << 8) + b).toString(16).slice(1);
    }

    /**
     * Apply primary color to CSS variables
     */
    function applyPrimary(hex) {
        const root = document.documentElement;
        root.style.setProperty('--primary-color', hex);
        root.style.setProperty('--primary-hover', shade(hex, -10));
        root.style.setProperty('--primary-light', shade(hex, 80));
        root.style.setProperty('--primary-soft', shade(hex, 90));
        root.style.setProperty('--primary-rgb', hexToRgb(hex));

        // Sync custom picker if present
        const picker = document.getElementById('customColorPicker');
        const hexInput = document.getElementById('customColorHex');
        if (picker) picker.value = hex;
        if (hexInput) hexInput.value = hex.toUpperCase();

        // Active swatch highlight
        document.querySelectorAll('.color-swatch').forEach(sw => {
            sw.classList.toggle('active', sw.dataset.color === hex);
        });

        saveColor(hex);
    }

    /**
     * Convert hex to RGB string for rgba() usage
     */
    function hexToRgb(hex) {
        const num = parseInt(hex.replace('#', ''), 16);
        if (isNaN(num)) return '30, 58, 138';
        const r = (num >> 16) & 255;
        const g = (num >> 8) & 255;
        const b = num & 255;
        return r + ', ' + g + ', ' + b;
    }

    /**
     * Set dark mode on/off
     */
    function setDarkMode(isDark) {
        if (isDark) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        saveDark(isDark);
        updateModeButtons(isDark);
    }

    /**
     * Toggle dark mode
     */
    function toggleDarkMode() {
        setDarkMode(!document.documentElement.classList.contains('dark'));
    }

    /**
     * Update mode button styling in customizer panel
     */
    function updateModeButtons(dark) {
        const lightBtn = document.getElementById('lightModeBtn');
        const darkBtn = document.getElementById('darkModeBtn');
        if (!lightBtn || !darkBtn) return;

        if (dark) {
            darkBtn.classList.add('dark-mode-active');
            darkBtn.classList.remove('dark-mode-inactive');
            lightBtn.classList.add('dark-mode-inactive');
            lightBtn.classList.remove('dark-mode-active');
        } else {
            lightBtn.classList.add('dark-mode-active');
            lightBtn.classList.remove('dark-mode-inactive');
            darkBtn.classList.add('dark-mode-inactive');
            darkBtn.classList.remove('dark-mode-active');
        }
    }

    /**
     * Save color to localStorage
     */
    function saveColor(color) {
        try {
            localStorage.setItem(STORAGE_COLOR_KEY, color);
        } catch(e) {
            // Private mode - ignore
        }
    }

    /**
     * Save dark mode flag to localStorage
     */
    function saveDark(isDark) {
        try {
            localStorage.setItem(STORAGE_DARK_KEY, isDark ? '1' : '0');
        } catch(e) {
            // Private mode - ignore
        }
    }

    /**
     * Get saved color or default
     */
    function getSavedColor() {
        try {
            return localStorage.getItem(STORAGE_COLOR_KEY) || DEFAULT_PRIMARY;
        } catch(e) {
            return DEFAULT_PRIMARY;
        }
    }

    /**
     * Get saved dark mode flag
     */
    function getSavedDark() {
        try {
            return localStorage.getItem(STORAGE_DARK_KEY) === '1';
        } catch(e) {
            return false;
        }
    }

    /**
     * Check if user prefers dark mode from system
     */
    function systemPrefersDark() {
        return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    }

    /**
     * Determine effective dark mode (saved preference > system preference)
     */
    function getEffectiveDark() {
        const saved = localStorage.getItem(STORAGE_DARK_KEY);
        if (saved === null || saved === undefined) {
            // No saved preference - use system preference
            return systemPrefersDark();
        }
        return saved === '1';
    }

    /**
     * Load theme and apply (call on DOM ready)
     */
    function loadTheme() {
        applyPrimary(getSavedColor());
        setDarkMode(getEffectiveDark());
    }

    /**
     * Reset theme to defaults
     */
    function resetTheme() {
        try {
            localStorage.removeItem(STORAGE_COLOR_KEY);
            localStorage.removeItem(STORAGE_DARK_KEY);
        } catch(e) {}
        applyPrimary(DEFAULT_PRIMARY);
        setDarkMode(systemPrefersDark());
    }

    /**
     * Validate hex color input
     */
    function isValidHex(hex) {
        return /^#?([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/.test(hex);
    }

    /**
     * Initialize customizer panel event listeners (if present)
     */
    function initCustomizer() {
        const toggleBtn = document.getElementById('themeToggleBtn');
        const customizer = document.getElementById('themeCustomizer');

        if (toggleBtn && customizer) {
            toggleBtn.addEventListener('click', function() {
                customizer.classList.toggle('open');
            });

            // Close on outside click
            document.addEventListener('click', function(e) {
                if (customizer.classList.contains('open')) {
                    if (!customizer.contains(e.target) && e.target !== toggleBtn && !toggleBtn.contains(e.target)) {
                        customizer.classList.remove('open');
                    }
                }
            });

            // Close on Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && customizer.classList.contains('open')) {
                    customizer.classList.remove('open');
                }
            });

            // Focus trap
            const closeBtn = customizer.querySelector('[data-customizer-close]');
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    customizer.classList.remove('open');
                });
            }
        }

        const lightBtn = document.getElementById('lightModeBtn');
        const darkBtn = document.getElementById('darkModeBtn');
        if (lightBtn) lightBtn.addEventListener('click', function() { setDarkMode(false); });
        if (darkBtn) darkBtn.addEventListener('click', function() { setDarkMode(true); });

        // Preset swatches
        document.querySelectorAll('.color-swatch').forEach(function(swatch) {
            swatch.addEventListener('click', function() {
                applyPrimary(this.dataset.color);
            });
        });

        // Color picker
        const picker = document.getElementById('customColorPicker');
        const hexInput = document.getElementById('customColorHex');
        if (picker) {
            picker.addEventListener('input', function() {
                hexInput.value = this.value.toUpperCase();
                applyPrimary(this.value);
            });
        }
        if (hexInput) {
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
        }

        // Reset
        const resetBtn = document.getElementById('resetThemeBtn');
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                resetTheme();
            });
        }

        // Listen for system preference changes
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                // Only auto-switch if user hasn't set a manual preference
                if (localStorage.getItem(STORAGE_DARK_KEY) === null) {
                    setDarkMode(e.matches);
                }
            });
        }
    }

    // Public API
    return {
        DEFAULT_PRIMARY: DEFAULT_PRIMARY,
        shade: shade,
        applyPrimary: applyPrimary,
        setDarkMode: setDarkMode,
        toggleDarkMode: toggleDarkMode,
        loadTheme: loadTheme,
        resetTheme: resetTheme,
        initCustomizer: initCustomizer,
        getSavedColor: getSavedColor,
        getSavedDark: getSavedDark,
        isValidHex: isValidHex,
        systemPrefersDark: systemPrefersDark
    };
})();