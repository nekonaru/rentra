<x-layouts.publik :rental="$rental" :title="$rental->nama">
    <div class="text-center pt-2 pb-6">
        @if ($rental->logo_path)
            <img src="{{ Storage::disk('public')->url($rental->logo_path) }}" alt="Logo {{ $rental->nama }}" class="mx-auto h-16 w-16 rounded-xl object-cover mb-3">
        @endif
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-rentra-ink">{{ $rental->nama }}</h1>
        <p class="mt-1 text-sm text-[var(--warna-teks-redup)]">Pilih unit, lihat jadwal, dan pesan langsung.</p>
        @if ($rental->deskripsi)
            <p class="mt-2 text-sm text-[var(--warna-teks-redup)] max-w-lg mx-auto">{{ $rental->deskripsi }}</p>
        @endif
        @if ($rental->alamat)
            <div class="mt-3 flex flex-wrap items-center justify-center gap-2 text-xs text-[var(--warna-teks-redup)]">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                <span>{{ $rental->alamat }}</span>
                @if ($rental->peta_url)
                    <a href="{{ $rental->peta_url }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-rentra hover:underline">Buka peta</a>
                @endif
            </div>
        @endif
    </div>

    @if ($rental->unit->isEmpty())
        <x-kosong judul="Belum ada unit tersedia" pesan="Hubungi pemilik lewat WhatsApp untuk info lebih lanjut." />
    @else
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach ($rental->unit as $unit)
                <div class="group kartu overflow-hidden hover:shadow-angkat transition relative">
                    <a href="{{ route('publik.unit', [$rental->slug, $unit->id]) }}" class="block">
                        @if ($unit->jalur_foto)
                            <img src="{{ Storage::disk('public')->url($unit->jalur_foto) }}" alt="Foto {{ $unit->nama }}" class="h-44 w-full object-cover" loading="lazy">
                        @else
                            <div class="h-44 w-full bg-rentra-50 flex items-center justify-center text-rentra/40">
                                <svg viewBox="0 0 24 24" class="h-14 w-14" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h3l2-2h6l2 2h3a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1Z"/><circle cx="12" cy="13" r="4"/></svg>
                            </div>
                        @endif
                    </a>
                    <div class="absolute top-2 right-2 flex gap-1">
                        <button type="button" data-favorit="{{ $unit->id }}" onclick="toogleFavorit({{ $unit->id }})" class="rounded-full bg-white/90 p-2 text-gray-500 hover:text-amber-500 transition" aria-label="Simpan favorit" aria-pressed="false">
                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 2 3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2Z"/></svg>
                        </button>
                        <button type="button" onclick="toogleBanding({{ $unit->id }}, @js($unit->nama))" class="rounded-full bg-white/90 p-2 text-gray-500 hover:text-rentra transition" aria-label="Bandingkan unit">
                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                        </button>
                    </div>
                    <div class="p-4">
                        <h2 class="font-semibold text-rentra-ink leading-snug group-hover:text-rentra transition">{{ $unit->nama }}</h2>
                        <p class="text-xs text-[var(--warna-teks-redup)] mt-0.5">{{ collect([$unit->kode_internal, $unit->merek, $unit->kategori])->filter()->join(' · ') }}</p>
                        <p class="mt-2 text-sm font-bold text-rentra-ink">
                            Rp {{ number_format((float) ($unit->aturanTarif->tarif_per_hari ?? 0), 0, ',', '.') }}<span class="font-normal text-[var(--warna-teks-redup)]">/hari</span>
                        </p>
                        <p class="mt-2 text-xs font-semibold text-rentra">Cek jadwal & pesan &rarr;</p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Panel bandingkan --}}
    <div id="panel-banding" x-data="{ buka: false }" class="mt-6" x-show="window.banding && window.banding.length > 0" x-cloak>
        <button type="button" @click="buka = !buka" class="w-full kartu p-4 flex items-center justify-between text-sm font-semibold text-rentra-ink">
            <span>Bandingkan unit (<span x-text="window.banding ? window.banding.length : 0"></span>)</span>
            <span x-text="buka ? 'Tutup' : 'Lihat'"></span>
        </button>
        <div x-show="buka" class="mt-2 kartu p-4">
            <template x-for="item in window.banding" :key="item.id">
                <div class="flex items-center justify-between py-2 border-b border-[var(--warna-tepi)] last:border-0">
                    <span x-text="item.nama" class="text-sm font-medium"></span>
                    <button type="button" @click="toogleBanding(item.id, item.nama)" class="text-xs text-status-terlambat">Hapus</button>
                </div>
            </template>
            <p class="mt-2 text-xs text-[var(--warna-teks-redup)]">Buka masing-masing unit untuk membandingkan jadwal dan harga.</p>
        </div>
    </div>
</x-layouts.publik>
