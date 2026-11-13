<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-rentra-ink leading-tight">Buat rental kamu</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-sm text-gray-600">Langkah 1 dari 2. Isi data toko rental kamu, lalu tambahkan unit pertama. Target di bawah 5 menit.</p>

            <form method="POST" action="{{ route('onboarding.simpan') }}" class="mt-6 bg-white rounded-xl border border-teal-50 shadow-sm p-6 space-y-5">
                @csrf

                <div>
                    <x-input-label for="nama" value="Nama rental" />
                    <x-text-input id="nama" name="nama" type="text" class="mt-1 block w-full" :value="old('nama')" placeholder="Contoh: Kamera Kita Rental" required autofocus />
                    <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="slug" value="Link publik" />
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">{{ parse_url(config('app.url'), PHP_URL_HOST) }}/</span>
                        <input id="slug" name="slug" type="text" value="{{ old('slug') }}" placeholder="kamera-kita" required pattern="[a-z0-9\-]+" class="block w-full rounded-none rounded-r-md border-gray-300 focus:border-rentra focus:ring-rentra text-sm" autocomplete="off">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Huruf kecil, angka, dan tanda hubung. Contoh: {{ parse_url(config('app.url'), PHP_URL_HOST) }}/kamera-kita</p>
                    <x-input-error :messages="$errors->get('slug')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="no_wa" value="Nomor WhatsApp (format 62)" />
                    <x-text-input id="no_wa" name="no_wa" type="text" inputmode="numeric" class="mt-1 block w-full" :value="old('no_wa')" placeholder="6281234567890" required />
                    <x-input-error :messages="$errors->get('no_wa')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="info_rekening" value="Info rekening untuk DP (opsional)" />
                    <textarea id="info_rekening" name="info_rekening" rows="3" maxlength="500" class="mt-1 block w-full rounded-md border-gray-300 focus:border-rentra focus:ring-rentra text-sm" placeholder="BCA 1234567890 a.n. Nama Kamu">{{ old('info_rekening') }}</textarea>
                    <x-input-error :messages="$errors->get('info_rekening')" class="mt-2" />
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="durasi_tahan_menit" value="Durasi tahan Pemesanan (menit)" />
                        <x-text-input id="durasi_tahan_menit" name="durasi_tahan_menit" type="number" min="5" max="1440" class="mt-1 block w-full" :value="old('durasi_tahan_menit', 60)" required />
                        <x-input-error :messages="$errors->get('durasi_tahan_menit')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="persen_dp" value="DP (persen)" />
                        <x-text-input id="persen_dp" name="persen_dp" type="number" min="0" max="100" class="mt-1 block w-full" :value="old('persen_dp', 50)" required />
                        <x-input-error :messages="$errors->get('persen_dp')" class="mt-2" />
                    </div>
                </div>

                <div class="pt-2">
                    <x-primary-button class="bg-rentra-accent text-rentra-ink hover:bg-amber-400 px-6 py-3 text-sm">
                        Simpan dan lanjut
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>




