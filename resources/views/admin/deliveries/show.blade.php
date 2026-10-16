@extends('layouts.app')
@section('title', 'Detail Pengiriman')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.delivery.index') }}">Pengiriman</a></li>
    <li class="breadcrumb-item active">{{ $delivery->delivery_number }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">{{ $delivery->delivery_number }}</h4>
        <span class="badge bg-{{ $delivery->status_badge['color'] }} fs-6">{{ $delivery->status_badge['label'] }}</span>
    </div>
    @if(!in_array($delivery->status, ['delivered','returned']))
    <button class="btn btn-warning btn-sm" id="btnUpdateStatus">
        <i class="fas fa-edit me-1"></i>Update Status
    </button>
    @endif
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold"><i class="fas fa-list me-2"></i>Item Pengiriman</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 small">
                    <thead class="table-light"><tr><th>Fasilitas</th><th class="text-end">Dikirim</th><th class="text-end">Diterima</th><th>Kondisi</th></tr></thead>
                    <tbody>
                        @foreach($delivery->items as $item)
                        <tr>
                            <td class="fw-semibold">{{ $item->facility->name }}</td>
                            <td class="text-end">{{ $item->quantity_sent }} {{ $item->facility->unit }}</td>
                            <td class="text-end">{{ $item->quantity_received ?? '–' }}</td>
                            <td>{{ $item->condition ? ucfirst($item->condition) : '–' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="fas fa-info-circle me-2"></i>Info Pengiriman</div>
            <div class="card-body small">
                <table class="table table-sm table-borderless">
                    <tr><td class="text-muted">No. Pengiriman</td><td class="fw-semibold">{{ $delivery->delivery_number }}</td></tr>
                    <tr><td class="text-muted">Booking</td><td><a href="{{ route('admin.booking.show', $delivery->booking_id) }}">{{ $delivery->booking->booking_number }}</a></td></tr>
                    <tr><td class="text-muted">Tujuan</td><td>{{ $delivery->destination_address ?? '–' }}</td></tr>
                    <tr><td class="text-muted">Penerima</td><td>{{ $delivery->destination_person ?? '–' }}</td></tr>
                    <tr><td class="text-muted">Telepon</td><td>{{ $delivery->destination_phone ?? '–' }}</td></tr>
                    <tr><td class="text-muted">Petugas</td><td>{{ $delivery->assignedTo?->name ?? '–' }}</td></tr>
                    <tr><td class="text-muted">Kendaraan</td><td>{{ $delivery->vehicle ?? '–' }}</td></tr>
                    <tr><td class="text-muted">Pengemudi</td><td>{{ $delivery->driver_name ?? '–' }}</td></tr>
                    <tr><td class="text-muted">Jadwal</td><td>{{ $delivery->scheduled_at?->format('d M Y H:i') ?? '–' }}</td></tr>
                    <tr><td class="text-muted">Berangkat</td><td>{{ $delivery->departed_at?->format('d M Y H:i') ?? '–' }}</td></tr>
                    <tr><td class="text-muted">Tiba</td><td>{{ $delivery->arrived_at?->format('d M Y H:i') ?? '–' }}</td></tr>
                </table>
                @if($delivery->notes)<div class="alert alert-light border py-2 small">{{ $delivery->notes }}</div>@endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('btnUpdateStatus')?.addEventListener('click', function() {
    const id = {{ $delivery->id }};
    const statusMap = {
        'waiting':[['preparing','Persiapan']],'preparing':[['ready_to_deliver','Siap Kirim']],
        'ready_to_deliver':[['in_delivery','Berangkat']],'in_delivery':[['delivered','Terkirim']]
    };
    const current = '{{ $delivery->status }}';
    const options = statusMap[current] || [];
    if (!options.length) return;
    const optHtml = options.map(o=>`<option value="${o[0]}">${o[1]}</option>`).join('');
    Swal.fire({
        title:'Update Status',
        html:`<select id="newSt" class="form-select mb-2">${optHtml}</select><input id="stNotes" type="text" class="form-control" placeholder="Catatan...">`,
        showCancelButton:true, confirmButtonText:'Update',
        preConfirm: () => ({ status: document.getElementById('newSt').value, notes: document.getElementById('stNotes').value })
    }).then(r => {
        if (r.isConfirmed) {
            $.post(`/admin/pengiriman/${id}/status`, r.value)
            .done(res => { if (res.success) { Swal.fire({icon:'success',timer:1200,showConfirmButton:false}).then(()=>location.reload()); } });
        }
    });
});
</script>
@endpush
