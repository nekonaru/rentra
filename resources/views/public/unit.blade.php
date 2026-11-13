<x-layouts.public :rental="$rental" :title="$unit->nama">
    <a href="{{ route('public.show', $rental->slug) }}" class="text-xs font-semibold text-rentra hover:underline">&larr; Kembali ke daftar unit</a>

    <div class="mt-3 grid gap-6 lg:grid-cols-5">
        <!-- Unit info -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl border border-teal-50 shadow-sm overflow-hidden">
                @if ($unit->foto_path)
                    <img src="{{ Storage::disk('public')->url($unit->foto_path) }}" alt="Foto {{ $unit->nama }}" class="h-48 sm:h-56 w-full object-cover">
                @else
                    <div class="h-48 sm:h-56 w-full bg-teal-50 flex items-center justify-center text-rentra/40">
                        <svg viewBox="0 0 24 24" class="h-16 w-16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h3l2-2h6l2 2h3a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1Z"/><circle cx="12" cy="13" r="4"/></svg>
                    </div>
                @endif
                <div class="p-5">
                    <h1 class="text-xl font-bold text-rentra-ink leading-snug">{{ $unit->nama }}</h1>
                    @if ($unit->kategori)
                        <p class="text-xs text-gray-500 mt-0.5">{{ $unit->kategori }}</p>
                    @endif
                    @if ($priceRule)
                        <dl class="mt-4 space-y-1.5 text-sm">
                            <div class="flex items-baseline justify-between gap-4">
                                <dt class="text-gray-500">Tarif per hari</dt>
                                <dd class="font-semibold text-rentra-ink">Rp {{ number_format((float) $priceRule->tarif_per_hari, 0, ',', '.') }}</dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-4">
                                <dt class="text-gray-500">Tarif per jam</dt>
                                <dd class="font-semibold text-rentra-ink">Rp {{ number_format((float) $priceRule->tarif_per_jam, 0, ',', '.') }}</dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-4">
                                <dt class="text-gray-500">Minimal sewa</dt>
                                <dd class="font-semibold text-rentra-ink">{{ $priceRule->minimal_sewa_jam }} jam</dd>
                            </div>
                        </dl>
                    @endif
                </div>
            </div>

            <a href="https://wa.me/{{ $rental->no_wa }}?text={{ rawurlencode("Halo, saya mau tanya tentang unit {$unit->nama}") }}" target="_blank" rel="noopener" class="mt-4 flex items-center justify-center gap-2 rounded-xl bg-status-tersedia text-white px-5 py-3 text-sm font-semibold hover:bg-green-700 transition">
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 2.1.55 4.14 1.6 5.95L2.05 22l4.34-1.13a9.87 9.87 0 0 0 5.65 1.8h.01c5.46 0 9.9-4.45 9.9-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2Zm0 18.13h-.01a8.2 8.2 0 0 1-4.2-1.15l-.3-.18-2.48.65.66-2.42-.2-.31a8.17 8.17 0 0 1-1.26-4.37c0-4.53 3.69-8.22 8.24-8.22a8.2 8.2 0 0 1 5.82 2.41 8.18 8.18 0 0 1 2.4 5.83c0 4.53-3.7 8.22-8.16 8.22Z"/></svg>
                Tanya pemilik via WhatsApp
            </a>
        </div>

        <!-- Calendar + calculator -->
        <div class="lg:col-span-3 space-y-6">
            <section class="bg-white rounded-xl border border-teal-50 shadow-sm p-5">
                <div class="flex items-center justify-between gap-2">
                    <a href="{{ route('public.unit', [$rental->slug, $unit->id, 'bulan' => $bulan->copy()->subMonth()->format('Y-m')]) }}" class="p-2 rounded-lg hover:bg-gray-50 text-gray-500" aria-label="Bulan sebelumnya">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                    </a>
                    <h2 class="font-semibold text-rentra-ink">{{ $bulan->translatedFormat('F Y') }}</h2>
                    <a href="{{ route('public.unit', [$rental->slug, $unit->id, 'bulan' => $bulan->copy()->addMonth()->format('Y-m')]) }}" class="p-2 rounded-lg hover:bg-gray-50 text-gray-500" aria-label="Bulan berikutnya">
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
            <section
                class="bg-white rounded-xl border border-teal-50 shadow-sm p-5"
                x-data="{
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
                            const res = await fetch(@js(route('public.preview', [$rental->slug, $unit->id])), {
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
                }"
            >
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
                        <template x-if="hasil && hasil.hari > 0">
                            <div class="flex justify-between"><dt class="text-gray-600"><span x-text="hasil.hari"></span> hari</dt><dd class="font-medium" x-text="rp(hasil.hari * {{ (float) ($priceRule->tarif_per_hari ?? 0) }})"></dd></div>
                        </template>
                        <div class="flex justify-between"><dt class="text-gray-600">Sisa <span x-text="hasil ? hasil.sisa_jam.toFixed(2).replace('.', ',') : 0"></span> jam</dt><dd class="font-medium" x-text="hasil ? rp(hasil.jam_part) : ''"></dd></div>
                        @if ($rental->persen_dp > 0)
                            <div class="flex justify-between"><dt class="text-gray-600">DP {{ $rental->persen_dp }}%</dt><dd class="font-medium" x-text="hasil ? rp(hasil.dp_nominal) : ''"></dd></div>
                        @endif
                        <div class="flex justify-between border-t border-teal-100 pt-2 text-base font-bold text-rentra-ink"><dt>Total</dt><dd x-text="hasil ? rp(hasil.total) : ''"></dd></div>
                    </dl>
                    <p x-show="hasil && ! hasil.tersedia" x-cloak class="mt-2 rounded-lg bg-red-50 border border-red-100 px-3 py-2 text-xs font-semibold text-status-terlambat">Jadwal ini baru saja diambil. Pilih waktu lain.</p>
                </div>
            </section>
        </div>
    </div>
</x-layouts.public>
