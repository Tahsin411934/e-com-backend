import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './Modules/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    DEFAULT: 'var(--primary-color)',
                    hover: 'var(--primary-hover)',
                    light: 'var(--primary-light)',
                    soft: 'var(--primary-soft)',
                },
                navy: { 800: '#1e3a8a', 900: '#1e3163' },
            },
            transitionProperty: { width: 'width', 'min-width': 'min-width' },
        }
    },

    plugins: [forms],
};