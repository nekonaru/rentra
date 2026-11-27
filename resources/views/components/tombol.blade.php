@props([
    'jenis' => 'utama',
    'ukuran' => 'md',
    'kelas' => '',
])

@php
    $dasar = 'tombol';
    $warna = match ($jenis) {
        'utama' => 'tombol-utama',
        'aksen' => 'tombol-aksen',
        'sekunder' => 'tombol-sekunder',
        default => 'tombol-utama',
    };
    $saiz = $ukuran === 'kecil'
        ? 'text-xs px-3 py-1.5'
        : ($ukuran === 'besar' ? 'text-base px-6 py-3.5' : 'text-sm px-4 py-2.5');
@endphp

<button {{ $attributes->merge(['type' => 'submit', 'class' => "{$dasar} {$warna} {$saiz} {$kelas}"]) }}>
    {{ $slot }}
</button>
