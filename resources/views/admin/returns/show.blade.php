@extends('layouts.app')
@section('title', 'Detail Pengembalian')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.return.index') }}">Pengembalian</a></li>
    <li class="breadcrumb-item active">{{ $return->return_number }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">{{ $return->return_number }}</h4>
        <span class="badge bg-{{ $return->status_badge['color'] }} fs-6">{{ $return->status_badge['label'] }}</span>
    </div>
    @if($return->status === 'completed')
    <button class="btn btn-success btn-sm" id="btnVerify">
        <i class="fas fa-check me-1"></i>Verifikasi Pengembalian
    </button>
    @endif
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold"><i class="fas fa-boxes me-2 text-primary"></i>Detail Barang Dikembalikan</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>Fasilitas</th>
                            <th class="text-end">Dikembalikan</th>
                            <th class="text-end text-success">Baik</th>
                            <th class="text-end text-info">Kotor</th>
                            <th class="text-end text-danger">Rusak</th>
                            <th class="text-end text-dark">Hilang</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($return->items as $item)
                        <tr>
                            <td class="fw-semibold">{{ $item->facility->name }}</td>
                            <td class="text-end">{{ $item->quantity_returned }}</td>
                            <td class="text-end text-success fw-bold">{{ $item->qty_good }}</td>
                            <td class="text-end text-info">{{ $item->qty_dirty }}</td>
                            <td class="text-end text-danger">{{ $item->qty_damaged }}</td>
                            <td class="text-end text-dark">{{ $item->qty_lost }}</td>
                            <td class="text-muted">{{ $item->condition_notes ?? '–' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="fas fa-info-circle me-2"></i>Info Pengembalian</div>
            <div class="card-body small">
                <table class="table table-sm table-borderless">
                    <tr><td class="text-muted">No.</td><td class="fw-semibold">{{ $return->return_number }}</td></tr>
                    <tr><td class="text-muted">Booking</td><td><a href="{{ route('admin.booking.show', $return->booking_id) }}">{{ $return->booking->booking_number }}</a></td></tr>
                    <tr><td class="text-muted">Penyewa</td><td>{{ $return->booking->customer->name }}</td></tr>
                    <tr><td class="text-muted">Tgl Kembali</td><td>{{ $return->return_date->format('d M Y') }}</td></tr>
                    <tr><td class="text-muted">Diterima</td><td>{{ $return->receivedBy?->name ?? '–' }}</td></tr>
                    @if($return->verified_at)
                    <tr><td class="text-muted">Diverifikasi</td><td>{{ $return->verified_at->format('d M Y H:i') }}<br>{{ $return->verifiedBy?->name }}</td></tr>
                    @endif
                </table>
                @if($return->notes)
                <div class="alert alert-light border py-2">{{ $return->notes }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('btnVerify')?.addEventListener('click', function() {
    Swal.fire({ title:'Verifikasi pengembalian ini?', icon:'question', showCancelButton:true, confirmButtonText:'Verifikasi', confirmButtonColor:'#198754' })
    .then(r => {
        if (r.isConfirmed) {
            $.post('/admin/pengembalian/{{ $return->id }}/verify')
            .done(res => { if (res.success) Swal.fire({icon:'success',timer:1500,showConfirmButton:false}).then(()=>location.reload()); });
        }
    });
});
</script>
@endpush
