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
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                rentra: {
                    DEFAULT: 'var(--warna-utama)',
                    50: 'var(--warna-utama-50)',
                    100: 'var(--warna-utama-100)',
                    600: 'var(--warna-utama-600)',
                    700: 'var(--warna-utama-700)',
                    800: 'var(--warna-utama-800)',
                    ink: 'var(--warna-teks)',
                    accent: 'var(--warna-aksen)',
                    bg: 'var(--warna-latar)',
                    permukaan: 'var(--warna-permukaan)',
                    tepi: 'var(--warna-tepi)',
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
            borderRadius: {
                kartu: 'var(--radius-kartu)',
                tombol: 'var(--radius-tombol)',
            },
            boxShadow: {
                kartu: 'var(--bayangan-kartu)',
                angkat: 'var(--bayangan-angkat)',
            },
            transitionDuration: {
                250: '250ms',
            },
            keyframes: {
                'masuk-atas': {
                    '0%': { opacity: '0', transform: 'translateY(-8px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                'gedip': {
                    '0%, 100%': { opacity: '1' },
                    '50%': { opacity: '0.4' },
                },
                'geser-masuk': {
                    '0%': { opacity: '0', transform: 'translateX(16px)' },
                    '100%': { opacity: '1', transform: 'translateX(0)' },
                },
            },
            animation: {
                'masuk-atas': 'masuk-atas 250ms ease-out',
                'gedip': 'gedip 1.5s ease-in-out infinite',
                'geser-masuk': 'geser-masuk 250ms ease-out',
            },
        },
    },
    plugins: [forms],
};
