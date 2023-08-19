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
        screens: {
            'laptop': '1360px',
            '1080': '1920px',
            '1440': '2560px',
            '4k': '3840px',
        },
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            dropShadow: {
                'md': '0 4px 6px rgba(0, 0, 0, 0.3)',
            },
            gridTemplateColumns: {
                // Simple 16 column grid
                'auto': 'repeat(auto-fit, minmax(10rem, 1fr))',
            },
        },
    },

    plugins: [forms],
};
