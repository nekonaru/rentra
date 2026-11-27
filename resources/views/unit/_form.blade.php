@php
    $unit = $unit ?? null;
    $aturanTarif = $aturanTarif ?? null;
@endphp

<div class="grid sm:grid-cols-2 gap-4">
    <div class="sm:col-span-2">
        <x-input-label for="nama" value="Nama unit" />
        <x-text-input id="nama" name="nama" type="text" class="input mt-1" :value="old('nama', $unit?->nama)" placeholder="Contoh: Sony A7 III Kit 28-70mm" required />
        <x-input-error :messages="$errors->get('nama')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="kategori" value="Kategori" />
        <x-text-input id="kategori" name="kategori" type="text" class="input mt-1" :value="old('kategori', $unit?->kategori)" placeholder="Kamera" />
    </div>

    <div>
        <x-input-label for="merek" value="Merek" />
        <x-text-input id="merek" name="merek" type="text" class="input mt-1" :value="old('merek', $unit?->merek)" placeholder="Sony" />
    </div>

    <div>
        <x-input-label for="tahun" value="Tahun" />
        <x-text-input id="tahun" name="tahun" type="number" min="1900" max="2100" class="input mt-1" :value="old('tahun', $unit?->tahun)" placeholder="2024" />
    </div>

    <div>
        <x-input-label for="foto" value="Foto utama (maks 2 MB)" />
        <input id="foto" name="foto" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm file:mr-3 file:rounded-tombol file:border-0 file:bg-rentra-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-rentra hover:file:bg-rentra-100" />
        <x-input-error :messages="$errors->get('foto')" class="mt-2" />
        @if ($unit?->jalur_foto)
            <label class="mt-2 flex items-center gap-2 text-sm">
                <input type="checkbox" name="hapus_foto" value="1" class="rounded border-gray-300 text-rentra focus:ring-rentra">
                Hapus foto utama
            </label>
        @endif
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="foto_tambahan" value="Galeri tambahan (maks 5 foto)" />
        <input id="foto_tambahan" name="foto_tambahan[]" type="file" multiple accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm file:mr-3 file:rounded-tombol file:border-0 file:bg-rentra-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-rentra" />
        <x-input-error :messages="$errors->get('foto_tambahan.*')" class="mt-2" />
        @if ($unit?->foto?->isNotEmpty())
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($unit->foto as $foto)
                    <img src="{{ Storage::disk('public')->url($foto->jalur) }}" alt="Foto galeri" class="h-16 w-16 rounded-lg object-cover" loading="lazy">
                @endforeach
            </div>
        @endif
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="spesifikasi" value="Spesifikasi & kelengkapan" />
        <textarea id="spesifikasi" name="spesifikasi" rows="3" maxlength="1000" class="input mt-1" placeholder="Contoh: Body + lensa 28-70mm, 2 baterai, charger, tas">{{ old('spesifikasi', $unit?->spesifikasi) }}</textarea>
    </div>

    <div>
        <x-input-label for="tarif_per_hari" value="Tarif per hari (Rp)" />
        <x-text-input id="tarif_per_hari" name="tarif_per_hari" type="number" min="0" step="1000" class="input mt-1" :value="old('tarif_per_hari', $aturanTarif?->tarif_per_hari)" required />
    </div>

    <div>
        <x-input-label for="tarif_per_jam" value="Tarif per jam (Rp)" />
        <x-text-input id="tarif_per_jam" name="tarif_per_jam" type="number" min="0" step="1000" class="input mt-1" :value="old('tarif_per_jam', $aturanTarif?->tarif_per_jam)" required />
    </div>

    <div>
        <x-input-label for="tarif_weekend" value="Tarif weekend per hari (opsional)" />
        <x-text-input id="tarif_weekend" name="tarif_weekend" type="number" min="0" step="1000" class="input mt-1" :value="old('tarif_weekend', $aturanTarif?->tarif_weekend)" placeholder="Sama dengan tarif harian jika kosong" />
    </div>

    <div>
        <x-input-label for="minimal_sewa_jam" value="Minimal sewa (jam)" />
        <x-text-input id="minimal_sewa_jam" name="minimal_sewa_jam" type="number" min="1" class="input mt-1" :value="old('minimal_sewa_jam', $aturanTarif?->minimal_sewa_jam ?? 3)" required />
    </div>

    <div>
        <x-input-label for="toleransi_menit" value="Toleransi keterlambatan (menit)" />
        <x-text-input id="toleransi_menit" name="toleransi_menit" type="number" min="0" class="input mt-1" :value="old('toleransi_menit', $aturanTarif?->toleransi_menit ?? 15)" required />
    </div>

    <div class="sm:col-span-2 flex items-center gap-2">
        <input id="aktif" name="aktif" type="checkbox" value="1" class="rounded border-gray-300 text-rentra focus:ring-rentra" @checked(old('aktif', $unit?->aktif ?? true))>
        <x-input-label for="aktif" value="Unit aktif dan bisa dipesan" class="!mt-0" />
    </div>
</div>
