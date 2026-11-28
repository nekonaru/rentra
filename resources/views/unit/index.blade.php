<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-rentra-ink leading-tight">Unit kamu</h2>
            <a href="{{ route('unit.create') }}" class="inline-flex items-center rounded-xl bg-rentra-accent px-4 py-2 text-xs font-semibold uppercase tracking-widest text-rentra-ink hover:bg-amber-400 transition">Tambah unit</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-xl bg-teal-50 border border-teal-100 text-rentra text-sm font-medium px-4 py-3">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-xl bg-red-50 border border-red-100 text-status-terlambat text-sm font-medium px-4 py-3">{{ session('error') }}</div>
            @endif

            <form method="GET" action="{{ route('unit.index') }}" class="mb-5 flex gap-2">
                <input type="search" name="q" value="{{ $q }}" placeholder="Cari unit, kategori, merek..." class="input flex-1">
                <button type="submit" class="tombol tombol-sekunder">Cari</button>
            </form>

            <form method="POST" action="{{ route('unit.aksi-massal') }}" id="form-massal" class="mb-4 flex flex-wrap items-center gap-2 kartu p-3">
                @csrf
                <select name="aksi" class="input w-auto text-sm" required>
                    <option value="aktifkan">Aktifkan yang dipilih</option>
                    <option value="nonaktifkan">Nonaktifkan yang dipilih</option>
                </select>
                <button type="submit" class="tombol tombol-sekunder text-xs">Terapkan</button>
                <span class="text-xs text-[var(--warna-teks-redup)]">Centang unit di bawah untuk aksi massal.</span>
            </form>

            @if ($unit->isEmpty())
                <div class="bg-white rounded-xl border border-teal-50 shadow-sm p-10 text-center">
                    <p class="text-rentra-ink font-semibold">Belum ada unit.</p>
                    <p class="mt-1 text-sm text-gray-500">Tambahkan unit pertama kamu supaya pelanggan bisa Pemesanan.</p>
                    <a href="{{ route('unit.create') }}" class="mt-4 inline-flex items-center rounded-xl bg-rentra-accent px-5 py-2 text-sm font-semibold text-rentra-ink hover:bg-amber-400 transition">Tambah unit</a>
                </div>
            @else
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($unit as $unit)
                        <div class="kartu overflow-hidden flex flex-col relative">
                            <div class="absolute top-2 left-2 z-10">
                                <input type="checkbox" form="form-massal" name="ids[]" value="{{ $unit->id }}" class="h-5 w-5 rounded border-gray-300 text-rentra focus:ring-rentra bg-white/90" aria-label="Pilih {{ $unit->nama }}">
                            </div>
                            @if ($unit->jalur_foto)
                                <img src="{{ Storage::disk('public')->url($unit->jalur_foto) }}" alt="Foto {{ $unit->nama }}" class="h-40 w-full object-cover">
                            @else
                                <div class="h-40 w-full bg-teal-50 flex items-center justify-center text-rentra/40">
                                    <svg viewBox="0 0 24 24" class="h-12 w-12" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h3l2-2h6l2 2h3a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1Z"/><circle cx="12" cy="13" r="4"/></svg>
                                </div>
                            @endif
                            <div class="p-4 flex flex-col flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <h3 class="font-semibold text-rentra-ink leading-snug">{{ $unit->nama }}</h3>
                                        @if ($unit->kategori)
                                            <p class="text-xs text-gray-500 mt-0.5">{{ $unit->kategori }}</p>
                                        @endif
                                    </div>
                                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold {{ $unit->aktif ? 'bg-green-50 text-status-tersedia' : 'bg-gray-100 text-status-diblok' }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $unit->aktif ? 'bg-status-tersedia' : 'bg-status-diblok' }}"></span>
                                        {{ $unit->aktif ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </div>
                                <p class="mt-3 text-sm font-semibold text-rentra-ink">
                                    Rp {{ number_format((float) ($unit->aturanTarif->tarif_per_hari ?? 0), 0, ',', '.') }}
                                    <span class="font-normal text-gray-500">/ hari</span>
                                    <span class="mx-1 text-gray-300">&middot;</span>
                                    Rp {{ number_format((float) ($unit->aturanTarif->tarif_per_jam ?? 0), 0, ',', '.') }}
                                    <span class="font-normal text-gray-500">/ jam</span>
                                </p>
                                <div class="mt-auto pt-4 flex items-center gap-2 text-xs">
                                    <a href="{{ route('unit.edit', $unit) }}" class="inline-flex items-center rounded-lg border border-rentra/30 text-rentra px-3 py-1.5 font-semibold hover:bg-teal-50">Ubah</a>
                                    <form method="POST" action="{{ route('unit.ganti-aktif', $unit) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="inline-flex items-center rounded-lg border border-gray-200 text-gray-600 px-3 py-1.5 font-semibold hover:bg-gray-50">{{ $unit->aktif ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('unit.destroy', $unit) }}" onsubmit="return confirm('Hapus unit ini? Tindakan ini tidak bisa dibatalkan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center rounded-lg border border-red-200 text-status-terlambat px-3 py-1.5 font-semibold hover:bg-red-50">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>



