<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-rentra-ink leading-tight">Pengaturan rental</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-sm text-[var(--warna-teks-redup)]">Ubah data rental kamu. Link publik tidak bisa diubah supaya link yang sudah dibagikan tetap jalan.</p>

            @if (session('status'))
                <div class="mt-4 rounded-xl bg-rentra-50 border border-rentra-100 text-rentra text-sm font-medium px-4 py-3">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('pengaturan.perbarui') }}" enctype="multipart/form-data" class="mt-6 kartu p-6 space-y-5">
                @csrf
                @method('PUT')

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="nama" value="Nama rental" />
                        <x-text-input id="nama" name="nama" type="text" class="input mt-1" :value="old('nama', $rental->nama)" required />
                    </div>
                    <div>
                        <x-input-label for="no_wa" value="Nomor WhatsApp" />
                        <x-text-input id="no_wa" name="no_wa" type="text" inputmode="numeric" class="input mt-1" :value="old('no_wa', $rental->no_wa)" required />
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="durasi_tahan_menit" value="Durasi tahan (menit)" />
                        <x-text-input id="durasi_tahan_menit" name="durasi_tahan_menit" type="number" min="5" max="1440" class="input mt-1" :value="old('durasi_tahan_menit', $rental->durasi_tahan_menit)" required />
                    </div>
                    <div>
                        <x-input-label for="persen_dp" value="DP (persen)" />
                        <x-text-input id="persen_dp" name="persen_dp" type="number" min="0" max="100" class="input mt-1" :value="old('persen_dp', $rental->persen_dp)" required />
                    </div>
                </div>

                <div>
                    <x-input-label for="info_rekening" value="Info rekening untuk DP" />
                    <textarea id="info_rekening" name="info_rekening" rows="3" maxlength="500" class="input mt-1">{{ old('info_rekening', $rental->info_rekening) }}</textarea>
                </div>

                <div class="border-t border-[var(--warna-tepi)] pt-5">
                    <h3 class="font-semibold text-rentra-ink mb-3">Operasional & denda</h3>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="jam_buka" value="Jam buka" />
                            <x-text-input id="jam_buka" name="jam_buka" type="time" class="input mt-1" :value="old('jam_buka', $rental->jam_buka?->format('H:i'))" />
                        </div>
                        <div>
                            <x-input-label for="jam_tutup" value="Jam tutup" />
                            <x-text-input id="jam_tutup" name="jam_tutup" type="time" class="input mt-1" :value="old('jam_tutup', $rental->jam_tutup?->format('H:i'))" />
                        </div>
                        <div>
                            <x-input-label for="denda_per_jam" value="Denda keterlambatan per jam (Rp)" />
                            <x-text-input id="denda_per_jam" name="denda_per_jam" type="number" min="0" step="1000" class="input mt-1" :value="old('denda_per_jam', $rental->denda_per_jam)" />
                        </div>
                        <div>
                            <x-input-label for="denda_maks" value="Maksimal denda (Rp)" />
                            <x-text-input id="denda_maks" name="denda_maks" type="number" min="0" step="1000" class="input mt-1" :value="old('denda_maks', $rental->denda_maks)" />
                        </div>
                    </div>
                </div>

                <div class="border-t border-[var(--warna-tepi)] pt-5">
                    <h3 class="font-semibold text-rentra-ink mb-3">Identitas halaman publik</h3>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="deskripsi" value="Deskripsi singkat" />
                            <textarea id="deskripsi" name="deskripsi" rows="3" maxlength="500" class="input mt-1" placeholder="Rental kamera terpercaya di kota kamu">{{ old('deskripsi', $rental->deskripsi) }}</textarea>
                        </div>
                        <div>
                            <x-input-label for="warna_aksen" value="Warna aksen (hex)" />
                            <x-text-input id="warna_aksen" name="warna_aksen" type="text" class="input mt-1" :value="old('warna_aksen', $rental->warna_aksen)" placeholder="#0F766E" />
                            <div>
                                <x-input-label for="logo" value="Logo rental (maks 1 MB)" />
                                <input id="logo" name="logo" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm file:rounded-tombol file:border-0 file:bg-rentra-50 file:px-4 file:py-2 file:text-rentra">
                                @if ($rental->logo_path)
                                    <img src="{{ Storage::disk('public')->url($rental->logo_path) }}" alt="Logo rental" class="mt-2 h-12 w-12 rounded-lg object-cover">
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <x-tombol>Simpan perubahan</x-tombol>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
