@php
    $unit = $unit ?? null;
    $priceRule = $priceRule ?? null;
@endphp

<div class="grid sm:grid-cols-2 gap-4">
    <div class="sm:col-span-2">
        <x-input-label for="nama" value="Nama unit" />
        <x-text-input id="nama" name="nama" type="text" class="mt-1 block w-full" :value="old('nama', $unit?->nama)" placeholder="Contoh: Sony A7 III Kit 28-70mm" required />
        <x-input-error :messages="$errors->get('nama')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="kategori" value="Kategori (opsional)" />
        <x-text-input id="kategori" name="kategori" type="text" class="mt-1 block w-full" :value="old('kategori', $unit?->kategori)" placeholder="Kamera" />
        <x-input-error :messages="$errors->get('kategori')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="foto" value="Foto (jpg, png, webp, maks 2 MB)" />
        <input id="foto" name="foto" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-teal-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-rentra hover:file:bg-teal-100" />
        <x-input-error :messages="$errors->get('foto')" class="mt-2" />
        @if ($unit?->foto_path)
            <label class="mt-2 flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="hapus_foto" value="1" class="rounded border-gray-300 text-rentra focus:ring-rentra">
                Hapus foto saat ini
            </label>
        @endif
    </div>

    <div>
        <x-input-label for="tarif_per_hari" value="Tarif per hari (Rp)" />
        <x-text-input id="tarif_per_hari" name="tarif_per_hari" type="number" min="0" step="1000" class="mt-1 block w-full" :value="old('tarif_per_hari', $priceRule?->tarif_per_hari)" placeholder="350000" required />
        <x-input-error :messages="$errors->get('tarif_per_hari')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="tarif_per_jam" value="Tarif per jam (Rp)" />
        <x-text-input id="tarif_per_jam" name="tarif_per_jam" type="number" min="0" step="1000" class="mt-1 block w-full" :value="old('tarif_per_jam', $priceRule?->tarif_per_jam)" placeholder="50000" required />
        <x-input-error :messages="$errors->get('tarif_per_jam')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="minimal_sewa_jam" value="Minimal sewa (jam)" />
        <x-text-input id="minimal_sewa_jam" name="minimal_sewa_jam" type="number" min="1" class="mt-1 block w-full" :value="old('minimal_sewa_jam', $priceRule?->minimal_sewa_jam ?? 3)" required />
        <x-input-error :messages="$errors->get('minimal_sewa_jam')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="toleransi_menit" value="Toleransi keterlambatan (menit)" />
        <x-text-input id="toleransi_menit" name="toleransi_menit" type="number" min="0" class="mt-1 block w-full" :value="old('toleransi_menit', $priceRule?->toleransi_menit ?? 15)" required />
        <x-input-error :messages="$errors->get('toleransi_menit')" class="mt-2" />
    </div>

    <div class="sm:col-span-2 flex items-center gap-2">
        <input id="aktif" name="aktif" type="checkbox" value="1" class="rounded border-gray-300 text-rentra focus:ring-rentra" @checked(old('aktif', $unit?->aktif ?? true))>
        <x-input-label for="aktif" value="Unit aktif dan bisa dibooking" class="!mt-0" />
    </div>
</div>
