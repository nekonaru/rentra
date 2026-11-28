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
   Favorit & bandingkan unit (tersimpan di localStorage).
   ============================================================ */
(function () {
    const kunciFavorit = 'rentra-favorit';
    const kunciBanding = 'rentra-banding';

    const baca = (kunci) => {
        try { return JSON.parse(localStorage.getItem(kunci) || '[]'); } catch { return []; }
    };
    const tulis = (kunci, nilai) => localStorage.setItem(kunci, JSON.stringify(nilai));

    window.favorit = baca(kunciFavorit);
    window.banding = baca(kunciBanding);

    window.toogleFavorit = function (id) {
        const i = window.favorit.indexOf(id);
        if (i >= 0) { window.favorit.splice(i, 1); window.toast('Dihapus dari favorit', 'info'); }
        else { window.favorit.push(id); window.toast('Disimpan ke favorit', 'sukses'); }
        tulis(kunciFavorit, window.favorit);
        document.querySelectorAll(`[data-favorit="${id}"]`).forEach((el) => {
            el.setAttribute('aria-pressed', window.favorit.includes(id) ? 'true' : 'false');
            el.classList.toggle('text-amber-500', window.favorit.includes(id));
        });
    };

    window.toogleBanding = function (id, nama) {
        const ada = window.banding.find((b) => b.id === id);
        if (ada) { window.banding = window.banding.filter((b) => b.id !== id); window.toast('Dihapus dari bandingan', 'info'); }
        else {
            if (window.banding.length >= 3) { window.toast('Maksimal 3 unit dibandingkan', 'gagal'); return; }
            window.banding.push({ id, nama });
            window.toast(`${nama} ditambahkan ke bandingan`, 'sukses');
        }
        tulis(kunciBanding, window.banding);
    };
})();

/* ============================================================
   PWA: daftarkan service worker.
   ============================================================ */
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    });
}
