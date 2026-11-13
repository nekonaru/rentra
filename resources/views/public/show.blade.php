<x-layouts.public :rental="$rental" :title="$rental->nama">
    <div class="text-center pt-2 pb-6">
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-rentra-ink">{{ $rental->nama }}</h1>
        <p class="mt-1 text-sm text-gray-600">Pilih unit, lihat jadwal, dan booking langsung.</p>
    </div>

    @if ($rental->units->isEmpty())
        <div class="bg-white rounded-xl border border-teal-50 shadow-sm p-10 text-center">
            <p class="font-semibold text-rentra-ink">Belum ada unit tersedia.</p>
            <p class="mt-1 text-sm text-gray-500">Hubungi pemilik lewat WhatsApp untuk info lebih lanjut.</p>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach ($rental->units as $unit)
                <a href="{{ route('public.unit', [$rental->slug, $unit->id]) }}" class="group bg-white rounded-xl border border-teal-50 shadow-sm overflow-hidden hover:shadow-md transition">
                    @if ($unit->foto_path)
                        <img src="{{ Storage::disk('public')->url($unit->foto_path) }}" alt="Foto {{ $unit->nama }}" class="h-44 w-full object-cover">
                    @else
                        <div class="h-44 w-full bg-teal-50 flex items-center justify-center text-rentra/40">
                            <svg viewBox="0 0 24 24" class="h-14 w-14" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h3l2-2h6l2 2h3a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1Z"/><circle cx="12" cy="13" r="4"/></svg>
                        </div>
                    @endif
                    <div class="p-4">
                        <h2 class="font-semibold text-rentra-ink leading-snug group-hover:text-rentra transition">{{ $unit->nama }}</h2>
                        @if ($unit->kategori)
                            <p class="text-xs text-gray-500 mt-0.5">{{ $unit->kategori }}</p>
                        @endif
                        <p class="mt-2 text-sm font-bold text-rentra-ink">
                            Rp {{ number_format((float) ($unit->priceRule->tarif_per_hari ?? 0), 0, ',', '.') }}<span class="font-normal text-gray-500">/hari</span>
                        </p>
                        <p class="mt-2 text-xs font-semibold text-rentra">Cek jadwal &amp; booking &rarr;</p>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</x-layouts.public>

