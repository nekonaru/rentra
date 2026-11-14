<x-layouts.admin>
    <h1 class="text-xl font-bold text-white">Dashboard Admin</h1>
    <p class="mt-1 text-sm text-white/60">Ringkasan seluruh tenant Rentra.</p>

    <div class="mt-6 grid grid-cols-2 lg:grid-cols-4 gap-3">
        @php
            $kartu = [
                ['label' => 'Pemilik terdaftar', 'nilai' => $statistik['pemilik']],
                ['label' => 'Rental aktif', 'nilai' => $statistik['rentalAktif']],
                ['label' => 'Rental ditangguhkan', 'nilai' => $statistik['rentalDitangguhkan']],
                ['label' => 'Unit', 'nilai' => $statistik['unit']],
                ['label' => 'Pemesanan', 'nilai' => $statistik['pemesanan']],
                ['label' => 'Pemesanan hari ini', 'nilai' => $statistik['pemesananHariIni']],
                ['label' => 'Menunggu verifikasi', 'nilai' => $statistik['menungguVerifikasi']],
                ['label' => 'Omzet diakui', 'nilai' => 'Rp '.number_format($statistik['omzetTotal'], 0, ',', '.')],
            ];
        @endphp
        @foreach ($kartu as $item)
            <div class="rounded-xl bg-white/5 border border-white/10 p-4">
                <div class="text-[11px] uppercase tracking-wide text-white/50">{{ $item['label'] }}</div>
                <div class="mt-1 text-2xl font-bold text-white">{{ $item['nilai'] }}</div>
            </div>
        @endforeach
    </div>

    <section class="mt-6 rounded-xl bg-white/5 border border-white/10 p-5">
        <h2 class="font-semibold text-white">Tenant terbaru</h2>
        @if ($terbaru->isEmpty())
            <p class="mt-2 text-sm text-white/50">Belum ada tenant.</p>
        @else
            <ul class="mt-3 divide-y divide-white/10">
                @foreach ($terbaru as $rental)
                    <li class="py-3 flex items-center justify-between gap-3 text-sm">
                        <div class="min-w-0">
                            <a href="{{ route('admin.tenant.show', $rental) }}" class="font-semibold text-teal-300 hover:underline truncate block">{{ $rental->nama }}</a>
                            <span class="text-white/50">{{ $rental->user->email }} &middot; {{ $rental->unit_count }} unit &middot; {{ $rental->pemesanan_count }} pemesanan</span>
                        </div>
                        <span class="shrink-0 inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold uppercase {{ $rental->aktif ? 'bg-green-500/20 text-green-300' : 'bg-red-500/20 text-red-300' }}">{{ $rental->aktif ? 'Aktif' : 'Ditangguhkan' }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-layouts.admin>
