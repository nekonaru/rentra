@props(['langkah' => [], 'aktif' => 1])

<ol class="flex items-center gap-2" aria-label="Progres">
    @foreach ($langkah as $index => $item)
        @php
            $selesai = $index + 1 < $aktif;
            $sekarang = $index + 1 === $aktif;
        @endphp
        <li class="flex items-center gap-2 flex-1">
            <span @class([
                'flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold',
                'bg-rentra text-white' => $selesai || $sekarang,
                'bg-[var(--warna-tepi)] text-[var(--warna-teks-redup)]' => ! $selesai && ! $sekarang,
            ])>
                {{ $selesai ? '✓' : $index + 1 }}
            </span>
            <span @class([
                'text-xs font-semibold hidden sm:block',
                'text-rentra-ink' => $sekarang,
                'text-[var(--warna-teks-redup)]' => ! $sekarang,
            ])>{{ $item }}</span>
            @if (! $loop->last)
                <span class="h-px flex-1 bg-[var(--warna-tepi)]"></span>
            @endif
        </li>
    @endforeach
</ol>
