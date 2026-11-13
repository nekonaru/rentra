<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-rentra-ink leading-tight">Pengaturan rental</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-sm text-gray-600">Ubah data rental kamu. Link publik (<span class="font-semibold">{{ parse_url(config('app.url'), PHP_URL_HOST) }}/{{ $rental->slug }}</span>) tidak bisa diubah supaya link yang sudah dibagikan tetap jalan.</p>

            @if (session('status'))
                <div class="mt-4 rounded-xl bg-teal-50 border border-teal-100 text-rentra text-sm font-medium px-4 py-3">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('pengaturan.perbarui') }}" class="mt-6 bg-white rounded-xl border border-teal-50 shadow-sm p-6 space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label for="nama" value="Nama rental" />
                    <x-text-input id="nama" name="nama" type="text" class="mt-1 block w-full" :value="old('nama', $rental->nama)" required />
                    <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="no_wa" value="Nomor WhatsApp (format 62)" />
                    <x-text-input id="no_wa" name="no_wa" type="text" inputmode="numeric" class="mt-1 block w-full" :value="old('no_wa', $rental->no_wa)" required />
                    <x-input-error :messages="$errors->get('no_wa')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="info_rekening" value="Info rekening untuk DP" />
                    <textarea id="info_rekening" name="info_rekening" rows="3" maxlength="500" class="mt-1 block w-full rounded-md border-gray-300 focus:border-rentra focus:ring-rentra text-sm">{{ old('info_rekening', $rental->info_rekening) }}</textarea>
                    <x-input-error :messages="$errors->get('info_rekening')" class="mt-2" />
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="durasi_tahan_menit" value="Durasi tahan (menit)" />
                        <x-text-input id="durasi_tahan_menit" name="durasi_tahan_menit" type="number" min="5" max="1440" class="mt-1 block w-full" :value="old('durasi_tahan_menit', $rental->durasi_tahan_menit)" required />
                        <x-input-error :messages="$errors->get('durasi_tahan_menit')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="persen_dp" value="DP (persen)" />
                        <x-text-input id="persen_dp" name="persen_dp" type="number" min="0" max="100" class="mt-1 block w-full" :value="old('persen_dp', $rental->persen_dp)" required />
                        <x-input-error :messages="$errors->get('persen_dp')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-primary-button>Simpan perubahan</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>


