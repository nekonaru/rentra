<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Struk {{ $pemesanan->kode }} - {{ $pemesanan->rental->nama }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />
        <style>
            * { box-sizing: border-box; }
            body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f6faf9; color: #0b1f24; margin: 0; padding: 24px; }
            .kertas { max-width: 560px; margin: 0 auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 28px; }
            .kepala { display: flex; align-items: center; justify-content: space-between; gap: 12px; border-bottom: 2px solid #0f766e; padding-bottom: 14px; }
            .kode { font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.08em; }
            h1 { font-size: 20px; margin: 4px 0 0; color: #0f766e; }
            .status { display: inline-block; padding: 4px 10px; border-radius: 9999px; color: #fff; font-size: 11px; font-weight: 700; text-transform: uppercase; }
            table { width: 100%; border-collapse: collapse; margin-top: 16px; font-size: 13px; }
            th, td { text-align: left; padding: 8px 4px; border-bottom: 1px solid #eef2f7; }
            th { color: #64748b; font-weight: 600; width: 40%; }
            .total td { font-weight: 700; font-size: 15px; border-bottom: none; padding-top: 12px; }
            .footer { margin-top: 20px; text-align: center; font-size: 11px; color: #94a3b8; }
            .aksi { text-align: center; margin-top: 16px; }
            button { font: inherit; font-weight: 700; background: #f59e0b; color: #0b1f24; border: 0; border-radius: 10px; padding: 10px 24px; cursor: pointer; }
            @media print {
                body { background: #fff; padding: 0; }
                .kertas { border: none; border-radius: 0; }
                .aksi { display: none; }
            }
        </style>
    </head>
    <body>
        <div class="kertas">
            <div class="kepala">
                <div>
                    <div class="kode">Struk Pemesanan</div>
                    <h1>{{ $pemesanan->rental->nama }}</h1>
                </div>
                <span class="status" style="background-color: {{ $pemesanan->status->warna() }}">{{ $pemesanan->status->label() }}</span>
            </div>

            <table>
                <tr><th>Kode</th><td>{{ $pemesanan->kode }}</td></tr>
                <tr><th>Unit</th><td>{{ $pemesanan->unit->nama }}</td></tr>
                <tr><th>Penyewa</th><td>{{ $pemesanan->nama_penyewa }}</td></tr>
                <tr><th>WhatsApp</th><td>{{ $pemesanan->wa }}</td></tr>
                <tr><th>Mulai</th><td>{{ $pemesanan->mulai->translatedFormat('d F Y, H:i') }} WIB</td></tr>
                <tr><th>Selesai</th><td>{{ $pemesanan->selesai->translatedFormat('d F Y, H:i') }} WIB</td></tr>
                <tr><th>DP ({{ $pemesanan->rental->persen_dp }}%)</th><td>Rp {{ number_format((float) $pemesanan->dp_nominal, 0, ',', '.') }}</td></tr>
                <tr class="total"><td>Total</td><td>Rp {{ number_format((float) $pemesanan->total, 0, ',', '.') }}</td></tr>
            </table>

            @if ($pemesanan->rental->info_rekening)
                <p style="margin-top: 14px; font-size: 12px; color: #475569;">Pembayaran ke: <strong>{{ $pemesanan->rental->info_rekening }}</strong></p>
            @endif

            <div class="footer">Dibuat dengan Rentra</div>
            <div class="aksi"><button onclick="window.print()">Cetak</button></div>
        </div>
    </body>
</html>
