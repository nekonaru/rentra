<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title . ' - ' : '' }}{{ $rental->nama ?? config('app.name') }}</title>
        <meta name="description" content="Cek ketersediaan unit, kalkulator harga, dan Pemesanan langsung di {{ $rental->nama ?? 'Rentra' }}.">
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
        <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

        <!-- Font -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Script -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-rentra-bg text-rentra-ink">
        <header class="bg-white border-b border-teal-50 sticky top-0 z-10">
            <div class="max-w-4xl mx-auto h-14 px-4 sm:px-6 flex items-center justify-between">
                <a href="{{ isset($rental) ? url('/' . $rental->slug) : url('/') }}" class="flex items-center gap-2 min-w-0">
                    <span class="text-rentra shrink-0"><x-logo variant="icon" class="h-6 w-6" /></span>
                    @isset($rental)
                        <span class="font-semibold text-rentra-ink truncate">{{ $rental->nama }}</span>
                    @else
                        <span class="font-semibold text-rentra-ink">rentra</span>
                    @endisset
                </a>
                <a href="https://wa.me/{{ $rental->no_wa ?? '' }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-xl bg-status-tersedia text-white px-3 py-1.5 text-xs font-semibold hover:bg-green-700 transition shrink-0">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 2.1.55 4.14 1.6 5.95L2.05 22l4.34-1.13a9.87 9.87 0 0 0 5.65 1.8h.01c5.46 0 9.9-4.45 9.9-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2Zm0 18.13h-.01a8.2 8.2 0 0 1-4.2-1.15l-.3-.18-2.48.65.66-2.42-.2-.31a8.17 8.17 0 0 1-1.26-4.37c0-4.53 3.69-8.22 8.24-8.22a8.2 8.2 0 0 1 5.82 2.41 8.18 8.18 0 0 1 2.4 5.83c0 4.53-3.7 8.22-8.16 8.22Z"/></svg>
                    Tanya WA
                </a>
            </div>
        </header>

        <main class="max-w-4xl mx-auto px-4 sm:px-6 py-6">
            {{ $slot }}
        </main>

        <footer class="max-w-4xl mx-auto px-4 sm:px-6 py-6 border-t border-teal-50 text-center text-xs text-gray-500">
            Dibuat dengan <a href="{{ url('/') }}" class="font-semibold text-rentra hover:underline">Rentra</a>
        </footer>
    </body>
</html>





