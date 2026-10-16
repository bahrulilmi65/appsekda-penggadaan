@extends('layouts.app')
@section('title', 'Laporan Penyewa')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.report.index') }}">Laporan</a></li>
    <li class="breadcrumb-item active">Penyewa</li>
@endsection
@section('content')
<h4 class="fw-bold mb-4">Laporan Data Penyewa</h4>
<div class="card border-0 shadow-sm"><div class="table-responsive">
    <table class="table table-hover mb-0 small">
        <thead class="table-light"><tr><th>Kode</th><th>Nama</th><th>Jenis</th><th>Instansi</th><th>Telepon</th><th>Status Verifikasi</th><th>Total Booking</th></tr></thead>
        <tbody>
            @forelse($customers as $c)
            <tr>
                <td><code>{{ $c->code }}</code></td>
                <td class="fw-semibold">{{ $c->name }}</td>
                <td>{{ $c->type_label }}</td>
                <td>{{ $c->institution_name??'–' }}</td>
                <td>{{ $c->phone??'–' }}</td>
                <td><span class="badge bg-{{ $c->verification_badge['color'] }}">{{ $c->verification_badge['label'] }}</span></td>
                <td><span class="badge bg-primary">{{ $c->bookings_count }}</span></td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>
</div><div class="card-footer bg-transparent">{{ $customers->links() }}</div></div>
@endsection
