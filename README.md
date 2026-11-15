<div align="center">
  <img src="assets/rentra-mark.png" alt="Logo Rentra" width="120" />
  <h1>Rentra: Booking Online untuk Rental Kecil</h1>
  <p><b>Ubah "kak, masih ready?" menjadi satu link publik dengan ketersediaan langsung,<br/>
  kalkulasi harga otomatis, dan verifikasi bukti DP.</b></p>

  <a href="https://github.com/nekonaru/rentra/releases"><img src="https://img.shields.io/github/v/release/nekonaru/rentra?style=flat-square&color=blue" alt="Release" /></a>
  <a href="https://github.com/nekonaru/rentra/actions/workflows/ci.yml"><img src="https://github.com/nekonaru/rentra/actions/workflows/ci.yml/badge.svg" alt="CI" /></a>
  <img src="https://img.shields.io/badge/PHP-8.2-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP" />
  <img src="https://img.shields.io/badge/Platform-Web-0F766E?style=flat-square" alt="Platform" />
  <img src="https://img.shields.io/badge/License-MIT-green?style=flat-square" alt="License" />
  <img src="https://img.shields.io/badge/Status-Active-brightgreen?style=flat-square" alt="Status" />
</div>

<br />

**Bahasa Indonesia**

> [!NOTE]
> Seluruh kode, dokumen, dan antarmuka Rentra memakai Bahasa Indonesia.

---

## Daftar Isi

