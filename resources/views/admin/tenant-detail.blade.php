<x-layouts.admin>
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-xl font-bold text-white">{{ $rental->nama }}</h1>
            <p class="mt-1 text-sm text-white/50">/{{ $rental->slug }} &middot; dibuat {{ $rental->created_at->translatedFormat('d F Y') }}</p>
        </div>
        <span class="inline-flex items-center rounded-full px-3 py-1 text-[11px] font-bold uppercase {{ $rental->aktif ? 'bg-green-500/20 text-green-300' : 'bg-red-500/20 text-red-300' }}">{{ $rental->aktif ? 'Aktif' : 'Ditangguhkan' }}</span>
    </div>

    <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        @php
            $kartu = [
                ['label' => 'Pemilik', 'nilai' => $rental->user->name],
                ['label' => 'Email pemilik', 'nilai' => $rental->user->email],
                ['label' => 'Unit', 'nilai' => $rental->unit_count . ' unit (' . $ringkasan['unitAktif'] . ' aktif)'],
                ['label' => 'Pemesanan', 'nilai' => $rental->pemesanan_count],
                ['label' => 'Omzet diakui', 'nilai' => 'Rp ' . number_format($ringkasan['omzet'], 0, ',', '.')],
                ['label' => 'DP terkumpul', 'nilai' => 'Rp ' . number_format($ringkasan['dpTerkumpul'], 0, ',', '.')],
                ['label' => 'Nomor WA', 'nilai' => $rental->no_wa],
                ['label' => 'Persen DP', 'nilai' => $rental->persen_dp . '%'],
            ];
        @endphp
        @foreach ($kartu as $item)
            <div class="rounded-xl bg-white/5 border border-white/10 p-4">
                <div class="text-[11px] uppercase tracking-wide text-white/50">{{ $item['label'] }}</div>
                <div class="mt-1 text-sm font-semibold text-white break-all">{{ $item['nilai'] }}</div>
            </div>
        @endforeach
    </div>

    @if ($ringkasan['perStatus']->isNotEmpty())
        <section class="mt-6 rounded-xl bg-white/5 border border-white/10 p-5">
            <h2 class="font-semibold text-white">Pemesanan per status</h2>
            <ul class="mt-3 grid grid-cols-2 sm:grid-cols-3 gap-2 text-sm">
                @foreach ($ringkasan['perStatus'] as $status => $jumlah)
                    @php
                        $enum = \App\Enums\StatusPemesanan::tryFrom((string) $status);
                    @endphp
                    <li class="rounded-lg bg-white/5 px-3 py-2 flex items-center justify-between gap-2">
                        <span class="text-white/70">{{ $enum?->label() ?? $status }}</span>
                        <span class="font-bold text-white">{{ $jumlah }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <div class="mt-6 flex flex-wrap gap-3">
        <a href="{{ route('publik.tampil', $rental->slug) }}" target="_blank" class="inline-flex items-center rounded-xl border border-teal-400/40 text-teal-300 px-5 py-3 text-sm font-semibold hover:bg-teal-400/10 transition">Lihat laman publik</a>
        @if ($rental->aktif)
            <form method="POST" action="{{ route('admin.tenant.tangguhkan', $rental) }}" onsubmit="return confirm('Tangguhkan tenant ini? Laman publik tidak akan tampil.')">
                @csrf
                <button type="submit" class="inline-flex items-center rounded-xl bg-red-500/80 px-5 py-3 text-sm font-bold text-white hover:bg-red-500 transition">Tangguhkan</button>
            </form>
        @else
            <form method="POST" action="{{ route('admin.tenant.aktifkan', $rental) }}">
                @csrf
                <button type="submit" class="inline-flex items-center rounded-xl bg-green-500/80 px-5 py-3 text-sm font-bold text-white hover:bg-green-500 transition">Aktifkan kembali</button>
            </form>
        @endif
    </div>
</x-layouts.admin>
