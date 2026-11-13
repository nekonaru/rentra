<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-rentra-ink leading-tight">Laporan</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Ringkasan -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="bg-white rounded-xl border border-teal-50 shadow-sm p-4">
                    <div class="text-xs text-gray-500">Omzet bulan ini</div>
                    <div class="mt-1 text-xl font-bold text-rentra-ink">Rp {{ number_format($statistik['omzetBulanIni'], 0, ',', '.') }}</div>
                </div>
                <div class="bg-white rounded-xl border border-teal-50 shadow-sm p-4">
                    <div class="text-xs text-gray-500">Rata-rata nilai sewa</div>
                    <div class="mt-1 text-xl font-bold text-rentra-ink">Rp {{ number_format($statistik['rataRataNilai'], 0, ',', '.') }}</div>
                </div>
                <div class="bg-white rounded-xl border border-teal-50 shadow-sm p-4">
                    <div class="text-xs text-gray-500">DP terkumpul</div>
                    <div class="mt-1 text-xl font-bold text-status-verifikasi">Rp {{ number_format($statistik['dpTerkumpul'], 0, ',', '.') }}</div>
                </div>
                <div class="bg-white rounded-xl border border-teal-50 shadow-sm p-4">
                    <div class="text-xs text-gray-500">Okupasi 30 hari</div>
                    <div class="mt-1 text-2xl font-bold text-rentra-ink">{{ $statistik['okupasi'] }}<span class="text-sm font-medium text-gray-400">%</span></div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Grafik omzet 6 bulan -->
                <section class="lg:col-span-2 bg-white rounded-xl border border-teal-50 shadow-sm p-6">
                    <h3 class="font-semibold text-rentra-ink">Omzet 6 bulan terakhir</h3>
                    <p class="text-xs text-gray-500">Dihitung dari tanggal selesai sewa (status: sedang disewa, terlambat, selesai).</p>
                    <div class="mt-6 flex items-end gap-2 sm:gap-4 h-48 border-b border-gray-100 pb-2" role="img" aria-label="Diagram batang omzet 6 bulan terakhir">
                        @foreach ($bulanData as $bulan)
                            @php
                                $tinggi = $bulan['omzet'] > 0 ? max(4, (int) round(($bulan['omzet'] / $maxOmzet) * 100)) : 0;
                            @endphp
                            <div class="flex-1 flex flex-col items-center gap-2 min-w-0">
                                <div class="text-[10px] font-bold text-rentra-ink whitespace-nowrap">Rp {{ number_format($bulan['omzet'] / 1000, 0, ',', '.') }}k</div>
                                <div class="w-full bg-teal-50 rounded-t-md flex items-end justify-center overflow-hidden" style="height: 100%;">
                                    <div class="w-full bg-rentra rounded-t-md transition-all" style="height: {{ $tinggi }}%"></div>
                                </div>
                                <div class="text-[10px] text-gray-500 whitespace-nowrap">{{ $bulan['label'] }}</div>
                                <div class="text-[9px] text-gray-400">{{ $bulan['jumlah'] }}x</div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <!-- Unit terlaris -->
                <section class="bg-white rounded-xl border border-teal-50 shadow-sm p-6">
                    <h3 class="font-semibold text-rentra-ink">Unit terlaris (omzet)</h3>
                    @if ($unitTerlaris->isEmpty())
                        <p class="mt-2 text-sm text-gray-500">Belum ada pemesanan yang diakui.</p>
                    @else
                        <ol class="mt-4 space-y-3">
                            @foreach ($unitTerlaris as $peringkat => $baris)
                                <li class="flex items-center gap-3 text-sm">
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-teal-50 text-xs font-bold text-rentra">{{ $peringkat + 1 }}</span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block font-semibold text-rentra-ink truncate">{{ $baris->unit->nama }}</span>
                                        <span class="block text-xs text-gray-500">{{ $baris->jumlah }} sewa</span>
                                    </span>
                                    <span class="text-xs font-bold text-rentra-ink whitespace-nowrap">Rp {{ number_format((float) $baris->omzet, 0, ',', '.') }}</span>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
