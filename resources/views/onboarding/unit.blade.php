<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-rentra-ink leading-tight">Tambah unit pertama</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-sm text-gray-600">Langkah 2 dari 2. Tambahkan unit beserta tarifnya. Kamu bisa menambah unit lagi kapan pun dari menu Unit.</p>

            @if (session('status'))
                <div class="mt-4 rounded-xl bg-teal-50 border border-teal-100 text-rentra text-sm font-medium px-4 py-3">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('onboarding.unit.simpan') }}" enctype="multipart/form-data" class="mt-6 bg-white rounded-xl border border-teal-50 shadow-sm p-6 space-y-5">
                @csrf
                @include('unit._form')

                <div class="flex flex-col sm:flex-row gap-3 pt-2">
                    <x-primary-button class="justify-center">Simpan unit</x-primary-button>
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-3 text-xs font-semibold uppercase tracking-widest text-rentra border border-rentra/30 hover:bg-teal-50 transition">Lanjut ke dashboard</a>
                </div>
            </form>

            @if ($rental->unit->isEmpty())
        <div class="kartu p-6">
            <h3 class="font-semibold text-rentra-ink mb-2">Tambah beberapa unit sekaligus</h3>
            <p class="text-xs text-[var(--warna-teks-redup)] mb-3">Satu nama per baris, maksimal 10 unit. Tarif di bawah dipakai untuk semua.</p>
            <form method="POST" action="{{ route('onboarding.unit.simpan') }}">
                @csrf
                <textarea name="nama_unit" rows="4" class="input" placeholder="Sony A7 III&#10;Lensa 35mm f/1.8&#10;DJI Mini 4 Pro"></textarea>
                <div class="grid sm:grid-cols-2 gap-3 mt-3">
                    <x-text-input name="tarif_per_hari" type="number" min="0" step="1000" class="input" :value="old('tarif_per_hari', 350000)" placeholder="Tarif per hari" required />
                    <x-text-input name="tarif_per_jam" type="number" min="0" step="1000" class="input" :value="old('tarif_per_jam', 50000)" placeholder="Tarif per jam" required />
                    <x-text-input name="minimal_sewa_jam" type="number" min="1" class="input" :value="old('minimal_sewa_jam', 3)" placeholder="Minimal sewa" required />
                    <x-text-input name="toleransi_menit" type="number" min="0" class="input" :value="old('toleransi_menit', 15)" placeholder="Toleransi" required />
                </div>
                <x-tombol class="mt-4">Tambah unit massal</x-tombol>
            </form>
        </div>
        <p class="text-center text-xs text-[var(--warna-teks-redup)] my-4">atau tambah satu unit dengan foto</p>
    @endif

    @if ($rental->unit->isNotEmpty())
        <h3 class="font-semibold text-rentra-ink mt-6">Unit tersimpan ({{ $rental->unit->count() }})</h3>
                <div class="mt-6 bg-white rounded-xl border border-teal-50 shadow-sm p-6">
                    <h3 class="font-semibold text-rentra-ink">Unit tersimpan ({{ $rental->unit->count() }})</h3>
                    <ul class="mt-3 divide-y divide-gray-100">
                        @foreach ($rental->unit as $unit)
                            <li class="py-2 flex items-center justify-between gap-4 text-sm">
                                <span class="font-medium">{{ $unit->nama }}</span>
                                <span class="text-gray-500">Rp {{ number_format((float) ($unit->aturanTarif->tarif_per_hari ?? 0), 0, ',', '.') }}/hari</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>





