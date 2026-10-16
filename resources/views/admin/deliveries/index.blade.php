@extends('layouts.app')
@section('title', 'Manajemen Pengiriman')
@section('breadcrumb')
    <li class="breadcrumb-item active">Pengiriman</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Manajemen Pengiriman</h4>
</div>

<div class="card border-0 shadow-sm mb-4"><div class="card-body py-2">
    <form method="GET" class="d-flex gap-2">
        <select name="status" class="form-select form-select-sm" style="width:180px;">
            <option value="">Semua Status</option>
            @foreach(\App\Models\Delivery::STATUSES as $k=>$v)
                <option value="{{ $k }}" {{ request('status')===$k?'selected':'' }}>{{ $v['label'] }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="{{ route('admin.delivery.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
    </form>
</div></div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 small">
            <thead class="table-light">
                <tr><th>No. Pengiriman</th><th>Booking</th><th>Penyewa</th><th>Tujuan</th><th>Petugas</th><th>Terjadwal</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($deliveries as $d)
                <tr>
                    <td class="fw-semibold">{{ $d->delivery_number }}</td>
                    <td><a href="{{ route('admin.booking.show', $d->booking_id) }}" class="text-decoration-none">{{ $d->booking->booking_number }}</a></td>
                    <td>{{ $d->booking->customer->name }}</td>
                    <td>{{ Str::limit($d->destination_address ?? '-', 30) }}</td>
                    <td>{{ $d->assignedTo?->name ?? 'â€“' }}</td>
                    <td>{{ $d->scheduled_at?->format('d M Y H:i') ?? 'â€“' }}</td>
                    <td><span class="badge bg-{{ $d->status_badge['color'] }}">{{ $d->status_badge['label'] }}</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.delivery.show', $d->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                            @if(!in_array($d->status, ['delivered','returned']))
                            <button class="btn btn-sm btn-outline-warning btn-update-delivery" data-id="{{ $d->id }}" data-status="{{ $d->status }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-4 text-muted">Tidak ada data pengiriman</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-transparent">{{ $deliveries->links('pagination::bootstrap-5') }}</div>
</div>
@endsection

@push('scripts')
<script>
$('.btn-update-delivery').on('click', function() {
    const id = $(this).data('id');
    const nextStatuses = {
        'waiting': [['preparing','Persiapan']], 'preparing': [['ready_to_deliver','Siap Kirim']],
        'ready_to_deliver': [['in_delivery','Berangkat']], 'in_delivery': [['delivered','Terkirim']],
        'delivered': [['returning','Kembali']], 'returning': [['returned','Dikembalikan']]
    };
    const current = $(this).data('status');
    const options = nextStatuses[current] || [];
    if (!options.length) { Swal.fire({icon:'info',text:'Tidak ada status berikutnya'}); return; }
    const optHtml = options.map(o => `<option value="${o[0]}">${o[1]}</option>`).join('');
    Swal.fire({
        title:'Update Status Pengiriman',
        html:`<select id="newStatus" class="form-select mb-2">${optHtml}</select>
              <input id="delNotes" type="text" class="form-control" placeholder="Catatan (opsional)">`,
        showCancelButton:true, confirmButtonText:'Update',
        preConfirm: () => ({ status: document.getElementById('newStatus').value, notes: document.getElementById('delNotes').value })
    }).then(r => {
        if (r.isConfirmed) {
            $.post(`/admin/pengiriman/${id}/status`, r.value)
            .done(res => { if (res.success) { Swal.fire({icon:'success',timer:1200,showConfirmButton:false}).then(()=>location.reload()); } });
        }
    });
});
</script>
@endpush

