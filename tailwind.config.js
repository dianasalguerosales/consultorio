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
            colors: {
                caine: {
                    azul: "#2D2B5B",
                    rosa: "#EE518E",
                    celeste: "#53C6D3",
                    verde: "#74BE69",
                    morado: "#8B70CD",
                    naranja: "#F4A654",
                    error: "#D64550",
                },
            },
            screens: {
                sm: '480px',
                md: '768px',
                lg: '1024px',
                xl: '1280px',
            },
        },
    },

    plugins: [forms],
};
