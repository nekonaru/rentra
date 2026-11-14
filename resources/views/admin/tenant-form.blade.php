<x-layouts.admin>
    <h1 class="text-xl font-bold text-white">Tambah Tenant Baru</h1>
    <p class="mt-1 text-sm text-white/60">Operator rental (Rentra) membuat akun untuk pemilik baru. Password dibuat otomatis.</p>

    @if ($errors->any())
        <div class="mt-4 rounded-xl bg-red-900/50 border border-red-500 px-4 py-3">
            <ul class="text-sm text-red-200 list-disc list-inside space-y-1">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.tenant.simpan') }}" class="mt-6 max-w-2xl bg-white/5 border border-white/10 rounded-xl p-6 space-y-4"
          x-data="{ nama: @js(old('nama', '')), slug: @js(old('slug', '')), slugManual: false }">
        @csrf

        <div>
            <label class="block text-xs font-semibold text-white/70">Nama rental</label>
            <input type="text" name="nama" required maxlength="120" value="{{ old('nama') }}" required x-model="nama"
                   @input="if (! slugManual) slug = nama.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/[^a-z0-9\s-]/g, '').trim().replace(/[\s_]+/g, '-').replace(/-+/g, '-')"
                   class="mt-1 block w-full rounded-xl border-white/10 bg-white/5 text-white placeholder-white/30 focus:border-teal-400 focus:ring-teal-400 text-sm"
                   placeholder="Contoh: Kamera Kita">
        </div>
        <div>
            <label class="block text-xs font-semibold text-white/70">Link publik (slug)</label>
            <input type="text" name="slug" required maxlength="60" pattern="[a-z0-9\-]+" x-model="slug" x-on:input="slugManual = true"
                   class="mt-1 block w-full rounded-xl border-white/10 bg-white/5 text-white placeholder-white/30 focus:border-teal-400 focus:ring-teal-400 text-sm"
                   placeholder="kamera-kita" autocomplete="off">
            <p class="mt-1 text-[11px] text-white/40">Slug ini jadi link publik: rentra.test/<span x-text="slug || 'slug'"></span></p>
        </div>
        <div>
            <label class="block text-xs font-semibold text-white/70">Nomor WhatsApp pemilik (format 62)</label>
            <input type="text" name="no_wa" required inputmode="numeric" value="{{ old('no_wa') }}"
                   class="mt-1 block w-full rounded-xl border-white/10 bg-white/5 text-white placeholder-white/30 focus:border-teal-400 focus:ring-teal-400 text-sm"
                   placeholder="6281234567890">
        </div>
        <div>
            <label class="block text-xs font-semibold text-white/70">Nama pemilik</label>
            <input type="text" name="nama_pemilik" required maxlength="120" value="{{ old('nama_pemilik') }}"
                   class="mt-1 block w-full rounded-xl border-white/10 bg-white/5 text-white placeholder-white/30 focus:border-teal-400 focus:ring-teal-400 text-sm"
                   placeholder="Nama lengkap pemilik">
        </div>
        <div>
            <label class="block text-xs font-semibold text-white/70">Email pemilik</label>
            <input type="email" name="email_pemilik" required maxlength="191" value="{{ old('email_pemilik') }}"
                   class="mt-1 block w-full rounded-xl border-white/10 bg-white/5 text-white placeholder-white/30 focus:border-teal-400 focus:ring-teal-400 text-sm"
                   placeholder="pemilik@email.com">
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="inline-flex items-center rounded-xl bg-teal-500 px-5 py-3 text-sm font-bold text-white hover:bg-teal-600 transition">Buat tenant</button>
            <a href="{{ route('admin.tenant.index') }}" class="text-sm text-white/60 hover:text-white">Batal</a>
        </div>
    </form>
</x-layouts.admin>
