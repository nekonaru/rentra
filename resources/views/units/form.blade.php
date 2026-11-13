<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-rentra-ink leading-tight">{{ $unit ? 'Ubah unit' : 'Tambah unit' }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ $unit ? route('units.update', $unit) : route('units.store') }}" enctype="multipart/form-data" class="bg-white rounded-xl border border-teal-50 shadow-sm p-6 space-y-5">
                @csrf
                @if ($unit)
                    @method('PUT')
                @endif

                @include('units._form')

                <div class="flex items-center gap-3 pt-2">
                    <x-primary-button>Simpan</x-primary-button>
                    <a href="{{ route('units.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
