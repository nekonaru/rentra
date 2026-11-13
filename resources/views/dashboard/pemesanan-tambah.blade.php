<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-rentra-ink leading-tight">Tambah Pemesanan Manual</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-sm text-gray-600">Untuk pelanggan yang booking langsung (walk-in atau lewat chat). Pemesanan langsung berstatus terkonfirmasi.</p>

            @if ($errors->any())
                <div class="mt-4 rounded-xl bg-red-50 border border-red-100 px-4 py-3 text-sm font-medium text-status-terlambat">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($unit->isEmpty())
                <div class="mt-6 bg-white rounded-xl border border-teal-50 shadow-sm p-10 text-center">
                    <p class="font-semibold text-rentra-ink">Belum ada unit aktif.</p>
                    <a href="{{ route('unit.create') }}" class="mt-4 inline-flex items-center rounded-xl bg-rentra-accent px-5 py-2 text-sm font-semibold text-rentra-ink hover:bg-amber-400 transition">Tambah unit</a>
                </div>
            @else
                <form method="POST" action="{{ route('pemesanan.simpan-tambah') }}" class="mt-6 bg-white rounded-xl border border-teal-50 shadow-sm p-6 space-y-5">
                    @csrf

                    <div>
                        <x-input-label for="unit_id" value="Unit" />
                        <select id="unit_id" name="unit_id" required class="mt-1 block w-full rounded-xl border-gray-200 focus:border-rentra focus:ring-rentra text-sm">
                            <option value="">Pilih unit...</option>
                            @foreach ($unit as $item)
                                <option value="{{ $item->id }}" @selected(old('unit_id') == $item->id)>{{ $item->nama }} - Rp {{ number_format((float) ($item->aturanTarif->tarif_per_hari ?? 0), 0, ',', '.') }}/hari</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <x-input-label for="nama_penyewa" value="Nama penyewa" />
                            <x-text-input id="nama_penyewa" name="nama_penyewa" type="text" class="mt-1 block w-full" :value="old('nama_penyewa')" placeholder="Nama lengkap" required maxlength="120" />
                        </div>
                        <div class="sm:col-span-2">
                            <x-input-label for="wa" value="Nomor WhatsApp (format 62)" />
                            <x-text-input id="wa" name="wa" type="text" inputmode="numeric" class="mt-1 block w-full" :value="old('wa')" placeholder="6281234567890" required />
                        </div>
                        <div>
                            <x-input-label for="mulai" value="Mulai" />
                            <input type="datetime-local" id="mulai" name="mulai" value="{{ old('mulai') }}" required class="mt-1 block w-full rounded-xl border-gray-200 focus:border-rentra focus:ring-rentra text-sm">
                        </div>
                        <div>
                            <x-input-label for="selesai" value="Selesai" />
                            <input type="datetime-local" id="selesai" name="selesai" value="{{ old('selesai') }}" required class="mt-1 block w-full rounded-xl border-gray-200 focus:border-rentra focus:ring-rentra text-sm">
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <x-primary-button>Simpan Pemesanan</x-primary-button>
                        <a href="{{ route('pemesanan.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Batal</a>
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
