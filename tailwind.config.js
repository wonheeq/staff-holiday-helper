import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            dropShadow: {
                'md': '0 4px 6px rgba(0, 0, 0, 0.3)',
            },
            height: {
                'shortcuts': '30rem',
                'calendar': '72.5rem',
                'messages': '41rem',
            },
        },
    },

    plugins: [forms],
};
