import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                sidan: {
                    950: '#07111f',
                    900: '#0F2D5B',
                    700: '#17457f',
                    500: '#22C55E',
                    400: '#4ade80',
                    100: '#dcfce7',
                },
            },

            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },

            boxShadow: {
                soft: '0 18px 50px rgba(15, 45, 91, 0.10)',
            },
        },
    },

    plugins: [forms],
};