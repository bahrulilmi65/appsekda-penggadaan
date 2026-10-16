<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 9px; }
        h2 { color: #1a3a6c; text-align: center; margin-bottom: 5px; }
        p.sub { text-align: center; color: #666; margin-bottom: 15px; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1a3a6c; color: white; padding: 5px 6px; font-size: 9px; }
        td { padding: 4px 6px; border-bottom: 1px solid #e9ecef; }
        tr:nth-child(even) td { background: #f8f9fa; }
        .footer { text-align: center; margin-top: 20px; font-size: 8px; color: #999; border-top: 1px solid #dee2e6; padding-top: 8px; }
    </style>
</head>
<body>
    <h2>{{ $settings['organization_name'] ?? 'Sekretariat Daerah' }}</h2>
    <h3 style="text-align:center;color:#333;font-size:12px;">LAPORAN BOOKING FASILITAS</h3>
    <p class="sub">Dicetak: {{ now()->format('d M Y H:i') }} | Total: {{ $bookings->count() }} booking</p>

    <table>
        <thead>
            <tr><th>#</th><th>No. Booking</th><th>Penyewa</th><th>Fasilitas</th><th>Tgl Mulai</th><th>Tgl Selesai</th><th>Total</th><th>Status</th></tr>
        </thead>
        <tbody>
            @foreach($bookings as $i => $b)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $b->booking_number }}</td>
                <td>{{ $b->customer->name }}</td>
                <td>{{ $b->items->map(fn($x)=>$x->facility->name)->join(', ') }}</td>
                <td>{{ $b->start_date->format('d/m/Y') }}</td>
                <td>{{ $b->end_date->format('d/m/Y') }}</td>
                <td>Rp {{ number_format($b->total_amount,0,',','.') }}</td>
                <td>{{ \App\Models\Booking::STATUSES[$b->status]['label'] ?? $b->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">{{ $settings['organization_name'] ?? '' }} &bull; Sistem Informasi Penyewaan Fasilitas</div>
</body>
</html>
