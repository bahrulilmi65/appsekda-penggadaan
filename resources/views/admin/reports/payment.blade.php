@extends('layouts.app')
@section('title', 'Laporan Pembayaran')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.report.index') }}">Laporan</a></li>
    <li class="breadcrumb-item active">Pembayaran</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Laporan Pembayaran</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.report.payment.pdf', request()->all()) }}" class="btn btn-danger btn-sm" target="_blank"><i class="fas fa-file-pdf me-1"></i>PDF</a>
        <a href="{{ route('admin.report.payment.excel', request()->all()) }}" class="btn btn-success btn-sm"><i class="fas fa-file-excel me-1"></i>Excel</a>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    @foreach([
        ['Total Tagihan','total_tagihan','primary'],
        ['Total Terbayar','total_bayar','success'],
        ['Belum Dibayar','belum_bayar','danger'],
        ['Menunggu Verifikasi','menunggu_verifikasi','warning'],
    ] as $s)
    <div class="col-md-3">
        <div class="card border-0 shadow-sm"><div class="card-body text-center">
            <div class="fw-bold fs-5 text-{{ $s[2] }}">{{ is_numeric($stats[$s[1]]) ? 'Rp '.number_format($stats[$s[1]],0,',','.') : $stats[$s[1]] }}</div>
            <div class="text-muted small">{{ $s[0] }}</div>
        </div></div>
    </div>
    @endforeach
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2"><input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}"></div>
            <div class="col-md-2"><input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}"></div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    @foreach(\App\Models\Payment::STATUSES as $k=>$v)
                        <option value="{{ $k }}" {{ request('status')===$k?'selected':'' }}>{{ $v['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary btn-sm w-100">Filter</button></div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 small">
            <thead class="table-light">
                <tr><th>#</th><th>No. Payment</th><th>No. Booking</th><th>Penyewa</th><th class="text-end">Tagihan</th><th class="text-end">Dibayar</th><th>Metode</th><th>Tgl Bayar</th><th>Status</th></tr>
            </thead>
            <tbody>
                @forelse($payments as $i => $p)
                <tr>
                    <td>{{ ($payments->currentPage()-1)*$payments->perPage() + $i + 1 }}</td>
                    <td class="fw-semibold">{{ $p->payment_number }}</td>
                    <td>{{ $p->booking->booking_number }}</td>
                    <td>{{ $p->booking->customer->name }}</td>
                    <td class="text-end">{{ $p->formatted_amount_due }}</td>
                    <td class="text-end text-success fw-semibold">{{ $p->amount_paid > 0 ? $p->formatted_amount_paid : '-' }}</td>
                    <td>{{ $p->payment_method ? \App\Models\Payment::PAYMENT_METHODS[$p->payment_method] : '-' }}</td>
                    <td>{{ $p->payment_date?->format('d M Y') ?? '-' }}</td>
                    <td><span class="badge bg-{{ $p->status_badge['color'] }}">{{ $p->status_badge['label'] }}</span></td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center py-4 text-muted">Tidak ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-transparent">{{ $payments->links() }}</div>
</div>
@endsection
