@props(['label', 'nilai', 'warna' => '#0F766E', 'ikon' => null, 'kelas' => ''])

<div class="kartu p-4 {{ $kelas }}">
    <div class="flex items-start justify-between gap-2">
        <div class="text-xs text-[var(--warna-teks-redup)] uppercase tracking-wide">{{ $label }}</div>
        @if ($ikon)
            <span class="text-lg leading-none" style="color: {{ $warna }}">{!! $ikon !!}</span>
        @endif
    </div>
    <div class="mt-1 text-2xl font-bold text-rentra-ink">{{ $nilai }}</div>
</div>
