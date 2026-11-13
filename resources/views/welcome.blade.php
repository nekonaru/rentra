<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Rentra - Link publik untuk rental kamu</title>
        <meta name="description" content="Satu link untuk cek ketersediaan unit, kalkulator harga otomatis, booking, dan kirim bukti DP. Selesai urusan 'masih ready kak?'.">
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-rentra-bg text-rentra-ink">
        <header class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <span class="text-rentra"><x-logo /></span>
            <nav class="flex items-center gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center rounded-xl bg-rentra px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800 transition">Buka dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center rounded-xl px-4 py-2 text-sm font-semibold text-rentra border border-rentra/30 hover:bg-teal-50 transition">Masuk</a>
                    <a href="{{ route('register') }}" class="inline-flex items-center rounded-xl bg-rentra-accent px-4 py-2 text-sm font-semibold text-rentra-ink hover:bg-amber-400 transition">Daftar gratis</a>
                @endauth
            </nav>
        </header>

        <main>
            <!-- Hero -->
            <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-16 sm:pt-16 sm:pb-24 text-center">
                <span class="inline-flex items-center gap-2 rounded-full bg-teal-50 text-rentra text-xs font-semibold px-3 py-1">Untuk rental kamera, lensa, dan lainnya</span>
                <h1 class="mt-6 text-3xl sm:text-5xl font-bold tracking-tight text-rentra-ink leading-tight">Satu link, urus<br class="hidden sm:block"> semua booking rental.</h1>
                <p class="mt-5 max-w-xl mx-auto text-base sm:text-lg text-gray-600">Tunjukkan ketersediaan unit, hitung harga otomatis, terima booking dan bukti DP, semua dari satu halaman publik. Tanpa balas chat satu per satu.</p>
                <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl bg-rentra-accent px-6 py-3 text-base font-semibold text-rentra-ink hover:bg-amber-400 transition">Buka dashboard</a>
                    @else
                        <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl bg-rentra-accent px-6 py-3 text-base font-semibold text-rentra-ink hover:bg-amber-400 transition">Buat link rental kamu</a>
                        <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl bg-rentra px-6 py-3 text-base font-semibold text-white hover:bg-teal-800 transition">Masuk</a>
                    @endauth
                </div>
            </section>

            <!-- Features -->
            <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-16 sm:pb-24">
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="rounded-xl bg-white border border-teal-50 shadow-sm p-6">
                        <div class="text-rentra font-bold text-lg">Ketersediaan real time</div>
                        <p class="mt-2 text-sm text-gray-600">Kalender publik menandai hari penuh dan hari tersedia. Pelanggan tahu unit kosong tanpa bertanya.</p>
                    </div>
                    <div class="rounded-xl bg-white border border-teal-50 shadow-sm p-6">
                        <div class="text-rentra font-bold text-lg">Kalkulator harga</div>
                        <p class="mt-2 text-sm text-gray-600">Tarif per hari dan per jam dihitung otomatis, termasuk toleransi dan minimal sewa. Sisa jam tidak pernah lebih mahal dari satu hari.</p>
                    </div>
                    <div class="rounded-xl bg-white border border-teal-50 shadow-sm p-6">
                        <div class="text-rentra font-bold text-lg">Anti bentrok</div>
                        <p class="mt-2 text-sm text-gray-600">Dua pelanggan tidak bisa booking unit dan jam yang sama. Sistem mengunci slot secara berurutan.</p>
                    </div>
                </div>
            </section>
        </main>

        <footer class="bg-white border-t border-teal-50">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row items-center justify-between gap-2 text-sm text-gray-500">
                <span>&copy; {{ date('Y') }} Rentra</span>
                <span>Dibuat dengan Rentra</span>
            </div>
        </footer>
    </body>
</html>
