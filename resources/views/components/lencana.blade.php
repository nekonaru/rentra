@props(['warna' => '#0F766E', 'teks' => null, 'kelas' => ''])

<span class="badge {{ $kelas }}" style="background-color: {{ $warna }}20; color: {{ $warna }};">
    @if ($teks)
        {{ $teks }}
    @else
        {{ $slot }}
    @endif
</span>
