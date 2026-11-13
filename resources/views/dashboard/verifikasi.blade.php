<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-rentra-ink leading-tight">Verifikasi DP</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-xl bg-teal-50 border border-teal-100 text-rentra text-sm font-medium px-4 py-3">{{ session('status') }}</div>
            @endif

            <section class="bg-white rounded-xl border border-teal-50 shadow-sm p-6">
                <h3 class="font-semibold text-rentra-ink">Menunggu verifikasi ({{ $menunggu->count() }})</h3>
                @if ($menunggu->isEmpty())
                    <p class="mt-2 text-sm text-gray-500">Belum ada bukti DP masuk. Bagikan link publik kamu supaya penyewa bisa memesan.</p>
                @else
                    <ul class="mt-4 divide-y divide-gray-100">
                        @foreach ($menunggu as $pemesanan)
                            <li class="py-4 grid gap-3 lg:grid-cols-12 lg:items-center">
                                <div class="lg:col-span-4">
                                    <p class="font-semibold text-rentra-ink">{{ $pemesanan->unit->nama }}</p>
                                    <p class="text-sm text-gray-600">{{ $pemesanan->nama_penyewa }} &middot; {{ $pemesanan->wa }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $pemesanan->mulai->translatedFormat('d M Y H:i') }} - {{ $pemesanan->selesai->translatedFormat('d M Y H:i') }} WIB</p>
                                    <p class="text-xs font-semibold text-rentra-ink mt-1">DP Rp {{ number_format((float) $pemesanan->dp_nominal, 0, ',', '.') }} &middot; Total Rp {{ number_format((float) $pemesanan->total, 0, ',', '.') }}</p>
                                </div>
                                <div class="lg:col-span-3">
                                    @if ($pemesanan->jalur_bukti && in_array(strtolower(pathinfo($pemesanan->jalur_bukti, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png']))
                                        <a href="{{ route('pemesanan.bukti', $pemesanan) }}" target="_blank" class="block">
                                            <img src="{{ route('pemesanan.bukti', $pemesanan) }}?sebaris=1" alt="Bukti DP {{ $pemesanan->kode }}" class="h-28 w-full object-contain rounded-lg border border-gray-100 bg-gray-50">
                                        </a>
                                    @else
                                        <a href="{{ route('pemesanan.bukti', $pemesanan) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-blue-50 text-status-verifikasi px-4 py-3 text-xs font-semibold hover:bg-blue-100">
                                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                                            Buka bukti (PDF)
                                        </a>
                                    @endif
                                </div>
                                <div class="lg:col-span-5 flex flex-col sm:flex-row gap-2">
                                    <form method="POST" action="{{ route('verifikasi.terima', $pemesanan) }}">
                                        @csrf
                                        <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 rounded-xl bg-rentra px-5 py-2.5 text-xs font-bold uppercase tracking-widest text-white hover:bg-teal-800 transition">Terima</button>
                                    </form>
                                    <form method="POST" action="{{ route('verifikasi.tolak', $pemesanan) }}" class="flex-1 flex gap-2">
                                        @csrf
                                        <input type="text" name="catatan_tolak" required maxlength="500" placeholder="Alasan tolak (mis. nominal tidak sesuai)" class="min-w-0 flex-1 rounded-xl border-gray-200 focus:border-status-terlambat focus:ring-status-terlambat text-xs">
                                        <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-red-200 text-status-terlambat px-4 py-2.5 text-xs font-bold uppercase tracking-widest hover:bg-red-50 transition">Tolak</button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="bg-white rounded-xl border border-teal-50 shadow-sm p-6">
                <h3 class="font-semibold text-rentra-ink">Ditahan, menunggu DP ({{ $ditahan->count() }})</h3>
                @if ($ditahan->isEmpty())
                    <p class="mt-2 text-sm text-gray-500">Tidak ada unit yang sedang ditahan.</p>
                @else
                    <ul class="mt-4 divide-y divide-gray-100">
                        @foreach ($ditahan as $pemesanan)
                            <li class="py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-sm">
                                <div>
                                    <span class="font-semibold text-rentra-ink">{{ $pemesanan->unit->nama }}</span>
                                    <span class="text-gray-500"> &middot; {{ $pemesanan->nama_penyewa }} &middot; {{ $pemesanan->wa }}</span>
                                    <span class="block text-xs text-gray-500">{{ $pemesanan->mulai->translatedFormat('d M Y H:i') }} - {{ $pemesanan->selesai->translatedFormat('d M Y H:i') }} WIB</span>
                                </div>
                                <span class="text-xs font-semibold text-status-ditahan">Tahan habis {{ $pemesanan->tahan_sampai->diffForHumans() }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>



