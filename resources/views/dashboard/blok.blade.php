<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-rentra-ink leading-tight">Blok jadwal unit</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-1">
                <form method="POST" action="{{ route('blok.store') }}" class="bg-white rounded-xl border border-teal-50 shadow-sm p-6 space-y-4">
                    @csrf
                    <h3 class="font-semibold text-rentra-ink">Tambah blok</h3>
                    <p class="text-xs text-gray-500">Unit tidak bisa dibooking selama rentang ini (mis. servis, dipakai pribadi).</p>

                    @if (session('konflik'))
                        <div class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-xs font-medium text-status-terlambat">{{ session('konflik') }}</div>
                    @endif
                    @if (session('status'))
                        <div class="rounded-xl bg-teal-50 border border-teal-100 px-4 py-3 text-xs font-medium text-rentra">{{ session('status') }}</div>
                    @endif
                    @if ($errors->any())
                        <ul class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-xs font-medium text-status-terlambat list-disc list-inside">
                            @foreach ($errors->all() as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    @endif

                    <div>
                        <x-input-label for="unit_id" value="Unit" />
                        <select id="unit_id" name="unit_id" required class="mt-1 block w-full rounded-xl border-gray-200 focus:border-rentra focus:ring-rentra text-sm">
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}" @selected(old('unit_id') == $unit->id)>{{ $unit->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="mulai" value="Mulai" />
                        <input type="datetime-local" id="mulai" name="mulai" value="{{ old('mulai') }}" required class="mt-1 block w-full rounded-xl border-gray-200 focus:border-rentra focus:ring-rentra text-sm">
                    </div>
                    <div>
                        <x-input-label for="selesai" value="Selesai" />
                        <input type="datetime-local" id="selesai" name="selesai" value="{{ old('selesai') }}" required class="mt-1 block w-full rounded-xl border-gray-200 focus:border-rentra focus:ring-rentra text-sm">
                    </div>
                    <div>
                        <x-input-label for="alasan" value="Alasan (opsional)" />
                        <x-text-input id="alasan" name="alasan" type="text" maxlength="120" class="mt-1 block w-full" :value="old('alasan')" placeholder="Servis rutin" />
                    </div>
                    <label class="flex items-center gap-2 text-xs text-gray-600">
                        <input type="checkbox" name="paksa" value="1" class="rounded border-gray-300 text-status-terlambat focus:ring-status-terlambat">
                        Tetap blokir walaupun bentrok dengan booking aktif
                    </label>

                    <x-primary-button class="w-full justify-center">Simpan blok</x-primary-button>
                </form>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl border border-teal-50 shadow-sm p-6">
                    <h3 class="font-semibold text-rentra-ink">Blok aktif ({{ $blocks->count() }})</h3>
                    @if ($blocks->isEmpty())
                        <p class="mt-2 text-sm text-gray-500">Belum ada blok jadwal.</p>
                    @else
                        <ul class="mt-4 divide-y divide-gray-100">
                            @foreach ($blocks as [$unit, $block])
                                <li class="py-3 flex items-center justify-between gap-3">
                                    <div class="text-sm">
                                        <p class="font-semibold text-rentra-ink">{{ $unit->nama }}</p>
                                        <p class="text-gray-500">{{ $block->mulai->translatedFormat('d M Y H:i') }} - {{ $block->selesai->translatedFormat('d M Y H:i') }} WIB</p>
                                        @if ($block->alasan)
                                            <p class="text-xs text-gray-400 mt-0.5">{{ $block->alasan }}</p>
                                        @endif
                                    </div>
                                    <form method="POST" action="{{ route('blok.destroy', $block) }}" onsubmit="return confirm('Hapus blok ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center rounded-lg border border-red-200 text-status-terlambat px-3 py-1.5 text-xs font-semibold hover:bg-red-50">Hapus</button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
