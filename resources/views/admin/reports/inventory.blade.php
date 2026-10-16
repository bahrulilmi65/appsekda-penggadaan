@extends('layouts.app')
@section('title', 'Laporan Inventory')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.report.index') }}">Laporan</a></li>
    <li class="breadcrumb-item active">Inventory</li>
@endsection
@section('content')
<h4 class="fw-bold mb-4">Laporan Inventory</h4>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 small">
            <thead class="table-light">
                <tr><th>Fasilitas</th><th>Kategori</th><th class="text-end">Total</th><th class="text-end text-success">Tersedia</th><th class="text-end text-warning">Dipesan</th><th class="text-end text-primary">Digunakan</th><th class="text-end text-info">Laundry</th><th class="text-end text-danger">Rusak</th><th class="text-end">Hilang</th></tr>
            </thead>
            <tbody>
                @foreach($facilities as $f)
                @php $inv = $f->inventory; @endphp
                <tr>
                    <td class="fw-semibold">{{ $f->name }} <span class="text-muted small">({{ $f->unit }})</span></td>
                    <td><span class="badge bg-secondary">{{ $f->category->name }}</span></td>
                    <td class="text-end">{{ number_format($inv?->qty_total??0) }}</td>
                    <td class="text-end text-success fw-bold">{{ number_format($inv?->qty_available??0) }}</td>
                    <td class="text-end text-warning">{{ number_format($inv?->qty_reserved??0) }}</td>
                    <td class="text-end text-primary">{{ number_format(($inv?->qty_in_use??0)+($inv?->qty_in_delivery??0)) }}</td>
                    <td class="text-end text-info">{{ number_format($inv?->qty_laundry??0) }}</td>
                    <td class="text-end text-danger">{{ number_format($inv?->qty_damaged??0) }}</td>
                    <td class="text-end">{{ number_format($inv?->qty_lost??0) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-transparent">{{ $facilities->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
