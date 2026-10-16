<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #333; }
        .page { padding: 30px; }
        .header { display: table; width: 100%; border-bottom: 3px solid #1a3a6c; padding-bottom: 15px; margin-bottom: 20px; }
        .header-left { display: table-cell; width: 70%; vertical-align: top; }
        .header-right { display: table-cell; width: 30%; text-align: right; vertical-align: top; }
        .org-name { font-size: 16px; font-weight: bold; color: #1a3a6c; }
        .org-sub { font-size: 10px; color: #666; }
        .invoice-title { font-size: 22px; font-weight: bold; color: #1a3a6c; }
        .invoice-number { font-size: 13px; color: #555; }

        .info-section { display: table; width: 100%; margin-bottom: 20px; }
        .info-left { display: table-cell; width: 55%; vertical-align: top; }
        .info-right { display: table-cell; width: 45%; vertical-align: top; }
        .info-box { background: #f8f9fa; padding: 12px; border-radius: 6px; }
        .info-box h4 { font-size: 11px; color: #1a3a6c; font-weight: bold; border-bottom: 1px solid #dee2e6; padding-bottom: 5px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .info-row { display: table; width: 100%; margin-bottom: 4px; }
        .info-label { display: table-cell; width: 40%; color: #666; font-size: 10px; }
        .info-value { display: table-cell; font-weight: 500; font-size: 10px; }

        table.items { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.items th { background: #1a3a6c; color: white; padding: 8px 10px; font-size: 10px; text-align: left; }
        table.items td { padding: 7px 10px; border-bottom: 1px solid #e9ecef; font-size: 10px; }
        table.items tr:nth-child(even) td { background: #f8f9fa; }
        .text-right { text-align: right; }

        .totals { display: table; width: 100%; }
        .totals-right { display: table-cell; width: 40%; float: right; }
        .total-row { display: table; width: 100%; border-bottom: 1px solid #e9ecef; padding: 4px 0; }
        .total-label { display: table-cell; color: #666; }
        .total-value { display: table-cell; text-align: right; font-weight: 500; }
        .total-final { background: #1a3a6c; color: white; padding: 8px 12px; border-radius: 4px; display: table; width: 100%; margin-top: 5px; }
        .total-final-label { display: table-cell; font-weight: bold; }
        .total-final-value { display: table-cell; text-align: right; font-weight: bold; font-size: 14px; }

        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 4px; font-weight: bold; font-size: 12px; }
        .status-paid { background: #d1e7dd; color: #0f5132; }
        .status-unpaid { background: #f8d7da; color: #842029; }

        .footer { border-top: 2px solid #1a3a6c; margin-top: 30px; padding-top: 15px; text-align: center; font-size: 10px; color: #666; }
        .qr-section { text-align: center; margin-top: 15px; }
        .verification-text { font-size: 9px; color: #999; }
    </style>
</head>
<body>
<div class="page">
    {{-- Header --}}
    <div class="header">
        <div class="header-left">
            <div class="org-name">{{ $settings['organization_name'] ?? 'Sekretariat Daerah' }}</div>
            <div class="org-sub">{{ $settings['organization_address'] ?? '' }}</div>
            <div class="org-sub">Telp: {{ $settings['organization_phone'] ?? '' }} | Email: {{ $settings['organization_email'] ?? '' }}</div>
        </div>
        <div class="header-right">
            <div class="invoice-title">INVOICE</div>
            <div class="invoice-number">{{ $invoice->invoice_number }}</div>
            <div style="margin-top:8px;">
                <span class="status-badge {{ $invoice->status === 'paid' ? 'status-paid' : 'status-unpaid' }}">
                    {{ $invoice->status === 'paid' ? 'LUNAS' : 'BELUM LUNAS' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Info Section --}}
    <div class="info-section">
        <div class="info-left" style="padding-right:15px;">
            <div class="info-box">
                <h4>Tagihan Kepada</h4>
                <div class="info-row"><div class="info-label">Nama</div><div class="info-value">{{ $invoice->customer->name }}</div></div>
                <div class="info-row"><div class="info-label">Jenis</div><div class="info-value">{{ $invoice->customer->type_label }}</div></div>
                @if($invoice->customer->institution_name)
                <div class="info-row"><div class="info-label">Instansi</div><div class="info-value">{{ $invoice->customer->institution_name }}</div></div>
                @endif
                <div class="info-row"><div class="info-label">Telepon</div><div class="info-value">{{ $invoice->customer->phone }}</div></div>
                <div class="info-row"><div class="info-label">Email</div><div class="info-value">{{ $invoice->customer->email }}</div></div>
            </div>
        </div>
        <div class="info-right">
            <div class="info-box">
                <h4>Detail Invoice</h4>
                <div class="info-row"><div class="info-label">No. Invoice</div><div class="info-value">{{ $invoice->invoice_number }}</div></div>
                <div class="info-row"><div class="info-label">No. Booking</div><div class="info-value">{{ $invoice->booking->booking_number }}</div></div>
                <div class="info-row"><div class="info-label">Tgl Invoice</div><div class="info-value">{{ $invoice->invoice_date->format('d M Y') }}</div></div>
                <div class="info-row"><div class="info-label">Tgl Mulai</div><div class="info-value">{{ $invoice->booking->start_date->format('d M Y') }}</div></div>
                <div class="info-row"><div class="info-label">Tgl Selesai</div><div class="info-value">{{ $invoice->booking->end_date->format('d M Y') }}</div></div>
                <div class="info-row"><div class="info-label">Tujuan</div><div class="info-value">{{ $invoice->booking->purpose }}</div></div>
            </div>
        </div>
    </div>

    {{-- Items Table --}}
    <table class="items">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="35%">Fasilitas</th>
                <th width="10%" class="text-right">Qty</th>
                <th width="15%" class="text-right">Harga Satuan</th>
                <th width="10%" class="text-right">Durasi</th>
                <th width="15%" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->booking->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->facility->name }}<br><span style="color:#999;font-size:9px;">{{ $item->facility->category->name }}</span></td>
                <td class="text-right">{{ $item->quantity }} {{ $item->facility->unit }}</td>
                <td class="text-right">Rp {{ number_format($item->unit_price,0,',','.') }}</td>
                <td class="text-right">{{ $item->duration }} hari</td>
                <td class="text-right">Rp {{ number_format($item->subtotal,0,',','.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <div style="display:table;width:100%;">
        <div style="display:table-cell;width:60%;vertical-align:top;">
            @if($bankSettings)
            <div class="info-box" style="max-width:280px;">
                <h4>Informasi Pembayaran</h4>
                <div class="info-row"><div class="info-label">Bank</div><div class="info-value">{{ $bankSettings['bank_name'] ?? '-' }}</div></div>
                <div class="info-row"><div class="info-label">No. Rekening</div><div class="info-value">{{ $bankSettings['bank_account_number'] ?? '-' }}</div></div>
                <div class="info-row"><div class="info-label">Atas Nama</div><div class="info-value">{{ $bankSettings['bank_account_name'] ?? '-' }}</div></div>
            </div>
            @endif
        </div>
        <div style="display:table-cell;width:40%;vertical-align:top;">
            @php $rows = [['Subtotal',$invoice->subtotal]]; if($invoice->discount>0) $rows[]=['Diskon','- '.$invoice->discount]; if($invoice->additional_fee>0) $rows[]=['Biaya Tambahan',$invoice->additional_fee]; @endphp
            @foreach($rows as $row)
            <div class="total-row">
                <div class="total-label">{{ $row[0] }}</div>
                <div class="total-value">Rp {{ is_numeric($row[1]) ? number_format($row[1],0,',','.') : $row[1] }}</div>
            </div>
            @endforeach
            <div class="total-final">
                <div class="total-final-label">TOTAL</div>
                <div class="total-final-value">Rp {{ number_format($invoice->total,0,',','.') }}</div>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <p>{{ $settings['invoice_footer'] ?? 'Terima kasih atas kepercayaan Anda.' }}</p>
        <p class="verification-text" style="margin-top:5px;">
            Verifikasi invoice: {{ url('/verifikasi-invoice/'.$invoice->verification_token) }}
        </p>
        <p class="verification-text">Dicetak pada: {{ now()->format('d M Y H:i') }}</p>
    </div>
</div>
</body>
</html>
