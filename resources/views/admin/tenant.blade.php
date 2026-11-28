<x-layouts.admin>
    <div class="flex items-center justify-between gap-3 flex-wrap">
        <div>
            <h1 class="text-xl font-bold text-white">Tenant</h1>
            <span class="text-xs text-white/50">{{ $tenant->total() }} rental terdaftar</span>
        </div>
        <a href="{{ route('admin.tenant.buat') }}" class="inline-flex items-center gap-2 rounded-xl bg-teal-500 px-4 py-2 text-sm font-bold text-white hover:bg-teal-600 transition">+ Tambah tenant</a>
        <a href="{{ route('admin.tenant.ekspor') }}" class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-semibold text-white/80 hover:text-white transition">Ekspor CSV</a>
    </div>

    @if (session('password_baru'))
        <div class="mt-4 rounded-xl bg-amber-900/40 border border-amber-500/60 p-4">
            <p class="text-sm font-bold text-amber-200">Password baru untuk tenant: <span class="text-white">{{ session('kredensial')['slug'] ?? '' }}</span></p>
            <p class="mt-2 text-2xl font-mono font-bold text-white select-all break-all">{{ session('password_baru') }}</p>
            <p class="mt-2 text-xs text-amber-300">Catat SEKARANG. Password ini tidak bisa dilihat lagi setelah halaman ditutup. Bagikan ke pemilik (email: <span class="text-white">{{ session('kredensial')['email_pemilik'] ?? '' }}</span>).</p>
        </div>
    @endif

    <form method="GET" action="{{ route('admin.tenant.index') }}" class="mt-4 flex gap-2">
        <input type="search" name="q" value="{{ $q }}" placeholder="Cari nama rental, slug, WA, nama/email pemilik..." class="flex-1 rounded-xl border-white/10 bg-white/5 text-white placeholder-white/30 focus:border-teal-400 focus:ring-teal-400 text-sm">
        <button type="submit" class="rounded-xl border border-white/10 bg-white/5 px-4 text-sm font-semibold text-white/80 hover:text-white hover:bg-white/10">Cari</button>
        @if ($q)
            <a href="{{ route('admin.tenant.index') }}" class="rounded-xl px-4 text-sm text-white/50 hover:text-white self-center">Reset</a>
        @endif
    </form>

    <div class="mt-4 overflow-x-auto rounded-xl border border-white/10">
        <table class="w-full text-sm min-w-[720px]">
            <thead class="bg-white/5 text-white/60 text-left">
                <tr>
                    <th class="px-4 py-3 font-semibold">Rental</th>
                    <th class="px-4 py-3 font-semibold">Pemilik</th>
                    <th class="px-4 py-3 font-semibold text-right">Unit</th>
                    <th class="px-4 py-3 font-semibold text-right">Pemesanan</th>
                    <th class="px-4 py-3 font-semibold text-right">Omzet</th>
                    <th class="px-4 py-3 font-semibold">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @foreach ($tenant as $rental)
                    <tr class="hover:bg-white/5">
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.tenant.show', $rental) }}" class="font-semibold text-teal-300 hover:underline">{{ $rental->nama }}</a>
                            <span class="block text-[11px] text-white/40">/{{ $rental->slug }}</span>
                        </td>
                        <td class="px-4 py-3 text-white/70">{{ $rental->user->name }}<span class="block text-[11px] text-white/40">{{ $rental->user->email }}</span></td>
                        <td class="px-4 py-3 text-right text-white/70">{{ $rental->unit_count }}</td>
                        <td class="px-4 py-3 text-right text-white/70">{{ $rental->pemesanan_count }}</td>
                        <td class="px-4 py-3 text-right text-white/70">Rp {{ number_format((float) ($rental->pemesanan_sum_total ?? 0), 0, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold uppercase {{ $rental->aktif ? 'bg-green-500/20 text-green-300' : 'bg-red-500/20 text-red-300' }}">{{ $rental->aktif ? 'Aktif' : 'Ditangguhkan' }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $tenant->links() }}</div>
</x-layouts.admin>
