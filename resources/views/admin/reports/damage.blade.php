@extends('layouts.app')
@section('title', 'Laporan Kerusakan')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.report.index') }}">Laporan</a></li>
    <li class="breadcrumb-item active">Kerusakan</li>
@endsection
@section('content')
<h4 class="fw-bold mb-4">Laporan Kerusakan & Kehilangan</h4>
<div class="card border-0 shadow-sm mb-4"><div class="card-body py-2">
    <form method="GET" class="d-flex gap-2">
        <input type="date" name="date_from" class="form-control form-control-sm" style="width:160px;" value="{{ request('date_from') }}">
        <input type="date" name="date_to" class="form-control form-control-sm" style="width:160px;" value="{{ request('date_to') }}">
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    </form>
</div></div>
<div class="card border-0 shadow-sm"><div class="table-responsive">
    <table class="table table-hover mb-0 small">
        <thead class="table-light"><tr><th>No.</th><th>Fasilitas</th><th>Jenis</th><th>Qty</th><th>Deskripsi</th><th>Tgl Lapor</th><th>Biaya</th><th>Status</th></tr></thead>
        <tbody>
            @forelse($damages as $d)
            <tr>
                <td><code>{{ $d->damage_number }}</code></td>
                <td>{{ $d->facility->name }}</td>
                <td><span class="badge bg-{{ $d->damage_type==='damage'?'danger':'dark' }}">{{ $d->damage_type==='damage'?'Rusak':'Hilang' }}</span></td>
                <td>{{ $d->quantity }}</td>
                <td>{{ Str::limit($d->description,50) }}</td>
                <td>{{ $d->reported_date->format('d M Y') }}</td>
                <td>{{ $d->repair_cost>0?'Rp '.number_format($d->repair_cost,0,',','.'):'–' }}</td>
                <td><span class="badge bg-{{ $d->status_badge['color'] }}">{{ $d->status_badge['label'] }}</span></td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center py-4 text-muted">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>
</div><div class="card-footer bg-transparent">{{ $damages->links() }}</div></div>
@endsection
