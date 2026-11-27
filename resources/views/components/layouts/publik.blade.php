<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="{{ $rental->warna_aksen ?? '#0F766E' }}">

        <title>{{ isset($title) ? $title . ' - ' : '' }}{{ $rental->nama ?? config('app.name') }}</title>
        <meta name="description" content="Cek ketersediaan unit, kalkulator harga, dan Pemesanan langsung di {{ $rental->nama ?? 'Rentra' }}.">
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
        <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
        <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased transisi-halaman">
        <a href="#konten-utama" class="skip-link">Lewati ke konten</a>

        @if (session('status'))
            <span data-toast="{{ session('status') }}" data-jenis="sukses" class="hidden"></span>
        @endif

        <header class="border-b sticky top-0 z-10" style="background-color: var(--warna-permukaan); border-color: var(--warna-tepi);">
            <div class="max-w-4xl mx-auto h-14 px-4 sm:px-6 flex items-center justify-between">
                <a href="{{ isset($rental) ? url('/' . $rental->slug) : url('/') }}" class="flex items-center gap-2 min-w-0">
                    @isset($rental)
                        @if ($rental->logo_path)
                            <img src="{{ Storage::disk('public')->url($rental->logo_path) }}" alt="Logo {{ $rental->nama }}" class="h-7 w-7 rounded-lg object-cover shrink-0">
                        @else
                            <span class="text-rentra shrink-0"><x-logo variant="icon" class="h-6 w-6" /></span>
                        @endif
                        <span class="font-semibold text-rentra-ink truncate">{{ $rental->nama }}</span>
                    @else
                        <span class="text-rentra shrink-0"><x-logo variant="icon" class="h-6 w-6" /></span>
                        <span class="font-semibold text-rentra-ink">rentra</span>
                    @endisset
                </a>
                <div class="flex items-center gap-2 shrink-0">
                    <x-ganti-tema />
                    @if (isset($rental))
                        <a href="https://wa.me/{{ $rental->no_wa }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-tombol bg-status-tersedia text-white px-3 py-1.5 text-xs font-semibold hover:bg-green-700 transition">
                            Tanya WA
                        </a>
                    @endif
                </div>
            </div>
        </header>

        <main id="konten-utama" class="max-w-4xl mx-auto px-4 sm:px-6 py-6 pb-24 sm:pb-6">
            {{ $slot }}
        </main>

        <nav class="sm:hidden fixed bottom-0 inset-x-0 z-10 border-t pb-[env(safe-area-inset-bottom)]" style="background-color: var(--warna-permukaan); border-color: var(--warna-tepi);" aria-label="Navigasi bawah">
            <div class="flex items-center justify-around h-14">
                <a href="{{ isset($rental) ? url('/' . $rental->slug) : url('/') }}" class="flex flex-col items-center gap-1 text-rentra px-3 py-2">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 10l9-7 9 7v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V10Z"/></svg>
                    <span class="text-[10px] font-semibold">Beranda</span>
                </a>
                <a href="https://wa.me/{{ $rental->no_wa ?? '' }}" target="_blank" rel="noopener" class="flex flex-col items-center gap-1 text-[var(--warna-teks-redup)] px-3 py-2">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.66 15l-1.3 4.7 4.8-1.25A10 10 0 1 0 12 2Z"/></svg>
                    <span class="text-[10px] font-semibold">Chat</span>
                </a>
                <button type="button" onclick="window.toast('Pilih unit dulu untuk booking', 'info')" class="flex flex-col items-center gap-1 text-[var(--warna-teks-redup)] px-3 py-2">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2h12l2 6H4l2-6ZM4 8v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8M9 12h6"/></svg>
                    <span class="text-[10px] font-semibold">Pesan</span>
                </button>
            </div>
        </nav>

        <footer class="max-w-4xl mx-auto px-4 sm:px-6 py-6 border-t text-center text-xs text-[var(--warna-teks-redup)]" style="border-color: var(--warna-tepi);">
            Dibuat dengan <a href="{{ url('/') }}" class="font-semibold text-rentra hover:underline">Rentra</a>
        </footer>

        <x-toast />
    </body>
</html>
