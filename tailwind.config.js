import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                display: ['"Bricolage Grotesque"', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Taken from the Lekobosi logo: the maroon plate and the saffron cloche.
                brand: {
                    50: '#FBF3F3',
                    100: '#F6E2E2',
                    200: '#EBC0C1',
                    300: '#D98F91',
                    400: '#C15A5E',
                    500: '#A8343A',
                    600: '#9A2329',
                    700: '#8E1B1E',
                    800: '#6E1517',
                    900: '#4B0F11',
                },
                saffron: {
                    50: '#FEF6EA',
                    100: '#FDE9CC',
                    200: '#FAD399',
                    300: '#F6B963',
                    400: '#F2A744',
                    500: '#EE9A2D',
                    600: '#D17D12',
                    700: '#A9620E',
                },
            },
        },
    },

    plugins: [forms],
};
