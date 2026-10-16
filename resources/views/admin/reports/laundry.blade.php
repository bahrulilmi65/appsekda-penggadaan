@extends('layouts.app')
@section('title', 'Laporan Laundry')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.report.index') }}">Laporan</a></li>
    <li class="breadcrumb-item active">Laundry</li>
@endsection
@section('content')
<h4 class="fw-bold mb-4">Laporan Laundry</h4>
<div class="card border-0 shadow-sm"><div class="table-responsive">
    <table class="table table-hover mb-0 small">
        <thead class="table-light"><tr><th>No. Laundry</th><th>Fasilitas</th><th>Qty</th><th>Tgl Masuk</th><th>Tgl Selesai</th><th>Vendor</th><th>Biaya</th><th>Status</th></tr></thead>
        <tbody>
            @forelse($laundries as $l)
            <tr>
                <td>{{ $l->laundry_number }}</td><td>{{ $l->facility->name }}</td>
                <td>{{ $l->quantity }}</td><td>{{ $l->date_in->format('d M Y') }}</td>
                <td>{{ $l->date_done?->format('d M Y')??'–' }}</td><td>{{ $l->laundry_vendor??'–' }}</td>
                <td>{{ $l->laundry_cost>0?'Rp '.number_format($l->laundry_cost,0,',','.'):'–' }}</td>
                <td><span class="badge bg-{{ $l->status_badge['color'] }}">{{ $l->status_badge['label'] }}</span></td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center py-4 text-muted">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>
</div><div class="card-footer bg-transparent">{{ $laundries->links() }}</div></div>
@endsection
