@props(['judul' => 'Belum ada data', 'pesan' => null, 'kelas' => ''])

<div class="kartu p-10 text-center {{ $kelas }}">
    <svg viewBox="0 0 24 24" class="mx-auto mb-3 h-12 w-12 text-rentra/30" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M21 8l-9-5-9 5v8l9 5 9-5V8Z"/><path d="M3 8l9 5 9-5M12 13v8"/>
    </svg>
    <p class="font-semibold text-rentra-ink">{{ $judul }}</p>
    @if ($pesan)
        <p class="mt-1 text-sm text-[var(--warna-teks-redup)]">{{ $pesan }}</p>
    @endif
    @if (isset($aksi))
        <div class="mt-4">{{ $aksi }}</div>
    @endif
</div>
