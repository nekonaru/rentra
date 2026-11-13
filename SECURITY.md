# Kebijakan Keamanan

## Melaporkan Kerentanan

Rentra menangani bukti pembayaran dan data pribadi (nama dan nomor ponsel penyewa). Kalau kamu menemukan masalah keamanan, jangan buka issue publik.

Laporkan secara privat ke nicolasdwidharma@gmail.com dengan subjek `[RENTA SECURITY]`. Sertakan:

- Deskripsi masalah dan dampaknya
- Langkah reproduksi, atau proof of concept
- Versi atau commit yang terdampak, kalau diketahui

Kamu akan menerima jawaban dalam 7 hari. Kami minta kamu menunggu perbaikan sebelum pengungkapan publik.

## Cakupan

- Autentikasi dan manajemen sesi
- Isolasi tenant antar akun rental
- Validasi upload file dan penyimpanan bukti DP
- Otorisasi pada route pemilik (dashboard, verifikasi DP, unduh bukti)
- Pembatasan rate pada endpoint publik pemesanan dan upload

## Di Luar Cakupan

- Social engineering terhadap pemilik atau penyewa
- Denial of service terhadap instance yang di-host sendiri
- Masalah pada layanan pihak ketiga (XAMPP, WhatsApp)
