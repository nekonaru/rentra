<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-rentra-ink leading-tight">Ulasan</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-xl bg-rentra-50 border border-rentra-100 px-4 py-3 text-sm text-rentra">{{ session('status') }}</div>
            @endif

            @if ($ulasan->isEmpty())
                <x-kosong judul="Belum ada ulasan" pesan="Ulasan dari penyewa akan muncul di sini setelah mereka selesai menyewa." />
            @else
                <div class="space-y-4">
                    @foreach ($ulasan as $item)
                        <div class="kartu p-5">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <div class="font-semibold text-rentra-ink">{{ $item->unit->nama }}</div>
                                    <div class="text-amber-500 text-sm" aria-label="Rating {{ $item->rating }} dari 5">
                                        @for ($i = 0; $i < $item->rating; $i++)★@endfor
                                    </div>
                                    <p class="mt-2 text-sm text-[var(--warna-teks-redup)]">{{ $item->komentar }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="lencana" style="background-color: {{ $item->status === 'tampil' ? '#16A34A' : '#F59E0B' }}20; color: {{ $item->status === 'tampil' ? '#16A34A' : '#F59E0B' }};">
                                        {{ $item->status === 'tampil' ? 'Ditampilkan' : 'Menunggu' }}
                                    </span>
                                    @if ($item->status === 'menunggu')
                                        <form method="POST" action="{{ route('ulasan.moderasi', $item) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="tampil">
                                            <button type="submit" class="tombol tombol-utama text-xs px-3 py-1.5">Tampilkan</button>
                                        </form>
                                        <form method="POST" action="{{ route('ulasan.moderasi', $item) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="tolak">
                                            <button type="submit" class="tombol tombol-sekunder text-xs px-3 py-1.5">Tolak</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
