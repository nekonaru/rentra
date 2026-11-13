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

            @if ($rental->unit->isNotEmpty())
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





