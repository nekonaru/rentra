<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-rentra-ink leading-tight">Riwayat Aktivitas</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6">
            @if ($aktivitas->isEmpty())
                <x-kosong judul="Belum ada aktivitas" pesan="Perubahan unit, booking, dan verifikasi akan tercatat di sini." />
            @else
                <div class="kartu divide-y divide-[var(--warna-tepi)]">
                    @foreach ($aktivitas as $item)
                        <div class="p-4 flex items-start justify-between gap-3">
                            <div>
                                <div class="font-semibold text-rentra-ink text-sm">{{ $item->aktivitas }}</div>
                                @if ($item->keterangan)
                                    <div class="text-xs text-[var(--warna-teks-redup)] mt-0.5">{{ $item->keterangan }}</div>
                                @endif
                            </div>
                            <div class="text-right shrink-0">
                                <div class="text-xs text-[var(--warna-teks-redup)]">{{ $item->created_at->diffForHumans() }}</div>
                                <div class="text-[10px] text-[var(--warna-teks-redup)]">{{ $item->user->name }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4">{{ $aktivitas->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
