@extends('layouts.app')
@section('title', 'Manajemen Pembayaran')
@section('breadcrumb')
    <li class="breadcrumb-item active">Pembayaran</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Manajemen Pembayaran</h4>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    @foreach([
        ['Total Tagihan','total_tagihan','fa-file-invoice-dollar','primary'],
        ['Total Terbayar','total_bayar','fa-check-circle','success'],
        ['Belum Bayar','belum_bayar','fa-times-circle','danger'],
        ['Menunggu Verifikasi','menunggu_verifikasi','fa-clock','warning'],
    ] as $s)
    <div class="col-md-3">
        <div class="card border-0 shadow-sm stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-{{ $s[3] }} bg-opacity-10 text-{{ $s[3] }}"><i class="fas {{ $s[2] }}"></i></div>
                <div>
                    <div class="fw-bold">{{ is_numeric($stats[$s[1]]) ? 'Rp '.number_format($stats[$s[1]],0,',','.') : $stats[$s[1]] }}</div>
                    <div class="text-muted small">{{ $s[0] }}</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3"><input type="text" name="search" class="form-control form-control-sm" placeholder="Cari no. payment / penyewa..." value="{{ request('search') }}"></div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    @foreach(\App\Models\Payment::STATUSES as $k=>$v)
                        <option value="{{ $k }}" {{ request('status')===$k?'selected':'' }}>{{ $v['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}"></div>
            <div class="col-md-2"><input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}"></div>
            <div class="col-md-3 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">Filter</button>
                <a href="{{ route('admin.payment.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                <a href="{{ route('admin.report.payment') }}" class="btn btn-success btn-sm"><i class="fas fa-file-excel me-1"></i>Export</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 small">
            <thead class="table-light">
                <tr><th>No. Payment</th><th>No. Booking</th><th>Penyewa</th><th class="text-end">Tagihan</th><th class="text-end">Dibayar</th><th>Metode</th><th>Tgl Bayar</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($payments as $p)
                <tr>
                    <td class="fw-semibold">{{ $p->payment_number }}</td>
                    <td><a href="{{ route('admin.booking.show', $p->booking_id) }}" class="text-decoration-none">{{ $p->booking->booking_number }}</a></td>
                    <td>{{ $p->booking->customer->name }}</td>
                    <td class="text-end">{{ $p->formatted_amount_due }}</td>
                    <td class="text-end text-success fw-semibold">{{ $p->amount_paid > 0 ? $p->formatted_amount_paid : '-' }}</td>
                    <td>{{ $p->payment_method ? \App\Models\Payment::PAYMENT_METHODS[$p->payment_method] : '-' }}</td>
                    <td>{{ $p->payment_date?->format('d M Y') ?? '-' }}</td>
                    <td><span class="badge bg-{{ $p->status_badge['color'] }}">{{ $p->status_badge['label'] }}</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.payment.show', $p->id) }}" class="btn btn-sm btn-outline-primary" title="Detail"><i class="fas fa-eye"></i></a>
                            @if($p->status === 'waiting_verification')
                                <button class="btn btn-sm btn-success btn-verify" data-id="{{ $p->id }}" title="Verifikasi"><i class="fas fa-check"></i></button>
                                <button class="btn btn-sm btn-danger btn-reject-pay" data-id="{{ $p->id }}" title="Tolak"><i class="fas fa-times"></i></button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center py-4 text-muted">Tidak ada data pembayaran</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-transparent">{{ $payments->links('pagination::bootstrap-5') }}</div>
</div>
@endsection

@push('scripts')
<script>
$('.btn-verify').on('click', function() {
    const id = $(this).data('id');
    Swal.fire({ title:'Verifikasi pembayaran ini?', icon:'question', showCancelButton:true, confirmButtonText:'Verifikasi', confirmButtonColor:'#198754' })
    .then(r => { if (r.isConfirmed) { $.post(`/admin/pembayaran/${id}/verify`).done(res => { if (res.success) { Swal.fire({icon:'success',timer:1500,showConfirmButton:false}).then(()=>location.reload()); } }); } });
});
$('.btn-reject-pay').on('click', function() {
    const id = $(this).data('id');
    Swal.fire({ title:'Tolak pembayaran?', input:'textarea', inputLabel:'Alasan penolakan', showCancelButton:true, confirmButtonColor:'#dc3545' })
    .then(r => { if (r.isConfirmed) { $.post(`/admin/pembayaran/${id}/reject`, { reason: r.value }).done(res => { if (res.success) { Swal.fire({icon:'success',timer:1500,showConfirmButton:false}).then(()=>location.reload()); } }); } });
});
</script>
@endpush

