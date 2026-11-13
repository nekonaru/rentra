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
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                rentra: {
                    DEFAULT: '#0F766E',
                    ink: '#0B1F24',
                    accent: '#F59E0B',
                    bg: '#F6FAF9',
                },
                status: {
                    tersedia: '#16A34A',
                    ditahan: '#F59E0B',
                    verifikasi: '#2563EB',
                    terkonfirmasi: '#0F766E',
                    terlambat: '#DC2626',
                    diblok: '#6B7280',
                },
            },
        },
    },

    plugins: [forms],
};
