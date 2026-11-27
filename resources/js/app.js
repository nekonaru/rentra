import './bootstrap';
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

/* ============================================================
   Dark mode: simpan preferensi di localStorage, default sistem.
   ============================================================ */
(function () {
    const kunci = 'rentra-tema';
    const tersimpan = localStorage.getItem(kunci);
    const gelap = tersimpan
        ? tersimpan === 'gelap'
        : window.matchMedia('(prefers-color-scheme: dark)').matches;

    document.documentElement.classList.toggle('dark', gelap);

    window.gantiTema = function () {
        const sekarang = document.documentElement.classList.toggle('dark');
        localStorage.setItem(kunci, sekarang ? 'gelap' : 'terang');
        window.dispatchEvent(new CustomEvent('tema-berubah', { detail: { gelap: sekarang } }));
    };
})();

/* ============================================================
   Toast: dipicu lepas custom event 'toast'.
   ============================================================ */
(function () {
    const wadah = () => document.getElementById('wadah-toast');
    window.toast = function (pesan, jenis = 'sukses') {
        const el = wadah();
        if (!el) return;
        const warna = {
            sukses: 'bg-rentra text-white',
            gagal: 'bg-status-terlambat text-white',
            info: 'bg-status-verifikasi text-white',
        }[jenis] || 'bg-rentra text-white';

        const item = document.createElement('div');
        item.className = `rounded-tombol px-4 py-3 text-sm font-semibold shadow-kartu animate-geser-masuk ${warna}`;
        item.setAttribute('role', 'status');
        item.textContent = pesan;
        el.appendChild(item);
        setTimeout(() => item.remove(), 4000);
    };

    // Toast otomatis dari pesan session Blade.
    document.querySelectorAll('[data-toast]').forEach((node) => {
        window.toast(node.dataset.toast, node.dataset.jenis || 'sukses');
        node.remove();
    });
})();

/* ============================================================
   Cetak lewat atribut data-print.
   ============================================================ */
document.addEventListener('click', (event) => {
    if (event.target.closest('[data-print]')) {
        window.print();
    }
});

/* ============================================================
   PWA: daftarkan service worker.
   ============================================================ */
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    });
}