- [Tentang](#tentang)
- [Fitur](#fitur)
- [Kebutuhan](#kebutuhan)
- [Instalasi](#instalasi)
- [Produksi](#produksi)
- [Penggunaan](#penggunaan)
- [Konfigurasi](#konfigurasi)
- [Struktur Proyek](#struktur-proyek)
- [Pengembangan](#pengembangan)
- [Pengujian](#pengujian)
- [Keamanan](#keamanan)
- [Integrasi Berkelanjutan](#integrasi-berkelanjutan)
- [FAQ](#faq)
- [Penulis](#penulis)
- [Disclaimer](#disclaimer)
- [Lisensi](#lisensi)

---

## Tentang

**Rentra** memberi usaha rental kecil dan menengah (kamera, lensa, dan peralatan lain) satu link publik berisi ketersediaan unit, kalkulator harga langsung, dan alur pemesanan dengan upload bukti DP. Pemilik punya dashboard untuk mengelola unit, pemesanan, dan verifikasi pembayaran, menggantikan chat "masih ready?" setiap hari.

> _"Satu link, tidak ada lagi chat 'masih ready?'."_

---

## Fitur

### Link publik dan pemesanan

| Fitur | Deskripsi |
|---------|-------------|
| **Halaman unit publik** | Satu link per rental berisi unit aktif, foto, dan tarif |
| **Kalender ketersediaan** | Kalender bulanan per unit: tersedia, sebagian terisi, penuh |
| **Kalkulator harga langsung** | Rincian harga dengan hari penuh, sisa jam mentok di tarif harian, toleransi menit, dan minimal sewa |
| **Tahan dengan hitung mundur** | Unit ditahan selama durasi yang bisa diatur (default 60 menit) setelah pemesanan |
| **Upload bukti DP** | Penyewa upload bukti JPG, PNG, atau PDF (maks 5 MB), disimpan secara privat |

### Dashboard pemilik

| Fitur | Deskripsi |
|---------|-------------|
| **Onboarding dalam hitungan menit** | Wizard terpandu: profil rental, lalu unit dan tarif dalam satu alur |
| **CRUD unit dan tarif** | Nama, kategori, foto, aktif/nonaktif, tarif per hari dan per jam |
| **Antrean verifikasi DP** | Terima atau tolak bukti dengan alasan; pemesanan yang ditolak dapat hold baru |
| **Blok unit** | Blokir rentang tanggal dengan alasan; bentrok dengan pemesanan aktif minta konfirmasi |
| **Siklus pemesanan** | Serah terima, tandai kembali, atau batalkan; pemesanan terlambat otomatis ditandai setelah lewat jam selesai |
| **Pengingat terlambat** | Satu tap membuka WhatsApp dengan template pesan terisi |
| **Ekspor CSV** | Unduh seluruh pemesanan ke CSV (UTF-8, pemisah titik koma) untuk rekap |
| **Ekspor bukti ZIP** | Unduh semua bukti DP pemilik sekaligus dari antrean verifikasi |
| **Kalender lintas unit** | Satu kalender penuh untuk semua pemesanan, klik hari untuk detail |
| **Laporan** | Omzet 6 bulan, unit terlaris, okupasi 30 hari, rata-rata nilai sewa, DP terkumpul |
| **Email bukti DP** | Pemilik menerima email otomatis saat penyewa mengupload bukti (default tercatat di log dev) |
| **Panel admin** | Operator platform mengelola tenant: lihat statistik, tangguhkan/aktifkan rental, buat tenant baru, tanpa data pribadi penyewa |
| **Anti bentrok** | Pemesanan berjalan dalam transaksi dengan row lock, dua penyewa tidak bisa mengambil slot yang sama |

### Platform

| Fitur | Deskripsi |
|---------|-------------|
| **Multi-tenant** | Satu database; setiap pemilik hanya melihat datanya sendiri, dijaga policy dan test |
| **UI Bahasa Indonesia** | Dibangun untuk usaha kecil Indonesia, mobile first, nyaman di layar 360px |
| **Setup ramah offline** | Tanpa layanan eksternal; jalan di XAMPP (Apache, MySQL/MariaDB) |

---

## Kebutuhan

| Dependensi | Versi Minimum | Catatan |
|------------|---------------|--------|
| **PHP** | 8.2 | Dengan `pdo_mysql`, `pdo_sqlite`, `gd`, `zip`, `mbstring`, `curl`, `fileinfo`, `openssl` |
| **Composer** | 2.x | Manajer dependensi PHP |
| **Node.js** | 20.19 | Untuk build Vite dan Tailwind |
| **MySQL atau MariaDB** | 10.4 / 8.0 | Seperti bawaan XAMPP; utf8mb4 |
| **Git** | terbaru | Untuk clone repository |

> [!TIP]
> Pakai XAMPP? PHP dan MySQL/MariaDB sudah termasuk. Kamu hanya butuh Composer dan Node.js.

### Belum pernah pakai terminal? Mulai di sini

<details>
<summary><b>Cara membuka terminal</b></summary>

Di Windows, tekan `Win + R`, ketik `cmd`, lalu tekan Enter. Atau buka Start menu, ketik `PowerShell`, lalu tekan Enter. Jalankan semua perintah di bawah di jendela itu, dari folder tempat kamu menaruh proyek.

</details>

---

## Instalasi

Rentra adalah web app. Tidak ada installer; kamu menjalankannya secara lokal atau di server sendiri.

### 1. Ambil kode

<details>
<summary><b>Saya sudah punya Git</b></summary>

```bash
git clone https://github.com/nekonaru/rentra.git
cd rentra
```

</details>

<details>
<summary><b>Saya belum punya Git</b></summary>

Download ZIP dari https://github.com/nekonaru/rentra/archive/refs/heads/main.zip, ekstrak, lalu buka terminal di dalam folder hasil ekstrak (bukan folder induknya).

</details>

### 2. Jalankan Apache dan MySQL di XAMPP

Buka XAMPP Control Panel, klik **Start** di sebelah **Apache** dan **MySQL**.

### 3. Buat database

Buka http://localhost/phpmyadmin, klik **New**, beri nama `rentra`, pilih `utf8mb4_unicode_ci`, lalu klik **Create**. Atau jalankan perintah ini di konsol MySQL:

```sql
CREATE DATABASE rentra CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4. Install dependensi dan siapkan aplikasi

```bash
composer install
npm install
cp .env.example .env   # di Windows: copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run build
```

> [!IMPORTANT]
> `php artisan migrate --seed` harus selesai tanpa error. Perintah ini membuat akun demo `demo@rentra.test` (pemilik) dan `admin@rentra.test` (admin platform), keduanya password `password`, plus rental demo di `http://localhost:8000/demo-kamera`.

### 5. Jalankan aplikasi

```bash
php artisan serve
```

Lalu buka http://localhost:8000. Login dengan `demo@rentra.test` / `password`, atau daftar akun baru.

### 6. Jaga hold dan status terlambat tetap akurat

Aplikasi butuh scheduler tiap menit untuk mengkedaluwarsakan pemesanan yang hold-nya habis dan menandai pengembalian terlambat. Untuk pengembangan:

```bash
php artisan schedule:work
```

Untuk demo lokal atau setup mirip produksi di Windows, pakai Task Scheduler:

1. Buka **Task Scheduler** dan buat task yang berjalan tiap 1 menit.
2. Isi action dengan `C:\xampp\php\php.exe` dan argument `C:\path\to\rentra\artisan schedule:run`.

Tanpa scheduler aplikasi tetap jalan: pengecekan ketersediaan juga mengabaikan hold yang waktunya sudah habis.

---

## Produksi

Batasan bahwa aplikasi ini aman dijalankan hanya ketika `.env` produksi diatur dengan benar. Berikut checklist singkat:

```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-kamu.com
SESSION_SECURE_COOKIE=true
```

Lalu:

```bash
composer install --no-dev -o
npm ci && npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan key:generate     # sebenarnya hanya sekali
php artisan migrate --force
```

Disarankan: aktifkan backup rutin (lihat `RESTORE.md`) dan saat email verifikasi diperlukan, ganti `MAIL_MAILER=log` dengan `MAIL_MAILER=smtp` dan atur kredensial SMTP provider kamu. Tanpa SMTP, pemilik tetap bisa masuk tetapi tidak menerima email verifikasi. Rentra tidak menggagalkan registrasi pemilik.

Perhatikan bahwa `php artisan config:cache` mematikan fitur hot reload config. Saat butuh ubah `.env`, jalankan `php artisan config:clear` lalu `config:cache` lagi.

---

## Penggunaan

### Peluncuran pertama

1. Jalankan `php artisan migrate --seed` (lihat Instalasi).
2. Buka http://localhost:8000 dan daftar, atau login dengan `demo@rentra.test` / `password`.
3. Selesaikan wizard onboarding (nama rental, slug link publik, nomor WhatsApp).
4. Tambahkan unit pertama beserta tarifnya.
5. Bagikan `http://localhost:8000/{slug-kamu}` ke pelanggan.

> [!NOTE]
> Halaman publik hanya menampilkan status slot, tidak pernah menampilkan nama atau kontak penyewa lain.

### Pemakaian harian

- **Bagikan link.** Pelanggan pilih unit, lihat kalender, hitung harga, dan pesan.
- **Cek status.** Di beranda, tamu memasukkan kode pemesanan 10 karakter untuk langsung melihat status; tanpa perlu login.
- **Verifikasi bukti.** Buka **Verifikasi**, periksa bukti DP, lalu **Terima** atau **Tolak** dengan alasan.
- **Cek kalender.** Buka **Kalender** untuk melihat semua pemesanan lintas unit dalam satu tampilan bulanan.
- **Lihat performa.** Buka **Laporan** untuk omzet 6 bulan, unit terlaris, okupasi, dan DP terkumpul.
- **Kelola platform.** Di **Panel admin** (khusus operator Rentra), pantau semua tenant, cari berdasarkan nama/slug/WA/email, buat tenant baru, tangguhkan rental yang melanggar, dan aktifkan kembali.
- **Serah terima.** Di halaman **Pemesanan**, tandai **Serah terima** saat unit keluar dan **Unit kembali** saat unit masuk.
- **Ekspor rekap.** Di halaman **Pemesanan**, klik **Ekspor CSV** untuk mengunduh seluruh pemesanan, atau **Ekspor ZIP** di antrean verifikasi untuk mengunduh semua bukti DP sekaligus.
- **Blokir tanggal.** Pakai **Blok** untuk servis atau pemakaian pribadi; pemesanan yang bentrok minta konfirmasi.
- **Ubah pengaturan.** Buka **Pengaturan rental** di menu profil untuk mengubah nama, nomor WA, rekening, durasi tahan, dan persen DP.
- **Ingatkan keterlambatan.** Pemesanan terlambat ditandai otomatis; **Kirim pengingat WA** membuka WhatsApp dengan pesan terisi.

---

## Konfigurasi

| Variabel | Default | Fungsi |
|----------|---------|--------|
| `APP_TIMEZONE` | `Asia/Jakarta` | Semua tanggal disimpan dan ditampilkan WIB |
| `APP_LOCALE` | `id` | Bahasa UI (Bahasa Indonesia) |
| `DB_DATABASE` | `rentra` | Nama database MySQL |
| `QUEUE_CONNECTION` | `database` | Queue database, tanpa Redis |
| `FILESYSTEM_DISK` | `local` | Disk privat untuk bukti DP |

Opsional: saat penyewa mengupload bukti DP, pemilik otomatis menerima email. Di mode default (`MAIL_MAILER=log`) isi email tercatat di `storage/logs`. Untuk produksi, isi `MAIL_MAILER=smtp` beserta `MAIL_HOST`, `MAIL_USERNAME`, dan `MAIL_PASSWORD` sesuai provider kamu.

---

## Struktur Proyek

```
rentra/
├── app/
│   ├── Console/Commands/    # SapuPemesanan: perintah pemesanan:sapu (entry point scheduler)
│   ├── Enums/               # Enum StatusPemesanan dengan label dan warna
│   ├── Http/Controllers/    # Controller web, publik, dan dashboard
│   ├── Http/Middleware/     # PastikanRental (penjaga onboarding)
│   ├── Http/Requests/       # Validasi Form Request
│   ├── Models/              # Rental, Unit, AturanTarif, Pemesanan, BlokUnit
│   ├── Policies/            # UnitPolicy, PemesananPolicy (isolasi tenant)
│   └── Services/            # KalkulatorHarga, LayananKetersediaan, LayananPemesanan, PengelolaUnit
├── assets/                  # Master logo dan gambar README
├── public/                  # favicon.svg, apple-touch-icon.png, symlink storage
├── resources/views/         # View Blade (publik, dashboard, auth)
├── routes/                  # web.php, auth.php, console.php (jadwal: tiap menit)
├── storage/app/private/     # Bukti DP (tidak pernah publik)
├── tests/                   # Test feature dan unit PHPUnit
├── .env.example             # Contoh file environment
├── AGENTS.md                # Variabel proyek dan catatan kontributor
└── README.md                # Readme Bahasa Indonesia
```

---

## Pengembangan

### Melakukan perubahan

1. Buat branch dan edit kode.
2. Simpan logika bisnis di `app/Services`, bukan di controller atau Blade.
3. Jalankan `vendor/bin/pint` untuk format, lalu `php artisan test` dan pastikan semua test lulus.
4. Commit dengan format `type: short description` (`feat:`, `fix:`, `docs:`, `chore:`, ...).

> [!WARNING]
> Jangan mengedit `public/build` manual. Folder itu dihasilkan `npm run build` dan diabaikan git.

### Menambah status pemesanan

1. Tambahkan case ke `app/Enums/StatusPemesanan.php` beserta label dan warnanya.
2. Update `menahanSlot()` kalau status itu menahan slot.
3. Update `pemesanan:sapu` di `app/Console/Commands/SapuPemesanan.php` kalau status bisa berubah otomatis.

---

## Pengujian

```bash
php artisan test                  # seluruh 93 test (feature dan unit, SQLite in-memory)
php artisan test --filter=KeamananTest   # 9 test keamanan khusus
vendor/bin/pint --test            # cek gaya kode
npm run build                     # build frontend tanpa error
```

| Cek | Requirement |
|-------|-------------|
| Test suite | Semua test lulus (saat ini 93 test) |
| Pint | Nol masalah gaya |
| Build frontend | `npm run build` selesai tanpa error |
| Keamanan | `composer audit` tidak melaporkan advisory |

---

## Keamanan

- Autentikasi berbasis sesi (Laravel Breeze) dan semua perubahan status memakai CSRF token.
- Bukti DP divalidasi tipe MIME dan ukurannya, disimpan di disk privat, dan diakses hanya lewat route berotorisasi.
- Endpoint pemesanan dan upload publik dibatasi rate limit.
- Semua query dashboard dibatasi ke rental milik pemilik; akses lintas tenant tercakup test.
- Dependensi diaudit dengan `composer audit`.

> [!NOTE]
> Laporkan kerentanan secara privat lewat proses yang dijelaskan di [`SECURITY.md`](SECURITY.md). Jangan buka issue publik untuk laporan keamanan.

---

## Integrasi Berkelanjutan

| Workflow | Fungsi |
|----------|--------|
| `.github/workflows/ci.yml` | Install dependensi, build aset, jalankan seluruh test suite pada setiap push dan PR ke `main` |

CI berjalan di `main` dan tag `v*`. Test memakai setup SQLite in-memory yang sama dengan lokal, jadi CI jadi sumber kebenaran kalau tooling lokal tidak tersedia.

---

## FAQ

<details>
<summary><b>Apakah saya butuh server dan database?</b></summary>

Tidak ada layanan berbayar. XAMPP memberi Apache dan MySQL/MariaDB secara lokal. Untuk kebutuhan kecil, VPS murah atau shared hosting dengan PHP 8.2 sudah cukup.

</details>

<details>
<summary><b>Bagaimana cara mengubah durasi tahan pemesanan?</b></summary>

Buka menu profil, klik **Pengaturan rental**, ubah **Durasi tahan (menit)**, lalu simpan. Perubahan berlaku untuk pemesanan berikutnya.

</details>

<details>
<summary><b>Apakah Rentra mengirim pesan WhatsApp otomatis?</b></summary>

Tidak. Rentra membuka link `wa.me` dengan pesan terisi, kamu yang menekan kirim. Tidak ada API key WhatsApp yang dibutuhkan.

</details>

<details>
<summary><b>Kemana bukti DP disimpan?</b></summary>

Disimpan di `storage/app/private/bukti` dan hanya bisa diakses lewat route terotorisasi dan terautentikasi. Tidak pernah ada di web root publik.

</details>

<details>
<summary><b>Apakah akun demo benar-benar jalan?</b></summary>

Ya. `php artisan migrate --seed` membuat `demo@rentra.test` dengan password `password` dan rental demo di `/demo-kamera` dengan tiga unit.

</details>

<details>
<summary><b>Bisakah Rentra dijadikan aplikasi mobile?</b></summary>

Tidak. Rentra adalah web app mobile first; tetap web, atau bungkus sendiri dengan webview kalau butuh.

</details>

---

## Penulis

<div align="center">

| [![Nicolas Dwi Dharma](https://github.com/nekonaru.png?size=100)](https://github.com/nekonaru) |
|:---:|
| **Nicolas Dwi Dharma** |
| [github.com/nekonaru](https://github.com/nekonaru) |

</div>

---

## Disclaimer

Rentra adalah alat bantu manajemen pemesanan. Perjanjian sewa, cek identitas, dan sengketa pembayaran tetap menjadi urusan pemilik dan penyewa. Pesan WhatsApp terbuka di akun kamu sendiri; tidak ada pesan yang terkirim tanpa kamu tekan kirim.

---

## Lisensi

Disebarkan di bawah **MIT License**. Lihat [`LICENSE`](LICENSE) untuk informasi lebih lanjut.




