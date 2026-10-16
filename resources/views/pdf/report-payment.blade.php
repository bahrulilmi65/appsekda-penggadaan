<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 9px; }
        h2 { color: #1a3a6c; text-align: center; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1a3a6c; color: white; padding: 5px 6px; font-size: 9px; }
        td { padding: 4px 6px; border-bottom: 1px solid #e9ecef; }
        tr:nth-child(even) td { background: #f8f9fa; }
        .footer { text-align: center; margin-top: 20px; font-size: 8px; color: #999; border-top: 1px solid #dee2e6; padding-top: 8px; }
    </style>
</head>
<body>
    <h2>{{ $settings['organization_name'] ?? 'Sekretariat Daerah' }}</h2>
    <h3 style="text-align:center;color:#333;font-size:12px;">LAPORAN PEMBAYARAN</h3>
    <p style="text-align:center;color:#666;font-size:9px;margin-bottom:15px;">Dicetak: {{ now()->format('d M Y H:i') }}</p>

    <table>
        <thead>
            <tr><th>#</th><th>No. Payment</th><th>No. Booking</th><th>Penyewa</th><th>Tagihan</th><th>Dibayar</th><th>Metode</th><th>Tgl Bayar</th><th>Status</th></tr>
        </thead>
        <tbody>
            @foreach($payments as $i => $p)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $p->payment_number }}</td>
                <td>{{ $p->booking->booking_number }}</td>
                <td>{{ $p->booking->customer->name }}</td>
                <td>Rp {{ number_format($p->amount_due,0,',','.') }}</td>
                <td>Rp {{ number_format($p->amount_paid,0,',','.') }}</td>
                <td>{{ $p->payment_method ?? '-' }}</td>
                <td>{{ $p->payment_date?->format('d/m/Y') ?? '-' }}</td>
                <td>{{ \App\Models\Payment::STATUSES[$p->status]['label'] ?? $p->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">{{ $settings['organization_name'] ?? '' }} &bull; Sistem Informasi Penyewaan Fasilitas</div>
</body>
</html>
