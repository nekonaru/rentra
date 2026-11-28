<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-rentra-ink leading-tight">Dashboard</h2>
            <a href="{{ url('/' . $rental->slug) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl border border-rentra/30 text-rentra px-4 py-2 text-xs font-semibold hover:bg-teal-50 transition">
                {{ parse_url(config('app.url'), PHP_URL_HOST) }}/{{ $rental->slug }}
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M17 7H9M17 7v8"/></svg>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if ($pengumuman)
                <div class="kartu p-4 border-l-4 border-rentra">
                    <div class="font-semibold text-rentra-ink">{{ $pengumuman->judul }}</div>
                    <p class="text-sm text-[var(--warna-teks-redup)] mt-1">{{ $pengumuman->isi }}</p>
                </div>
            @endif
            <!-- Statistik -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="bg-white rounded-xl border border-teal-50 shadow-sm p-4">
                    <div class="text-xs text-gray-500">Unit aktif</div>
                    <div class="mt-1 text-2xl font-bold text-rentra-ink">{{ $statistik['unitAktif'] }}<span class="text-sm font-medium text-gray-400">/{{ $statistik['unitTotal'] }}</span></div>
                </div>
                <div class="bg-white rounded-xl border border-teal-50 shadow-sm p-4">
                    <div class="text-xs text-gray-500">Ditahan</div>
                    <div class="mt-1 text-2xl font-bold text-status-ditahan">{{ $statistik['ditahan'] }}</div>
                </div>
                <a href="{{ route('verifikasi.index') }}" class="bg-white rounded-xl border border-teal-50 shadow-sm p-4 hover:shadow-md transition">
                    <div class="text-xs text-gray-500">Menunggu verifikasi</div>
                    <div class="mt-1 text-2xl font-bold text-status-verifikasi">{{ $statistik['menungguVerifikasi'] }}</div>
                </a>
                <div class="bg-white rounded-xl border border-teal-50 shadow-sm p-4">
                    <div class="text-xs text-gray-500">Omzet bulan ini</div>
                    <div class="mt-1 text-xl font-bold text-rentra-ink">Rp {{ number_format($statistik['omzetBulanIni'], 0, ',', '.') }}</div>
                </div>
                <div class="bg-white rounded-xl border border-teal-50 shadow-sm p-4">
                    <div class="text-xs text-gray-500">Pemesanan bulan ini</div>
                    <div class="mt-1 text-2xl font-bold text-rentra-ink">{{ $statistik['jumlahBulanIni'] }}</div>
                </div>
                <div class="bg-white rounded-xl border border-teal-50 shadow-sm p-4">
                    <div class="text-xs text-gray-500">Terlambat</div>
                    <div class="mt-1 text-2xl font-bold text-status-terlambat">{{ $statistik['terlambat'] }}</div>
                </div>
            </div>

            <!-- Agenda hari ini -->
            <div class="bg-white rounded-xl border border-teal-50 shadow-sm p-6">
                <h3 class="font-semibold text-rentra-ink">Agenda hari ini ({{ $agenda->count() }})</h3>
                @if ($agenda->isEmpty())
                    <p class="mt-2 text-sm text-gray-500">Tidak ada unit keluar atau kembali hari ini.</p>
                @else
                    <ul class="mt-3 divide-y divide-gray-100">
                        @foreach ($agenda as $item)
                            <li class="py-2 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 text-sm">
                                <div>
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold uppercase text-white" style="background-color: {{ $item->status->warna() }}">{{ $item->status->label() }}</span>
                                    <span class="ms-2 font-semibold text-rentra-ink">{{ $item->unit->nama }}</span>
                                    <span class="block sm:inline text-gray-500 sm:ms-2">{{ $item->nama_penyewa }}</span>
                                </div>
                                <span class="text-xs text-gray-500">{{ $item->mulai->translatedFormat('d M H:i') }} - {{ $item->selesai->translatedFormat('d M H:i') }} WIB</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            @if ($terlambat->isNotEmpty())
                <!-- Terlambat -->
                <div class="bg-white rounded-xl border border-red-100 shadow-sm p-6">
                    <h3 class="font-semibold text-status-terlambat">Terlambat dikembalikan ({{ $terlambat->count() }})</h3>
                    <ul class="mt-3 divide-y divide-gray-100">
                        @foreach ($terlambat as $pemesanan)
                            <li class="py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                <div class="text-sm">
                                    <span class="font-semibold text-rentra-ink">{{ $pemesanan->unit->nama }}</span>
                                    <span class="text-gray-500"> - {{ $pemesanan->nama_penyewa }}</span>
                                    <span class="block text-xs text-status-terlambat">Seharusnya kembali {{ $pemesanan->selesai->translatedFormat('d M Y H:i') }} WIB</span>
                                </div>
                                <a target="_blank" rel="noopener" href="https://wa.me/{{ $pemesanan->wa }}?text={{ rawurlencode("Halo {$pemesanan->nama_penyewa}, unit {$pemesanan->unit->nama} seharusnya sudah dikembalikan pada {$pemesanan->selesai->translatedFormat('d M Y H:i')} WIB. Mohon segera dikembalikan ya. Terima kasih.") }}" class="inline-flex items-center gap-2 rounded-xl bg-status-tersedia text-white px-4 py-2 text-xs font-semibold hover:bg-green-700 transition">
                                    Kirim pengingat WA
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Mulai cepat kalau belum ada apa-apa -->
            @if ($statistik['unitTotal'] === 0)
                <div class="bg-white rounded-xl border border-teal-50 shadow-sm p-6">
                    <h3 class="font-semibold text-rentra-ink">Mulai di sini</h3>
                    <p class="mt-1 text-sm text-gray-600">Tambahkan unit pertama kamu, lalu bagikan link publik ke pelanggan.</p>
                    <a href="{{ route('unit.create') }}" class="mt-3 inline-flex items-center rounded-xl bg-rentra-accent px-5 py-2 text-sm font-semibold text-rentra-ink hover:bg-amber-400 transition">Tambah unit</a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>







