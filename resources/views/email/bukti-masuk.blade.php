<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <title>Bukti DP masuk</title>
    </head>
    <body style="font-family: 'Plus Jakarta Sans', Arial, sans-serif; background: #f6faf9; color: #0b1f24; padding: 24px;">
        <div style="max-width: 520px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px;">
            <h1 style="font-size: 18px; color: #0f766e; margin: 0 0 16px;">Bukti DP masuk</h1>
            <p style="font-size: 14px; margin: 0 0 12px;">Pemilik rental {{ $pemesanan->rental->nama }}, ada bukti DP baru untuk diverifikasi:</p>
            <table style="width: 100%; font-size: 13px; border-collapse: collapse;">
                <tr><td style="padding: 6px 0; color: #64748b;">Kode</td><td style="padding: 6px 0;"><strong>{{ $pemesanan->kode }}</strong></td></tr>
                <tr><td style="padding: 6px 0; color: #64748b;">Unit</td><td style="padding: 6px 0;">{{ $pemesanan->unit->nama }}</td></tr>
                <tr><td style="padding: 6px 0; color: #64748b;">Penyewa</td><td style="padding: 6px 0;">{{ $pemesanan->nama_penyewa }}</td></tr>
                <tr><td style="padding: 6px 0; color: #64748b;">DP</td><td style="padding: 6px 0;">Rp {{ number_format((float) $pemesanan->dp_nominal, 0, ',', '.') }}</td></tr>
            </table>
            <p style="margin: 16px 0 0;">
                <a href="{{ route('verifikasi.index') }}" style="display: inline-block; background: #f59e0b; color: #0b1f24; text-decoration: none; font-weight: 700; border-radius: 10px; padding: 10px 20px; font-size: 13px;">Buka antrean verifikasi</a>
            </p>
        </div>
    </body>
</html>
