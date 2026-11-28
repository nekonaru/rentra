<x-layouts.admin>
    <h1 class="text-xl font-bold text-white">Pengumuman Platform</h1>
    <p class="mt-1 text-sm text-white/60">Pesan singkat yang tampil di dashboard semua pemilik.</p>

    @if (session('status'))
        <div class="mt-4 rounded-xl bg-teal-900/50 border border-teal-700 px-4 py-3 text-sm text-teal-200">{{ session('status') }}</div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2 mt-6">
        <form method="POST" action="{{ route('admin.pengumuman.store') }}" class="bg-white/5 border border-white/10 rounded-xl p-5 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-white/70">Judul</label>
                <input type="text" name="judul" required maxlength="160" class="mt-1 block w-full rounded-xl bg-white/5 border-white/10 text-white text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-white/70">Isi</label>
                <textarea name="isi" rows="4" required maxlength="2000" class="mt-1 block w-full rounded-xl bg-white/5 border-white/10 text-white text-sm"></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-white/70">Tampil sampai (opsional)</label>
                <input type="datetime-local" name="ditampilkan_sampai" class="mt-1 block w-full rounded-xl bg-white/5 border-white/10 text-white text-sm">
            </div>
            <button type="submit" class="rounded-xl bg-teal-500 px-5 py-2.5 text-sm font-bold text-white hover:bg-teal-600">Terbitkan</button>
        </form>

        <div class="bg-white/5 border border-white/10 rounded-xl p-5">
            <h2 class="font-semibold text-white mb-3">Daftar pengumuman</h2>
            @if ($pengumuman->isEmpty())
                <p class="text-sm text-white/50">Belum ada pengumuman.</p>
            @else
                <div class="space-y-3">
                    @foreach ($pengumuman as $item)
                        <div class="border-b border-white/10 pb-3 last:border-0">
                            <div class="flex items-center justify-between gap-2">
                                <div class="font-semibold text-white text-sm">{{ $item->judul }}</div>
                                <form method="POST" action="{{ route('admin.pengumuman.destroy', $item) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-300">Hapus</button>
                                </form>
                            </div>
                            <p class="text-xs text-white/50 mt-1">{{ $item->isi }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
