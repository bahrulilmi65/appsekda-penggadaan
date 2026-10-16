@extends('layouts.app')
@section('title', 'Manajemen Pengembalian')
@section('breadcrumb')
    <li class="breadcrumb-item active">Pengembalian</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Manajemen Pengembalian</h4>
</div>

<div class="card border-0 shadow-sm mb-4"><div class="card-body py-2">
    <form method="GET" class="d-flex gap-2">
        <select name="status" class="form-select form-select-sm" style="width:180px;">
            <option value="">Semua Status</option>
            @foreach(\App\Models\ReturnModel::STATUSES as $k=>$v)
                <option value="{{ $k }}" {{ request('status')===$k?'selected':'' }}>{{ $v['label'] }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    </form>
</div></div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 small">
            <thead class="table-light">
                <tr><th>No. Pengembalian</th><th>Booking</th><th>Penyewa</th><th>Tgl Kembali</th><th>Diterima Oleh</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($returns as $r)
                <tr>
                    <td class="fw-semibold">{{ $r->return_number }}</td>
                    <td><a href="{{ route('admin.booking.show', $r->booking_id) }}" class="text-decoration-none">{{ $r->booking->booking_number }}</a></td>
                    <td>{{ $r->booking->customer->name }}</td>
                    <td>{{ $r->return_date->format('d M Y') }}</td>
                    <td>{{ $r->receivedBy?->name ?? '–' }}</td>
                    <td><span class="badge bg-{{ $r->status_badge['color'] }}">{{ $r->status_badge['label'] }}</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.return.show', $r->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                            @if($r->status === 'completed')
                            <button class="btn btn-sm btn-success btn-verify-return" data-id="{{ $r->id }}">
                                <i class="fas fa-check"></i> Verifikasi
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada data pengembalian</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-transparent">{{ $returns->links() }}</div>
</div>
@endsection

@push('scripts')
<script>
$('.btn-verify-return').on('click', function() {
    const id = $(this).data('id');
    Swal.fire({ title:'Verifikasi pengembalian ini?', icon:'question', showCancelButton:true, confirmButtonText:'Verifikasi', confirmButtonColor:'#198754' })
    .then(r => {
        if (r.isConfirmed) {
            $.post(`/admin/pengembalian/${id}/verify`)
            .done(res => { if (res.success) Swal.fire({icon:'success',timer:1500,showConfirmButton:false}).then(()=>location.reload()); });
        }
    });
});
</script>
@endpush
