@props(['judul' => null, 'kelas' => ''])

<div {{ $attributes->merge(['class' => "kartu p-5 sm:p-6 {$kelas}"]) }}>
    @if ($judul)
        <h3 class="font-semibold text-rentra-ink mb-4">{{ $judul }}</h3>
    @endif
    {{ $slot }}
</div>
