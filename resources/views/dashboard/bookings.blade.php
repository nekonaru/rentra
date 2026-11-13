<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-rentra-ink leading-tight">Booking</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-xl bg-teal-50 border border-teal-100 text-rentra text-sm font-medium px-4 py-3">{{ session('status') }}</div>
            @endif

            <section class="bg-white rounded-xl border border-teal-50 shadow-sm p-6">
                <h3 class="font-semibold text-rentra-ink">Sedang berjalan ({{ $aktif->count() }})</h3>
                @if ($aktif->isEmpty())
                    <p class="mt-2 text-sm text-gray-500">Belum ada booking terkonfirmasi atau berjalan.</p>
                @else
                    <ul class="mt-4 divide-y divide-gray-100">
                        @foreach ($aktif as $booking)
                            <li class="py-4 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                                <div class="text-sm">
                                    <span class="inline-flex items-center gap-2 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase text-white align-middle" style="background-color: {{ $booking->status->color() }}">{{ $booking->status->label() }}</span>
                                    <p class="mt-1 font-semibold text-rentra-ink">{{ $booking->unit->nama }}</p>
                                    <p class="text-gray-600">{{ $booking->nama_penyewa }} &middot; <a href="https://wa.me/{{ $booking->wa }}" target="_blank" rel="noopener" class="text-rentra hover:underline">{{ $booking->wa }}</a></p>
                                    <p class="text-xs text-gray-500">{{ $booking->mulai->translatedFormat('d M Y H:i') }} - {{ $booking->selesai->translatedFormat('d M Y H:i') }} WIB</p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @if ($booking->status === \App\Enums\BookingStatus::Terkonfirmasi)
                                        <form method="POST" action="{{ route('bookings.serah-terima', $booking) }}">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center rounded-xl bg-rentra px-4 py-2 text-xs font-bold uppercase tracking-widest text-white hover:bg-teal-800 transition">Serah terima</button>
                                        </form>
                                    @endif
                                    @if (in_array($booking->status, [\App\Enums\BookingStatus::SedangDisewa, \App\Enums\BookingStatus::Terlambat], true))
                                        <form method="POST" action="{{ route('bookings.selesai', $booking) }}">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center rounded-xl bg-status-tersedia px-4 py-2 text-xs font-bold uppercase tracking-widest text-white hover:bg-green-700 transition">Unit kembali</button>
                                        </form>
                                    @endif
                                    @if ($booking->status === \App\Enums\BookingStatus::Terkonfirmasi)
                                        <form method="POST" action="{{ route('bookings.batal', $booking) }}" onsubmit="return confirm('Batalkan booking ini?')">
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
                        @foreach ($riwayat as $booking)
                            <li class="py-3 flex items-center justify-between gap-3 text-sm">
                                <div>
                                    <span class="font-semibold text-rentra-ink">{{ $booking->unit->nama }}</span>
                                    <span class="text-gray-500"> &middot; {{ $booking->nama_penyewa }}</span>
                                    <span class="block text-xs text-gray-400">{{ $booking->mulai->translatedFormat('d M Y') }} &middot; {{ $booking->kode }}</span>
                                </div>
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold uppercase text-white" style="background-color: {{ $booking->status->color() }}">{{ $booking->status->label() }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
