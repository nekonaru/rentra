<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-rentra-ink leading-tight">Kalender Rental</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid gap-6 lg:grid-cols-3">
            <section class="lg:col-span-2 bg-white rounded-xl border border-teal-50 shadow-sm p-5">
                <div class="flex items-center justify-between gap-2">
                    <a href="{{ route('kalender.index', ['bulan' => $bulan->copy()->subMonth()->format('Y-m'), 'hari' => $hari->format('Y-m-d')]) }}" class="p-2 rounded-lg hover:bg-gray-50 text-gray-500" aria-label="Bulan sebelumnya">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                    </a>
                    <h3 class="font-semibold text-rentra-ink">{{ $bulan->translatedFormat('F Y') }}</h3>
                    <a href="{{ route('kalender.index', ['bulan' => $bulan->copy()->addMonth()->format('Y-m'), 'hari' => $hari->format('Y-m-d')]) }}" class="p-2 rounded-lg hover:bg-gray-50 text-gray-500" aria-label="Bulan berikutnya">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg>
                    </a>
                </div>

                @php
                    $weekdays = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
                    $gridStart = $bulan->copy()->startOfMonth()->startOfWeek(\Carbon\Carbon::MONDAY);
                    $gridEnd = $bulan->copy()->endOfMonth()->endOfWeek(\Carbon\Carbon::SUNDAY);
                    $today = now()->toDateString();
                    $hariTerpilih = $hari->toDateString();
                @endphp
                <div class="mt-4 grid grid-cols-7 gap-1 text-center text-[10px] font-semibold uppercase text-gray-400">
                    @foreach ($weekdays as $day)
                        <div class="py-1">{{ $day }}</div>
                    @endforeach
                </div>
                <div class="grid grid-cols-7 gap-1">
                    @for ($date = $gridStart->copy(); $date->lte($gridEnd); $date->addDay())
                        @php
                            $key = $date->toDateString();
                            $daftar = $perHari[$key] ?? [];
                            $inMonth = $date->month === $bulan->month;
                            $isSelected = $key === $hariTerpilih;
                            $isToday = $key === $today;
                            $cellClasses = $isSelected ? 'ring-2 ring-rentra bg-teal-50' : ($isToday ? 'border border-rentra/40 bg-white' : 'hover:bg-gray-50 bg-white');
                        @endphp
                        <a href="{{ route('kalender.index', ['bulan' => $bulan->format('Y-m'), 'hari' => $key]) }}" class="rounded-lg p-1 text-left min-h-[64px] flex flex-col {{ $cellClasses }} {{ $inMonth ? '' : 'opacity-30' }} border border-gray-100">
                            <span class="text-[10px] font-semibold text-gray-500">{{ $date->day }}</span>
                            <div class="mt-0.5 flex flex-col gap-0.5">
                                @foreach (array_slice($daftar, 0, 2) as $item)
                                    <span class="block rounded px-1 py-0.5 text-[9px] font-semibold text-white truncate" style="background-color: {{ $item->status->warna() }}">{{ $item->unit->nama }}</span>
                                @endforeach
                                @if (count($daftar) > 2)
                                    <span class="text-[9px] font-semibold text-gray-500 px-1">+{{ count($daftar) - 2 }} lainnya</span>
                                @endif
                            </div>
                        </a>
                    @endfor
                </div>

                <ul class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-[11px] text-gray-500">
                    @foreach (array_slice(\App\Enums\StatusPemesanan::menahanSlot(), 0, 5) as $s)
                        <li class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full" style="background-color: {{ $s->warna() }}"></span>{{ $s->label() }}</li>
                    @endforeach
                </ul>
            </section>

            <section class="bg-white rounded-xl border border-teal-50 shadow-sm p-6">
                <h3 class="font-semibold text-rentra-ink">Jadwal {{ $hari->translatedFormat('d F Y') }}</h3>
                @if ($detail->isEmpty())
                    <p class="mt-2 text-sm text-gray-500">Tidak ada unit keluar atau kembali pada hari ini.</p>
                @else
                    <ul class="mt-4 divide-y divide-gray-100">
                        @foreach ($detail as $item)
                            <li class="py-3 text-sm">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold uppercase text-white" style="background-color: {{ $item->status->warna() }}">{{ $item->status->label() }}</span>
                                    <a href="{{ route('pemesanan.cetak', $item) }}" class="text-xs font-semibold text-rentra hover:underline">Cetak</a>
                                </div>
                                <p class="mt-1 font-semibold text-rentra-ink">{{ $item->unit->nama }}</p>
                                <p class="text-gray-600">{{ $item->nama_penyewa }} &middot; {{ $item->wa }}</p>
                                <p class="text-xs text-gray-500">{{ $item->mulai->translatedFormat('H:i') }} - {{ $item->selesai->translatedFormat('d M H:i') }} WIB &middot; Rp {{ number_format((float) $item->total, 0, ',', '.') }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
