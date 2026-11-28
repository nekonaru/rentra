<x-layouts.publik :rental="$pemesanan->unit->rental" :title="'Pemesanan ' . $pemesanan->kode">
    @php
        $status = $pemesanan->status;
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

    <div class="kartu p-6">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[var(--warna-tepi)] pb-4">
            <div>
                <p class="text-[11px] uppercase tracking-wide text-[var(--warna-teks-redup)]">Kode Pemesanan</p>
                <p class="text-lg font-bold tracking-widest text-rentra-ink">{{ $pemesanan->kode }}</p>
            </div>
            <span class="lencana text-white" style="background-color: {{ $status->warna() }}">
                {{ $status->label() }}
            </span>
        </div>

        <!-- Garis waktu status -->
        @php
            $urutan = [
                ['Ditahan', 'ditahan'],
                ['Menunggu verifikasi', 'menunggu_verifikasi'],
                ['Terkonfirmasi', 'terkonfirmasi'],
                ['Sedang disewa', 'sedang_disewa'],
                ['Selesai', 'selesai'],
            ];
            $statusSekarang = $status->value;
            $indeks = collect($urutan)->search(fn ($s) => $s[1] === $statusSekarang);
            if ($statusSekarang === 'terlambat') $indeks = 3;
            if ($statusSekarang === 'kedaluwarsa' || $statusSekarang === 'dibatalkan') $indeks = 0;
        @endphp
        <div class="mt-4" aria-label="Progres pemesanan">
            <x-langkah :langkah="array_column($urutan, 0)" :aktif="max(1, $indeks + 1)" />
        </div>

        <dl class="mt-4 grid sm:grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-gray-500">Penyewa</dt>
                <dd class="font-semibold text-rentra-ink">{{ $pemesanan->nama_penyewa }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Unit</dt>
                <dd class="font-semibold text-rentra-ink">{{ $pemesanan->unit->nama }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Mulai</dt>
                <dd class="font-semibold text-rentra-ink">{{ $pemesanan->mulai->translatedFormat('d F Y, H:i') }} WIB</dd>
            </div>
            <div>
                <dt class="text-gray-500">Selesai</dt>
                <dd class="font-semibold text-rentra-ink">{{ $pemesanan->selesai->translatedFormat('d F Y, H:i') }} WIB</dd>
            </div>
            <div>
                <dt class="text-gray-500">Total sewa</dt>
                <dd class="font-semibold text-rentra-ink">Rp {{ number_format((float) $pemesanan->total, 0, ',', '.') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">DP ({{ $pemesanan->rental->persen_dp }}%)</dt>
                <dd class="font-semibold text-rentra-ink">Rp {{ number_format((float) $pemesanan->dp_nominal, 0, ',', '.') }}</dd>
            </div>
        </dl>

        @if ($status === \App\Enums\StatusPemesanan::Ditahan && $pemesanan->tahan_sampai)
            <div x-data="{
                target: @js($pemesanan->tahan_sampai->toIso8601String()),
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
                @if ($pemesanan->rental->info_rekening)
                    <p class="mt-3 text-xs font-semibold text-gray-600">Transfer DP ke:</p>
                    <p class="mt-0.5 text-sm text-rentra-ink whitespace-pre-line">{{ $pemesanan->rental->info_rekening }}</p>
                @endif
                @if ($pemesanan->catatan_tolak)
                    <p class="mt-3 rounded-lg bg-white border border-amber-200 px-3 py-2 text-xs text-amber-800"><span class="font-bold">Bukti sebelumnya ditolak:</span> {{ $pemesanan->catatan_tolak }}</p>
                @endif

                <form method="POST" action="{{ route('publik.pemesanan.bukti', $pemesanan->kode) }}" enctype="multipart/form-data" class="mt-4">
                    @csrf
                    <label class="block text-xs font-semibold text-gray-700">
                        Upload bukti DP (jpg, png, atau pdf, maks 5 MB)
                        <input type="file" name="bukti" accept="image/jpeg,image/png,application/pdf" required class="mt-1 block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-amber-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-amber-800 hover:file:bg-amber-200">
                    </label>
                    <div><label class="block text-xs font-semibold">Nominal DP dibayar (opsional)</label><input type="number" name="nominal" min="0" step="1000" class="input mt-1" placeholder="Jumlah transfer"></div><button type="submit" class="mt-3 w-full tombol tombol-aksen">Kirim bukti DP</button>
                </form>
            </div>
        @elseif ($status === \App\Enums\StatusPemesanan::MenungguVerifikasi)
            <div class="mt-5 rounded-xl bg-blue-50 border border-blue-200 p-4 text-sm text-blue-900">
                <p class="font-semibold">Bukti DP sudah terkirim.</p>
                <p class="mt-1 text-blue-800">Pemilik akan memeriksa dan mengonfirmasi Pemesanan kamu. Simpan halaman ini untuk melihat status terbaru.</p>
            </div>
        @elseif ($status === \App\Enums\StatusPemesanan::Terkonfirmasi)
            <div class="mt-5 rounded-xl bg-teal-50 border border-teal-200 p-4 text-sm text-teal-900">
                <p class="font-semibold">Pemesanan terkonfirmasi.</p>
                <p class="mt-1 text-teal-800">Sampai jumpa pada {{ $pemesanan->mulai->translatedFormat('d F Y, H:i') }} WIB. Bawa KTP atau identitas asli saat ambil unit.</p>
            </div>
        @elseif ($status === \App\Enums\StatusPemesanan::SedangDisewa)
            <div class="mt-5 rounded-xl bg-teal-50 border border-teal-200 p-4 text-sm text-teal-900">
                <p class="font-semibold">Unit sedang kamu sewa.</p>
                <p class="mt-1 text-teal-800">Jangan lupa dikembalikan paling lambat {{ $pemesanan->selesai->translatedFormat('d F Y, H:i') }} WIB.</p>
            </div>
        @elseif ($status === \App\Enums\StatusPemesanan::Terlambat)
            <div class="mt-5 rounded-xl bg-red-50 border border-red-200 p-4 text-sm text-red-900">
                <p class="font-semibold">Unit melewati batas pengembalian.</p>
                <p class="mt-1 text-red-800">Segera hubungi pemilik lewat WhatsApp dan kembalikan unit.</p>
            </div>
        @elseif ($status === \App\Enums\StatusPemesanan::Selesai)
            <div class="mt-5 rounded-xl bg-green-50 border border-green-200 p-4 text-sm text-green-900">
                <p class="font-semibold">Pemesanan selesai. Terima kasih!</p>
            </div>

            @if (! \App\Models\Ulasan::where('pemesanan_id', $pemesanan->id)->exists())
                <div class="mt-5 kartu p-5">
                    <h3 class="font-semibold text-rentra-ink">Bagaimana pengalaman sewa kamu?</h3>
                    <form method="POST" action="{{ route('publik.pemesanan.ulasan', $pemesanan->kode) }}" class="mt-3 space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold">Rating</label>
                            <select name="rating" required class="input mt-1">
                                <option value="">Pilih rating</option>
                                @for ($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}" @selected(old('rating') == $i)>{{ $i }} bintang</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold">Komentar</label>
                            <textarea name="komentar" rows="3" minlength="5" maxlength="1000" required class="input mt-1" placeholder="Ceritakan pengalamanmu...">{{ old('komentar') }}</textarea>
                        </div>
                        <button type="submit" class="tombol tombol-utama w-full">Kirim ulasan</button>
                    </form>
                </div>
            @endif
        @elseif ($status === \App\Enums\StatusPemesanan::Kedaluwarsa)
            <div class="mt-5 rounded-xl bg-gray-50 border border-gray-200 p-4 text-sm text-gray-800">
                <p class="font-semibold">Waktu tahan habis.</p>
                <p class="mt-1">Unit dilepas kembali. <a href="{{ route('publik.tampil', $pemesanan->unit->rental->slug) }}" class="font-semibold text-rentra hover:underline">Pesan lagi</a> jika masih berminat.</p>
            </div>
        @elseif ($status === \App\Enums\StatusPemesanan::Dibatalkan)
            <div class="mt-5 rounded-xl bg-gray-50 border border-gray-200 p-4 text-sm text-gray-800">
                <p class="font-semibold">Pemesanan dibatalkan.</p>
                @if ($pemesanan->catatan_tolak)
                    <p class="mt-1">{{ $pemesanan->catatan_tolak }}</p>
                @endif
            </div>
        @endif

        <div class="mt-5 border-t border-[var(--warna-tepi)] pt-4 flex flex-wrap gap-3">
            <a href="{{ route('publik.pemesanan.kalender', $pemesanan->kode) }}" class="tombol tombol-sekunder text-xs">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z"/></svg>
                Tambah ke kalender
            </a>
        </div>
    </div>
</x-layouts.publik>

