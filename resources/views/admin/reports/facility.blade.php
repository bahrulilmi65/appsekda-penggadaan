@extends('layouts.app')
@section('title', 'Laporan Fasilitas')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.report.index') }}">Laporan</a></li>
    <li class="breadcrumb-item active">Fasilitas</li>
@endsection
@section('content')
<h4 class="fw-bold mb-4">Laporan Penggunaan Fasilitas</h4>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 small">
            <thead class="table-light"><tr><th>#</th><th>Fasilitas</th><th class="text-end">Jml Booking</th><th class="text-end">Total Qty</th><th class="text-end">Total Pendapatan</th></tr></thead>
            <tbody>
                @foreach($data as $i => $item)
                <tr>
                    <td>{{ $data->firstItem() + $i }}</td>
                    <td class="fw-semibold">{{ $item->facility?->name ?? 'N/A' }}</td>
                    <td class="text-end"><span class="badge bg-primary">{{ number_format($item->booking_count) }}</span></td>
                    <td class="text-end">{{ number_format($item->total_qty) }}</td>
                    <td class="text-end fw-semibold text-success">Rp {{ number_format($item->total_revenue,0,',','.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-transparent">{{ $data->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
