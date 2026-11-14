<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex,nofollow">

        <title>{{ isset($title) ? $title . ' - ' : '' }}Admin Rentra</title>
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-rentra-ink text-white min-h-screen">
        <div class="min-h-screen flex">
            <aside class="w-56 shrink-0 border-r border-white/10 p-4 hidden sm:flex flex-col gap-1">
                <a href="{{ route('admin.index') }}" class="flex items-center gap-2 px-2 py-3 text-teal-300 font-bold text-lg tracking-tight">
                    <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l7 4v5c0 5-3.5 8-7 9-3.5-1-7-4-7-9V7l7-4Z"/></svg>
                    Rentra Admin
                </a>
                @php
                    $klathAktif = 'bg-white/10 text-teal-300';
                    $klathNormal = 'text-white/70 hover:bg-white/5 hover:text-white';
                @endphp
                <a href="{{ route('admin.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.index') ? $klathAktif : $klathNormal }}">Dashboard</a>
                <a href="{{ route('admin.tenant.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.tenant.*') ? $klathAktif : $klathNormal }}">Tenant</a>
                <div class="mt-auto pt-4 border-t border-white/10">
                    <div class="px-2 text-xs text-white/60">{{ Auth::user()->name }}</div>
                    <div class="px-2 text-[10px] text-white/30 truncate">{{ Auth::user()->email }}</div>
                    <form method="POST" action="{{ route('logout') }}" class="mt-2">
                        @csrf
                        <button type="submit" class="w-full text-start px-2 py-2 text-xs font-semibold text-red-300 hover:bg-white/5 rounded-lg">Keluar</button>
                    </form>
                </div>
            </aside>

            <main class="flex-1 min-w-0">
                <header class="sm:hidden border-b border-white/10 px-4 h-14 flex items-center justify-between">
                    <span class="font-bold text-teal-300">Rentra Admin</span>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="text-xs text-red-300">Keluar</button></form>
                </header>
                <div class="p-4 sm:p-6">
                    @if (session('status'))
                        <div class="mb-4 rounded-xl bg-teal-900/50 border border-teal-700 px-4 py-3 text-sm text-teal-200">{{ session('status') }}</div>
                    @endif
                    {{ $slot }}
                </div>
            </main>
        </div>
    </body>
</html>
