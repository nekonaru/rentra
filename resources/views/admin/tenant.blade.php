<x-layouts.admin>
    <div class="flex items-center justify-between gap-3 flex-wrap">
        <h1 class="text-xl font-bold text-white">Tenant</h1>
        <span class="text-xs text-white/50">{{ $tenant->total() }} rental terdaftar</span>
    </div>

    <div class="mt-4 overflow-x-auto rounded-xl border border-white/10">
        <table class="w-full text-sm min-w-[720px]">
            <thead class="bg-white/5 text-white/60 text-left">
                <tr>
                    <th class="px-4 py-3 font-semibold">Rental</th>
                    <th class="px-4 py-3 font-semibold">Pemilik</th>
                    <th class="px-4 py-3 font-semibold text-right">Unit</th>
                    <th class="px-4 py-3 font-semibold text-right">Pemesanan</th>
                    <th class="px-4 py-3 font-semibold text-right">Omzet</th>
                    <th class="px-4 py-3 font-semibold">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @foreach ($tenant as $rental)
                    <tr class="hover:bg-white/5">
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.tenant.show', $rental) }}" class="font-semibold text-teal-300 hover:underline">{{ $rental->nama }}</a>
                            <span class="block text-[11px] text-white/40">/{{ $rental->slug }}</span>
                        </td>
                        <td class="px-4 py-3 text-white/70">{{ $rental->user->name }}<span class="block text-[11px] text-white/40">{{ $rental->user->email }}</span></td>
                        <td class="px-4 py-3 text-right text-white/70">{{ $rental->unit_count }}</td>
                        <td class="px-4 py-3 text-right text-white/70">{{ $rental->pemesanan_count }}</td>
                        <td class="px-4 py-3 text-right text-white/70">Rp {{ number_format((float) ($rental->pemesanan_sum_total ?? 0), 0, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold uppercase {{ $rental->aktif ? 'bg-green-500/20 text-green-300' : 'bg-red-500/20 text-red-300' }}">{{ $rental->aktif ? 'Aktif' : 'Ditangguhkan' }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $tenant->links() }}</div>
</x-layouts.admin>
