# Prosedur Restore Rentra

Panduan memulihkan database dan file bukti DP ke environment baru (mis. setelah laptop rusak atau migrasi server).

## Yang perlu dibackup

1. **Database MySQL `rentra`** (isi: akun pemilik, unit, aturan tarif, pemesanan, riwayat).
2. **Folder `storage/app/private`** (bukti DP penyewa).
3. **File `.env`** (semua konfigurasi dan secret), disimpan aman, jangan di-Git.

## Backup rutin

```bash
# Database (ganti password dan path sesuai)
C:\xampp\mysql\bin\mysqldump.exe -u root rentra > rentra-backup.sql

# File bukti
robocopy storage\app\private rentra-bukti-backup /E
```

Jadwalkan keduanya lewat Task Scheduler Windows, misalnya tiap hari 02:00. Simpan hasilnya di media lain (Google Drive pribadi, hard disk eksternal), bukan hanya di laptop yang sama.

## Restore ke environment baru

1. Install XAMPP + PHP 8.2 + Composer + Node.js.
2. Clone repo, `composer install`, `npm install`, `npm run build`.
3. Copy `.env` dari backup aman.
4. Buat database kosong `rentra` (utf8mb4).
5. Restore database: `C:\xampp\mysql\bin\mysql.exe -u root rentra < rentra-backup.sql`
6. Restore folder bukti: salin isi backup ke `storage/app/private`.
7. `php artisan storage:link`, lalu `php artisan serve`.
8. Login dengan akun pemilik yang di-backup, pastikan daftar unit dan pemesanan tampil, dan satu bukti DP bisa dibuka dari antrean verifikasi.

## Uji restore berkala

Sebulan sekali, lakukan restore ke folder/sekali jalan di mesin lain (atau database sementara `rentra_uji`) tanpa menyentuh data aktif. Kalau langkah 8 lolos, backup valid.

## Kalau ada yang terhapus

Jangan menjalankan `migrate:fresh` atau `db:wipe` di environment produksi. Restore dari backup terakhir sebagai gantinya.
