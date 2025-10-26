import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms, typography],

    safelist: [
        'text-[#BB2528]',
        'text-[#244372]',
        'text-[#414f4f]',        
        'bg-[#BB2528]',
        'bg-[#244372]',
        'bg-[#414f4f]',
        'border-[#BB2528]',
        'border-[#244372]',
        'border-[#414f4f]',
    ],
};
