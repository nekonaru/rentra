@props(['item' => []])

<ol class="relative border-l-2 border-[var(--warna-tepi)] ms-3 space-y-6">
    @foreach ($item as $row)
        <li class="ps-6">
            <span class="absolute -start-[7px] mt-1 h-3 w-3 rounded-full border-2 border-[var(--warna-permukaan)]" style="background-color: {{ $row['warna'] ?? '#0F766E' }}"></span>
            <div class="font-semibold text-rentra-ink text-sm">{{ $row['judul'] }}</div>
            @if (isset($row['keterangan']))
                <div class="text-xs text-[var(--warna-teks-redup)] mt-0.5">{{ $row['keterangan'] }}</div>
            @endif
            @if (isset($row['waktu']))
                <div class="text-[11px] text-[var(--warna-teks-redup)] mt-0.5">{{ $row['waktu'] }}</div>
            @endif
        </li>
    @endforeach
</ol>
