<x-layouts.publik :rental="$rental" :title="$unit->nama">
    <a href="{{ route('publik.tampil', $rental->slug) }}" class="text-xs font-semibold text-rentra hover:underline">&larr; Kembali ke daftar unit</a>

    @if ($errors->any())
        <div class="mt-3 rounded-xl bg-red-50 border border-red-100 px-4 py-3 text-sm font-medium text-status-terlambat">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mt-3 grid gap-6 lg:grid-cols-5">
        <!-- Info unit -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl border border-teal-50 shadow-sm overflow-hidden">
                @if ($unit->jalur_foto)
                    <img src="{{ Storage::disk('public')->url($unit->jalur_foto) }}" alt="Foto {{ $unit->nama }}" class="h-48 sm:h-56 w-full object-cover">
                @else
                    <div class="h-48 sm:h-56 w-full bg-teal-50 flex items-center justify-center text-rentra/40">
                        <svg viewBox="0 0 24 24" class="h-16 w-16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h3l2-2h6l2 2h3a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1Z"/><circle cx="12" cy="13" r="4"/></svg>
                    </div>
                @endif
                <div class="p-5">
                    <h1 class="text-xl font-bold text-rentra-ink leading-snug">{{ $unit->nama }}</h1>
                    @if ($unit->kategori || $unit->merek)
                        <p class="text-xs text-[var(--warna-teks-redup)] mt-0.5">{{ collect([$unit->merek, $unit->kategori, $unit->tahun])->filter()->join(' · ') }}</p>
                    @endif

                    @if ($ratingRata > 0)
                        <div class="mt-2 text-sm text-amber-500 font-semibold" aria-label="Rating rata-rata {{ number_format($ratingRata, 1) }} dari 5">
                            ★ {{ number_format($ratingRata, 1) }} <span class="text-[var(--warna-teks-redup)] font-normal text-xs">({{ $ulasan->count() }} ulasan)</span>
                        </div>
                    @endif

                    @if ($unit->spesifikasi)
                        <div class="mt-4 rounded-tombol bg-rentra-50 p-3 text-sm">
                            <div class="font-semibold text-rentra text-xs mb-1">Spesifikasi & kelengkapan</div>
                            <p class="whitespace-pre-line text-rentra-ink">{{ $unit->spesifikasi }}</p>
                        </div>
                    @endif
                    @if ($aturanTarif)
                        <dl class="mt-4 space-y-1.5 text-sm">
                            <div class="flex items-baseline justify-between gap-4">
                                <dt class="text-gray-500">Tarif per hari</dt>
                                <dd class="font-semibold text-rentra-ink">Rp {{ number_format((float) $aturanTarif->tarif_per_hari, 0, ',', '.') }}</dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-4">
                                <dt class="text-gray-500">Tarif per jam</dt>
                                <dd class="font-semibold text-rentra-ink">Rp {{ number_format((float) $aturanTarif->tarif_per_jam, 0, ',', '.') }}</dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-4">
                                <dt class="text-gray-500">Minimal sewa</dt>
                                <dd class="font-semibold text-rentra-ink">{{ $aturanTarif->minimal_sewa_jam }} jam</dd>
                            </div>
                        </dl>
                    @endif
                </div>
            </div>

            {{-- Galeri foto --}}
            @if ($unit->foto->isNotEmpty())
                <div class="mt-3 kartu p-4" x-data="{ gambar: null }">
                    <div class="flex gap-2 overflow-x-auto pb-2">
                        @foreach ($unit->foto as $foto)
                            <button type="button" @click="gambar = '{{ Storage::disk('public')->url($foto->jalur) }}'" class="shrink-0">
                                <img src="{{ Storage::disk('public')->url($foto->jalur) }}" alt="Foto {{ $unit->nama }}" class="h-16 w-16 rounded-lg object-cover hover:ring-2 hover:ring-rentra transition" loading="lazy">
                            </button>
                        @endforeach
                    </div>
                    <div x-show="gambar" x-cloak class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4" @click.self="gambar = null">
                        <img :src="gambar" alt="Foto besar" class="max-h-full max-w-full rounded-xl">
                        <button type="button" @click="gambar = null" class="absolute top-4 right-4 text-white text-2xl" aria-label="Tutup">×</button>
                    </div>
                </div>
            @endif

            {{-- Ulasan --}}
            @if ($ulasan->isNotEmpty())
                <div class="mt-3 kartu p-5">
                    <h2 class="font-semibold text-rentra-ink">Ulasan penyewa</h2>
                    <div class="mt-3 space-y-3">
                        @foreach ($ulasan as $item)
                            <div class="border-b border-[var(--warna-tepi)] pb-3 last:border-0 last:pb-0">
                                <div class="text-amber-500 text-sm">@for ($i = 0; $i < $item->rating; $i++)★@endfor</div>
                                <p class="text-sm mt-1">{{ $item->komentar }}</p>
                                <div class="text-[11px] text-[var(--warna-teks-redup)] mt-1">{{ $item->created_at->translatedFormat('d M Y') }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <a href="https://wa.me/{{ $rental->no_wa }}?text={{ rawurlencode("Halo, saya mau tanya tentang unit {$unit->nama}") }}" target="_blank" rel="noopener" class="mt-4 flex items-center justify-center gap-2 rounded-xl border border-status-tersedia/40 text-status-tersedia px-5 py-3 text-sm font-semibold hover:bg-green-50 transition">
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 2.1.55 4.14 1.6 5.95L2.05 22l4.34-1.13a9.87 9.87 0 0 0 5.65 1.8h.01c5.46 0 9.9-4.45 9.9-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2Zm0 18.13h-.01a8.2 8.2 0 0 1-4.2-1.15l-.3-.18-2.48.65.66-2.42-.2-.31a8.17 8.17 0 0 1-1.26-4.37c0-4.53 3.69-8.22 8.24-8.22a8.2 8.2 0 0 1 5.82 2.41 8.18 8.18 0 0 1 2.4 5.83c0 4.53-3.7 8.22-8.16 8.22Z"/></svg>
                Tanya pemilik via WhatsApp
            </a>
        </div>

        <!-- Kalender + kalkulator + pemesanan -->
        <div class="lg:col-span-3 space-y-6" x-data="{
            mulai: @js(old('mulai')),
            selesai: @js(old('selesai')),
            hasil: null,
            loading: false,
            error: null,
            rp(n) { return 'Rp ' + Math.round(n).toLocaleString('id-ID'); },
            async hitung() {
                this.error = null;
                if (! this.mulai || ! this.selesai) { this.hasil = null; return; }
                this.loading = true;
                try {
                    const res = await fetch(@js(route('publik.pratinjau-harga', [$rental->slug, $unit->id])), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': @js(csrf_token()) },
                        body: JSON.stringify({ mulai: this.mulai.replace('T', ' '), selesai: this.selesai.replace('T', ' ') }),
                    });
                    if (! res.ok) { const body = await res.json().catch(() => null); this.error = body?.message ?? 'Tanggal tidak valid.'; this.hasil = null; return; }
                    this.hasil = await res.json();
                } catch (e) {
                    this.error = 'Gagal menghitung harga. Coba lagi.';
                } finally {
                    this.loading = false;
                }
            },
        }">
            <section class="bg-white rounded-xl border border-teal-50 shadow-sm p-5">
                <div class="flex items-center justify-between gap-2">
                    <a href="{{ route('publik.unit', [$rental->slug, $unit->id, 'bulan' => $bulan->copy()->subMonth()->format('Y-m')]) }}" class="p-2 rounded-lg hover:bg-gray-50 text-gray-500" aria-label="Bulan sebelumnya">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                    </a>
                    <h2 class="font-semibold text-rentra-ink">{{ $bulan->translatedFormat('F Y') }}</h2>
                    <a href="{{ route('publik.unit', [$rental->slug, $unit->id, 'bulan' => $bulan->copy()->addMonth()->format('Y-m')]) }}" class="p-2 rounded-lg hover:bg-gray-50 text-gray-500" aria-label="Bulan berikutnya">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg>
                    </a>
                </div>

                @php
                    $weekdays = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
                    $gridStart = $bulan->copy()->startOfMonth()->startOfWeek(\Carbon\Carbon::MONDAY);
                    $gridEnd = $bulan->copy()->endOfMonth()->endOfWeek(\Carbon\Carbon::SUNDAY);
                    $today = now()->toDateString();
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
                            $info = $ringkasan[$key] ?? null;
                            $inMonth = $date->month === $bulan->month;
                            $isPast = $date->lt(now()->startOfDay());
                            $state = $isPast ? 'lewat' : ($info['state'] ?? 'tersedia');
                            $cellClasses = match ($state) {
                                'penuh' => 'bg-gray-100 text-gray-400',
                                'sebagian' => 'bg-amber-50 text-amber-800',
                                'lewat' => 'text-gray-300',
                                default => 'bg-white text-rentra-ink border border-green-100',
                            };
                            $dotClasses = match ($state) {
                                'penuh' => 'bg-status-diblok',
                                'sebagian' => 'bg-status-ditahan',
                                'lewat' => 'bg-gray-200',
                                default => 'bg-status-tersedia',
                            };
                        @endphp
                        <div class="rounded-lg px-1 py-1.5 text-center {{ $cellClasses }} {{ $inMonth ? '' : 'opacity-30' }}" title="{{ match ($state) { 'penuh' => 'Penuh', 'sebagian' => 'Sebagian terisi', 'lewat' => 'Lewat', default => 'Tersedia' } }}">
                            <span class="text-xs font-semibold">{{ $date->day }}</span>
                            <span class="mx-auto mt-1 block h-1.5 w-1.5 rounded-full {{ $dotClasses }}"></span>
                        </div>
                    @endfor
                </div>
                <ul class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-[11px] text-gray-500">
                    <li class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-status-tersedia"></span>Tersedia</li>
                    <li class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-status-ditahan"></span>Sebagian terisi</li>
                    <li class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-status-diblok"></span>Penuh</li>
                </ul>
            </section>

            <!-- Kalkulator harga -->
            <section class="bg-white rounded-xl border border-teal-50 shadow-sm p-5">
                <h2 class="font-semibold text-rentra-ink">Kalkulator harga</h2>
                <div class="mt-3 grid sm:grid-cols-2 gap-3">
                    <label class="block text-xs font-semibold text-gray-600">
                        Mulai
                        <input type="datetime-local" x-model="mulai" @change="hitung()" class="mt-1 block w-full rounded-xl border-gray-200 focus:border-rentra focus:ring-rentra text-sm">
                    </label>
                    <label class="block text-xs font-semibold text-gray-600">
                        Selesai
                        <input type="datetime-local" x-model="selesai" @change="hitung()" class="mt-1 block w-full rounded-xl border-gray-200 focus:border-rentra focus:ring-rentra text-sm">
                    </label>
                </div>

                <div x-show="loading" class="mt-3 text-xs text-gray-500">Menghitung...</div>
                <div x-show="error" x-cloak class="mt-3 rounded-xl bg-red-50 border border-red-100 px-3 py-2 text-xs font-medium text-status-terlambat" x-text="error"></div>

                <div x-show="hasil" x-cloak class="mt-4 rounded-xl bg-teal-50/60 border border-teal-100 p-4">
                    <dl class="text-sm space-y-1">
                        <div class="flex justify-between" x-show="hasil && hasil.hari > 0"><dt class="text-gray-600"><span x-text="hasil?.hari"></span> hari</dt><dd class="font-medium" x-text="hasil ? rp(hasil.hari * {{ (float) ($aturanTarif->tarif_per_hari ?? 0) }}) : ''"></dd></div>
                        <div class="flex justify-between"><dt class="text-gray-600">Sisa <span x-text="hasil ? hasil.sisa_jam.toFixed(2).replace('.', ',') : 0"></span> jam</dt><dd class="font-medium" x-text="hasil ? rp(hasil.jam_part) : ''"></dd></div>
                        @if ($rental->persen_dp > 0)
                            <div class="flex justify-between"><dt class="text-gray-600">DP {{ $rental->persen_dp }}%</dt><dd class="font-medium" x-text="hasil ? rp(hasil.dp_nominal) : ''"></dd></div>
                        @endif
                        <div class="flex justify-between border-t border-teal-100 pt-2 text-base font-bold text-rentra-ink"><dt>Total</dt><dd x-text="hasil ? rp(hasil.total) : ''"></dd></div>
                    </dl>
                    <p x-show="hasil && ! hasil.tersedia" x-cloak class="mt-2 rounded-lg bg-red-50 border border-red-100 px-3 py-2 text-xs font-semibold text-status-terlambat">Jadwal ini baru saja diambil. Pilih waktu lain.</p>
                </div>
            </section>

            <!-- Form pemesanan -->
            <form method="POST" action="{{ route('publik.pemesanan.simpan', [$rental->slug, $unit->id]) }}" class="bg-white rounded-xl border border-teal-50 shadow-sm p-5">
                @csrf
                <h2 class="font-semibold text-rentra-ink">Pesan unit ini</h2>
                <p class="mt-0.5 text-xs text-gray-500">Pilih jadwal di atas, lalu isi data kamu. Unit ditahan {{ $rental->durasi_tahan_menit }} menit sampai DP masuk.</p>
                <input type="hidden" name="mulai" x-bind:value="mulai">
                <input type="hidden" name="selesai" x-bind:value="selesai">

                <div class="mt-3 grid gap-3">
                    <label class="block text-xs font-semibold text-gray-600">
                        Nama kamu
                        <input type="text" name="nama_penyewa" value="{{ old('nama_penyewa') }}" placeholder="Nama lengkap" required maxlength="120" class="mt-1 block w-full rounded-xl border-gray-200 focus:border-rentra focus:ring-rentra text-sm">
                    </label>
                    <label class="block text-xs font-semibold text-gray-600">
                        Nomor WhatsApp (format 62)
                        <input type="text" name="wa" value="{{ old('wa') }}" inputmode="numeric" placeholder="6281234567890" required class="mt-1 block w-full rounded-xl border-gray-200 focus:border-rentra focus:ring-rentra text-sm">
                    </label>
                    <label class="block text-xs font-semibold">
                        Kode promo (opsional)
                        <input type="text" name="kode_promo" value="{{ old('kode_promo') }}" placeholder="HEMAT10" class="input mt-1 uppercase tracking-widest" autocomplete="off">
                    </label>
                </div>

                <button type="submit" x-bind:disabled="! hasil || ! hasil.tersedia || loading" class="mt-4 w-full inline-flex items-center justify-center rounded-xl bg-rentra-accent px-5 py-3 text-sm font-bold text-rentra-ink hover:bg-amber-400 transition disabled:opacity-40 disabled:cursor-not-allowed">
                    Pesan sekarang
                </button>
                <p class="mt-2 text-[11px] text-gray-400 text-center">Harga dan ketersediaan final dikonfirmasi server saat pemesanan.</p>
            </form>
        </div>
    </div>
</x-layouts.publik>





