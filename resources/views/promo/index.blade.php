<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-rentra-ink leading-tight">Kode Promo</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-xl bg-rentra-50 border border-rentra-100 text-rentra text-sm px-4 py-3">{{ session('status') }}</div>
            @endif

            <div class="grid gap-6 md:grid-cols-2">
                <div class="kartu p-5">
                    <h3 class="font-semibold text-rentra-ink mb-4">Buat promo baru</h3>
                    <form method="POST" action="{{ route('promo.buat') }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold">Kode</label>
                            <x-text-input name="kode" type="text" class="input mt-1 uppercase" :value="old('kode')" placeholder="HEMAT10" required maxlength="32" />
                            @error('kode') <p class="text-xs text-status-terlambat mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold">Jenis diskon</label>
                            <select name="jenis" class="input mt-1" required>
                                <option value="persen" @selected(old('jenis') === 'persen')">Persen (%)</option>
                                <option value="nominal" @selected(old('jenis') === 'nominal')">Nominal (Rp)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold">Nilai</label>
                            <x-text-input name="nilai" type="number" min="0" step="1" class="input mt-1" :value="old('nilai')" required />
                            @error('nilai') <p class="text-xs text-status-terlambat mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold">Min. sewa (jam)</label>
                                <x-text-input name="minimal_sewa_jam" type="number" min="1" class="input mt-1" :value="old('minimal_sewa_jam', 3)" required />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold">Kuota</label>
                                <x-text-input name="kuota" type="number" min="1" class="input mt-1" :value="old('kuota', 100)" required />
                            </div>
                        </div>
                        <x-tombol>Buat promo</x-tombol>
                    </form>
                </div>

                <div class="kartu p-5">
                    <h3 class="font-semibold text-rentra-ink mb-4">Promo aktif ({{ $promo->count() }})</h3>
                    @if ($promo->isEmpty())
                        <x-kosong judul="Belum ada promo" pesan="Promo bisa dipakai penyewa saat booking." />
                    @else
                        <div class="space-y-3">
                            @foreach ($promo as $item)
                                <div class="border-b border-[var(--warna-tepi)] pb-3 last:border-0 flex items-center justify-between gap-3">
                                    <div>
                                        <div class="font-bold tracking-widest text-rentra-ink">{{ $item->kode }}</div>
                                        <div class="text-xs text-[var(--warna-teks-redup)]">
                                            {{ $item->jenis === 'persen' ? $item->nilai.'%' : 'Rp '.number_format((float) $item->nilai, 0, ',', '.') }}
                                            &middot; min {{ $item->minimal_sewa_jam }} jam &middot; {{ $item->terpakai }}/{{ $item->kuota }} terpakai
                                        </div>
                                    </div>
                                    <form method="POST" action="{{ route('promo.hapus', $item) }}" onsubmit="return confirm('Hapus promo ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-status-terlambat font-semibold hover:underline">Hapus</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
