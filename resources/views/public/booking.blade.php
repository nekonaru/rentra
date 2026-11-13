<x-layouts.public :rental="$booking->unit->rental" :title="'Booking ' . $booking->kode">
    @php
        $status = $booking->status;
    @endphp

    @if (session('status'))
        <div class="mb-4 rounded-xl bg-teal-50 border border-teal-100 text-rentra text-sm font-medium px-4 py-3">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-4 rounded-xl bg-red-50 border border-red-100 px-4 py-3 text-sm font-medium text-status-terlambat">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl border border-teal-50 shadow-sm p-6">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 pb-4">
            <div>
                <p class="text-[11px] uppercase tracking-wide text-gray-400">Kode booking</p>
                <p class="text-lg font-bold tracking-widest text-rentra-ink">{{ $booking->kode }}</p>
            </div>
            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-bold text-white" style="background-color: {{ $status->color() }}">
                <span class="h-2 w-2 rounded-full bg-white/80"></span>
                {{ $status->label() }}
            </span>
        </div>

        <dl class="mt-4 grid sm:grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-gray-500">Penyewa</dt>
                <dd class="font-semibold text-rentra-ink">{{ $booking->nama_penyewa }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Unit</dt>
                <dd class="font-semibold text-rentra-ink">{{ $booking->unit->nama }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Mulai</dt>
                <dd class="font-semibold text-rentra-ink">{{ $booking->mulai->translatedFormat('d F Y, H:i') }} WIB</dd>
            </div>
            <div>
                <dt class="text-gray-500">Selesai</dt>
                <dd class="font-semibold text-rentra-ink">{{ $booking->selesai->translatedFormat('d F Y, H:i') }} WIB</dd>
            </div>
            <div>
                <dt class="text-gray-500">Total sewa</dt>
                <dd class="font-semibold text-rentra-ink">Rp {{ number_format((float) $booking->total, 0, ',', '.') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">DP ({{ $booking->rental->persen_dp }}%)</dt>
                <dd class="font-semibold text-rentra-ink">Rp {{ number_format((float) $booking->dp_nominal, 0, ',', '.') }}</dd>
            </div>
        </dl>

        @if ($status === \App\Enums\BookingStatus::Ditahan && $booking->hold_expires_at)
            <div x-data="{
                target: @js($booking->hold_expires_at->toIso8601String()),
                sisa: '',
                init() { this.tick(); setInterval(() => this.tick(), 1000); },
                tick() {
                    const diff = Math.max(0, Math.floor((new Date(this.target) - Date.now()) / 1000));
                    const m = Math.floor(diff / 60), s = diff % 60;
                    this.sisa = diff <= 0 ? 'Waktu tahan habis' : String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
                },
            }" class="mt-5 rounded-xl bg-amber-50 border border-amber-200 p-4">
                <p class="text-xs font-semibold text-amber-800 uppercase tracking-wide">Unit ditahan, sisa waktu</p>
                <p class="mt-1 text-3xl font-bold tabular-nums text-amber-700" x-text="sisa">--:--</p>
                @if ($booking->rental->info_rekening)
                    <p class="mt-3 text-xs font-semibold text-gray-600">Transfer DP ke:</p>
                    <p class="mt-0.5 text-sm text-rentra-ink whitespace-pre-line">{{ $booking->rental->info_rekening }}</p>
                @endif
                @if ($booking->catatan_tolak)
                    <p class="mt-3 rounded-lg bg-white border border-amber-200 px-3 py-2 text-xs text-amber-800"><span class="font-bold">Bukti sebelumnya ditolak:</span> {{ $booking->catatan_tolak }}</p>
                @endif

                <form method="POST" action="{{ route('public.booking.bukti', $booking->kode) }}" enctype="multipart/form-data" class="mt-4">
                    @csrf
                    <label class="block text-xs font-semibold text-gray-700">
                        Upload bukti DP (jpg, png, atau pdf, maks 5 MB)
                        <input type="file" name="bukti" accept="image/jpeg,image/png,application/pdf" required class="mt-1 block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-amber-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-amber-800 hover:file:bg-amber-200">
                    </label>
                    <button type="submit" class="mt-3 w-full inline-flex items-center justify-center rounded-xl bg-rentra-accent px-5 py-3 text-sm font-bold text-rentra-ink hover:bg-amber-400 transition">Kirim bukti DP</button>
                </form>
            </div>
        @elseif ($status === \App\Enums\BookingStatus::MenungguVerifikasi)
            <div class="mt-5 rounded-xl bg-blue-50 border border-blue-200 p-4 text-sm text-blue-900">
                <p class="font-semibold">Bukti DP sudah terkirim.</p>
                <p class="mt-1 text-blue-800">Pemilik akan memeriksa dan mengonfirmasi booking kamu. Simpan halaman ini untuk melihat status terbaru.</p>
            </div>
        @elseif ($status === \App\Enums\BookingStatus::Terkonfirmasi)
            <div class="mt-5 rounded-xl bg-teal-50 border border-teal-200 p-4 text-sm text-teal-900">
                <p class="font-semibold">Booking terkonfirmasi.</p>
                <p class="mt-1 text-teal-800">Sampai jumpa pada {{ $booking->mulai->translatedFormat('d F Y, H:i') }} WIB. Bawa KTP atau identitas asli saat ambil unit.</p>
            </div>
        @elseif ($status === \App\Enums\BookingStatus::SedangDisewa)
            <div class="mt-5 rounded-xl bg-teal-50 border border-teal-200 p-4 text-sm text-teal-900">
                <p class="font-semibold">Unit sedang kamu sewa.</p>
                <p class="mt-1 text-teal-800">Jangan lupa dikembalikan paling lambat {{ $booking->selesai->translatedFormat('d F Y, H:i') }} WIB.</p>
            </div>
        @elseif ($status === \App\Enums\BookingStatus::Terlambat)
            <div class="mt-5 rounded-xl bg-red-50 border border-red-200 p-4 text-sm text-red-900">
                <p class="font-semibold">Unit melewati batas pengembalian.</p>
                <p class="mt-1 text-red-800">Segera hubungi pemilik lewat WhatsApp dan kembalikan unit.</p>
            </div>
        @elseif ($status === \App\Enums\BookingStatus::Selesai)
            <div class="mt-5 rounded-xl bg-green-50 border border-green-200 p-4 text-sm text-green-900">
                <p class="font-semibold">Booking selesai. Terima kasih!</p>
            </div>
        @elseif ($status === \App\Enums\BookingStatus::Kedaluwarsa)
            <div class="mt-5 rounded-xl bg-gray-50 border border-gray-200 p-4 text-sm text-gray-800">
                <p class="font-semibold">Waktu tahan habis.</p>
                <p class="mt-1">Unit dilepas kembali. <a href="{{ route('public.show', $booking->unit->rental->slug) }}" class="font-semibold text-rentra hover:underline">Booking lagi</a> jika masih berminat.</p>
            </div>
        @elseif ($status === \App\Enums\BookingStatus::Dibatalkan)
            <div class="mt-5 rounded-xl bg-gray-50 border border-gray-200 p-4 text-sm text-gray-800">
                <p class="font-semibold">Booking dibatalkan.</p>
                @if ($booking->catatan_tolak)
                    <p class="mt-1">{{ $booking->catatan_tolak }}</p>
                @endif
            </div>
        @endif
    </div>
</x-layouts.public>
