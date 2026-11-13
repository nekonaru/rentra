# Catatan Rilis

## v1.0.0 - Rilis awal

Rentra adalah SaaS multi-tenant untuk pemilik rental kecil: satu link publik berisi ketersediaan unit, kalkulator harga otomatis, dan pemesanan dengan verifikasi bukti DP, dikendalikan dari dashboard pribadi.

### Fitur baru

- **Link publik & halaman unit.** Setiap rental punya halaman `/{slug}` berisi unit aktif, foto, dan tarif.
- **Kalender ketersediaan.** Kalender bulanan per unit: tersedia, sebagian terisi, atau penuh.
- **Kalkulator harga langsung.** Rincian hari penuh, sisa jam mentok di tarif harian, toleransi menit, dan minimal sewa.
- **Anti bentrok.** Pemesanan berjalan dalam transaksi dengan row lock; dua penyewa tidak bisa mengambil slot yang sama.
- **Tahan dengan hitung mundur.** Unit ditahan selama durasi yang bisa diatur (default 60 menit), lalu otomatis kedaluwarsa jika belum membayar.
- **Upload bukti DP privat.** JPG, PNG, atau PDF maks 5 MB, disimpan di disk privat dan hanya bisa diakses pemilik.
- **Antrean verifikasi DP.** Pemilik menerima atau menolak bukti dengan alasan; booking yang ditolak dapat hold baru.
- **Blok unit.** Blokir rentang tanggal dengan alasan; bentrok dengan booking aktif minta konfirmasi.
- **Siklus pemesanan.** Serah terima, unit kembali, atau batalkan; pemesanan terlambat otomatis ditandai lewat scheduler (`pemesanan:sapu`).
- **Pemesanan manual.** Pemilik membuat pemesanan langsung (walk-in), status langsung terkonfirmasi.
- **Agenda hari ini & omzet bulanan.** Ringkasan singkat di dashboard.
- **Ekspor CSV.** Seluruh pemesanan ter-download (UTF-8, proteksi formula injection).
- **Pengaturan rental.** Nama, nomor WA, info rekening, durasi tahan, dan persen DP bisa diubah kapan pun.
- **Cetak struk.** Struk pemesanan yang rapi untuk dicetak dari browser.
- **Pengingat WA.** Satu tap membuka WhatsApp dengan pesan terisi untuk pelanggan terlambat.

### Peningkatan

- **Keamanan berlapis.** Security headers (CSP, HSTS produksi, nosniff, anti-framing), Origin check, rate limit di endpoint publik dan login, disk privat untuk bukti, tenant terisolasi per rental.
- **Full Bahasa Indonesia.** Kode, komentar, dokumen, dan UI seluruhnya Bahasa Indonesia.
- **PWA-ready asset.** Favicon SVG, apple-touch-icon, font Plus Jakarta Sans.

### Perbaikan bug

- **Keamanan dependensi.** Tidak ada `npm audit` / `composer audit` advisories; CSV injection di ekspor ditutup.
- **Trust proxy.** Hanya rentang privat yang dipercaya untuk `X-Forwarded-For`.
