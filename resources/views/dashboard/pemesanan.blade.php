<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-rentra-ink leading-tight">Pemesanan</h2>
            <a href="{{ route('pemesanan.ekspor') }}" class="inline-flex items-center gap-2 rounded-xl border border-rentra/30 text-rentra px-4 py-2 text-xs font-semibold hover:bg-teal-50 transition">
                Ekspor CSV
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-xl bg-teal-50 border border-teal-100 text-rentra text-sm font-medium px-4 py-3">{{ session('status') }}</div>
            @endif

            <section class="bg-white rounded-xl border border-teal-50 shadow-sm p-6">
                <h3 class="font-semibold text-rentra-ink">Sedang berjalan ({{ $berjalan->count() }})</h3>
                @if ($berjalan->isEmpty())
                    <p class="mt-2 text-sm text-gray-500">Belum ada pemesanan terkonfirmasi atau berjalan.</p>
                @else
                    <ul class="mt-4 divide-y divide-gray-100">
                        @foreach ($berjalan as $pemesanan)
                            <li class="py-4 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                                <div class="text-sm">
                                    <span class="inline-flex items-center gap-2 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase text-white align-middle" style="background-color: {{ $pemesanan->status->warna() }}">{{ $pemesanan->status->label() }}</span>
                                    <p class="mt-1 font-semibold text-rentra-ink">{{ $pemesanan->unit->nama }}</p>
                                    <p class="text-gray-600">{{ $pemesanan->nama_penyewa }} &middot; <a href="https://wa.me/{{ $pemesanan->wa }}" target="_blank" rel="noopener" class="text-rentra hover:underline">{{ $pemesanan->wa }}</a></p>
                                    <p class="text-xs text-gray-500">{{ $pemesanan->mulai->translatedFormat('d M Y H:i') }} - {{ $pemesanan->selesai->translatedFormat('d M Y H:i') }} WIB</p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @if ($pemesanan->status === \App\Enums\StatusPemesanan::Terkonfirmasi)
                                        <form method="POST" action="{{ route('pemesanan.serah-terima', $pemesanan) }}">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center rounded-xl bg-rentra px-4 py-2 text-xs font-bold uppercase tracking-widest text-white hover:bg-teal-800 transition">Serah terima</button>
                                        </form>
                                    @endif
                                    @if (in_array($pemesanan->status, [\App\Enums\StatusPemesanan::SedangDisewa, \App\Enums\StatusPemesanan::Terlambat], true))
                                        <form method="POST" action="{{ route('pemesanan.selesai', $pemesanan) }}">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center rounded-xl bg-status-tersedia px-4 py-2 text-xs font-bold uppercase tracking-widest text-white hover:bg-green-700 transition">Unit kembali</button>
                                        </form>
                                    @endif
                                    @if ($pemesanan->status === \App\Enums\StatusPemesanan::Terkonfirmasi)
                                        <form method="POST" action="{{ route('pemesanan.batal', $pemesanan) }}" onsubmit="return confirm('Batalkan pemesanan ini??')">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center rounded-xl border border-red-200 text-status-terlambat px-4 py-2 text-xs font-bold uppercase tracking-widest hover:bg-red-50 transition">Batalkan</button>
                                        </form>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="bg-white rounded-xl border border-teal-50 shadow-sm p-6">
                <h3 class="font-semibold text-rentra-ink">Riwayat terakhir ({{ $riwayat->count() }})</h3>
                @if ($riwayat->isEmpty())
                    <p class="mt-2 text-sm text-gray-500">Belum ada riwayat.</p>
                @else
                    <ul class="mt-4 divide-y divide-gray-100">
                        @foreach ($riwayat as $pemesanan)
                            <li class="py-3 flex items-center justify-between gap-3 text-sm">
                                <div>
                                    <span class="font-semibold text-rentra-ink">{{ $pemesanan->unit->nama }}</span>
                                    <span class="text-gray-500"> &middot; {{ $pemesanan->nama_penyewa }}</span>
                                    <span class="block text-xs text-gray-400">{{ $pemesanan->mulai->translatedFormat('d M Y') }} &middot; {{ $pemesanan->kode }}</span>
                                </div>
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold uppercase text-white" style="background-color: {{ $pemesanan->status->warna() }}">{{ $pemesanan->status->label() }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>




