<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title . ' - ' : '' }}{{ config('app.name', 'Rentra') }}</title>
        <meta name="description" content="Rentra: link publik untuk rental kamu. Cek ketersediaan, kalkulator harga, dan Pemesanan online.">
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
        <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <a href="#konten-utama" class="skip-link">Lewati ke konten</a>

        @if (session('status'))
            <span data-toast="{{ session('status') }}" data-jenis="sukses" class="hidden"></span>
        @endif

        <div class="min-h-screen flex flex-col transisi-halaman">
            @include('layouts.navigation')

            @isset($header)
                <header class="border-b" style="background-color: var(--warna-permukaan); border-color: var(--warna-tepi);">
                    <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main id="konten-utama" class="flex-1">
                {{ $slot }}
            </main>

            <footer class="border-t" style="background-color: var(--warna-permukaan); border-color: var(--warna-tepi);">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row items-center justify-between gap-2 text-sm text-[var(--warna-teks-redup)]">
                    <span>&copy; {{ date('Y') }} {{ config('app.name') }}</span>
                    <span>Dibuat dengan Rentra</span>
                </div>
            </footer>
        </div>

        <x-toast />
    </body>
</html>
