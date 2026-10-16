@extends('layouts.app')
@section('title', 'Laporan Booking')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.report.index') }}">Laporan</a></li>
    <li class="breadcrumb-item active">Booking</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Laporan Booking</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.report.booking.pdf', request()->all()) }}" class="btn btn-danger btn-sm" target="_blank">
            <i class="fas fa-file-pdf me-1"></i>PDF
        </a>
        <a href="{{ route('admin.report.booking.excel', request()->all()) }}" class="btn btn-success btn-sm">
            <i class="fas fa-file-excel me-1"></i>Excel
        </a>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    @foreach([['Total','total','primary'],['Selesai','completed','success'],['Dibatalkan','cancelled','danger'],['Aktif','active','info']] as $s)
    <div class="col-md-3">
        <div class="card border-0 shadow-sm"><div class="card-body text-center">
            <div class="fw-bold fs-3 text-{{ $s[2] }}">{{ $stats[$s[1]] }}</div>
            <div class="text-muted small">{{ $s[0] }}</div>
        </div></div>
    </div>
    @endforeach
</div>

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2"><input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}" placeholder="Dari tanggal"></div>
            <div class="col-md-2"><input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}" placeholder="Sampai tanggal"></div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    @foreach(\App\Models\Booking::STATUSES as $k=>$v)
                        <option value="{{ $k }}" {{ request('status')===$k?'selected':'' }}>{{ $v['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 small">
            <thead class="table-light">
                <tr><th>#</th><th>No. Booking</th><th>Penyewa</th><th>Fasilitas</th><th>Tgl Mulai</th><th>Tgl Selesai</th><th class="text-end">Total</th><th>Status</th><th>Pembayaran</th></tr>
            </thead>
            <tbody>
                @forelse($bookings as $i => $b)
                <tr>
                    <td>{{ ($bookings->currentPage()-1)*$bookings->perPage() + $i + 1 }}</td>
                    <td><a href="{{ route('admin.booking.show', $b->id) }}" class="text-decoration-none fw-semibold">{{ $b->booking_number }}</a></td>
                    <td>{{ $b->customer->name }}</td>
                    <td class="text-muted">{{ $b->items->map(fn($i)=>$i->facility->name)->join(', ') }}</td>
                    <td>{{ $b->start_date->format('d M Y') }}</td>
                    <td>{{ $b->end_date->format('d M Y') }}</td>
                    <td class="text-end fw-semibold">{{ $b->formatted_total }}</td>
                    <td><span class="badge bg-{{ $b->status_badge['color'] }}">{{ $b->status_badge['label'] }}</span></td>
                    <td><span class="badge bg-{{ $b->payment_status_badge['color'] }}">{{ $b->payment_status_badge['label'] }}</span></td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center py-4 text-muted">Tidak ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-transparent">{{ $bookings->links() }}</div>
</div>
@endsection
