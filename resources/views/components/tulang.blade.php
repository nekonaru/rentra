@props(['baris' => 3, 'kelas' => ''])

<div class="space-y-3 {{ $kelas }}" aria-busy="true" aria-live="polite">
    @for ($i = 0; $i < $baris; $i++)
        <div class="kartu p-4">
            <div class="skeleton h-4 w-1/3 rounded mb-3"></div>
            <div class="skeleton h-8 w-2/3 rounded"></div>
        </div>
    @endfor
</div>
