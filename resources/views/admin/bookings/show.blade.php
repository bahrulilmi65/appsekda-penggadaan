@extends('layouts.app')
@section('title', 'Detail Booking '.$booking->booking_number)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.booking.index') }}">Booking</a></li>
    <li class="breadcrumb-item active">{{ $booking->booking_number }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">{{ $booking->booking_number }}</h4>
        <span class="badge bg-{{ $booking->status_badge['color'] }} me-2 fs-6">{{ $booking->status_badge['label'] }}</span>
        <span class="badge bg-{{ $booking->payment_status_badge['color'] }} fs-6">{{ $booking->payment_status_badge['label'] }}</span>
    </div>
    <div class="d-flex gap-2">
        @if(in_array($booking->status, ['pending','waiting_approval']))
            <button class="btn btn-success btn-sm" id="btnApprove"><i class="fas fa-check me-1"></i>Setujui</button>
            <button class="btn btn-danger btn-sm" id="btnReject"><i class="fas fa-times me-1"></i>Tolak</button>
        @endif
        @if(in_array($booking->status, ['paid']) && !$booking->delivery)
            <button class="btn btn-info btn-sm text-white" data-bs-toggle="modal" data-bs-target="#createDeliveryModal"><i class="fas fa-truck me-1"></i>Buat Pengiriman</button>
        @endif
        @if(in_array($booking->status, ['in_use']))
            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#createReturnModal"><i class="fas fa-undo me-1"></i>Catat Pengembalian</button>
        @endif
        @if($booking->invoice)
            <a href="{{ route('admin.invoice.pdf', $booking->invoice->id) }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-file-pdf me-1"></i>Invoice</a>
        @endif
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        {{-- Timeline --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold"><i class="fas fa-timeline me-2 text-primary"></i>Status Perjalanan</div>
            <div class="card-body">
                <div class="row g-2 mb-3">
                    @php
                    $statuses = ['pending','waiting_approval','approved','waiting_payment','paid','preparing','in_delivery','in_use','return_process','completed'];
                    $currentIdx = array_search($booking->status, $statuses);
                    @endphp
                    @foreach($statuses as $i => $st)
                    @php $badge = \App\Models\Booking::STATUSES[$st] ?? ['label'=>$st,'color'=>'secondary']; @endphp
                    <div class="col">
                        <div class="text-center">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-1
                                {{ $i < $currentIdx ? 'bg-success' : ($i === $currentIdx ? 'bg-'.$badge['color'] : 'bg-light border') }}"
                                style="width:32px;height:32px;">
                                <i class="fas {{ $i < $currentIdx ? 'fa-check' : 'fa-circle' }} text-{{ $i <= $currentIdx ? 'white' : 'muted' }}" style="font-size:.6rem;"></i>
                            </div>
                            <div style="font-size:.65rem;" class="text-muted">{{ $badge['label'] }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <hr>
                <h6 class="fw-semibold mb-3">Riwayat Status</h6>
                @foreach($booking->statusHistories as $h)
                <div class="d-flex gap-3 mb-2">
                    <div class="text-muted small" style="min-width:130px;">{{ $h->created_at->format('d M Y H:i') }}</div>
                    <div>
                        @if($h->from_status) <span class="badge bg-secondary me-1">{{ $h->from_label }}</span> → @endif
                        <span class="badge bg-primary">{{ $h->to_label }}</span>
                        <div class="text-muted small">{{ $h->changedBy?->name ?? 'System' }}{{ $h->notes ? ' — '.$h->notes : '' }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Items --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold"><i class="fas fa-list me-2 text-primary"></i>Item Pesanan</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 small">
                    <thead class="table-light"><tr><th>Fasilitas</th><th>Qty</th><th>Harga</th><th>Durasi</th><th>Subtotal</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach($booking->items as $item)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $item->facility->name }}</div>
                                <div class="text-muted" style="font-size:.75rem;">{{ $item->facility->type_label }}</div>
                            </td>
                            <td>{{ $item->quantity }} {{ $item->facility->unit }}</td>
                            <td>Rp {{ number_format($item->unit_price,0,',','.') }}</td>
                            <td>{{ $item->duration }} hari</td>
                            <td class="fw-semibold">{{ $item->formatted_subtotal }}</td>
                            <td><span class="badge bg-{{ $item->status_badge['color'] }}">{{ $item->status_badge['label'] }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr><td colspan="4" class="text-end">Total</td><td colspan="2" class="text-primary">{{ $booking->formatted_total }}</td></tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Change Status --}}
        @if(auth()->user()->can('approve bookings'))
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="fas fa-cog me-2 text-primary"></i>Ubah Status</div>
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <select id="newStatus" class="form-select form-select-sm">
                            <option value="">Pilih status...</option>
                            @foreach(['preparing','in_delivery','in_use','return_process','completed','cancelled'] as $st)
                                <option value="{{ $st }}">{{ \App\Models\Booking::STATUSES[$st]['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input type="text" id="statusNotes" class="form-control form-control-sm" placeholder="Catatan (opsional)">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary btn-sm w-100" id="btnChangeStatus">Ubah</button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Right --}}
    <div class="col-lg-4">
        {{-- Booking Info --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold"><i class="fas fa-info-circle me-2"></i>Info Booking</div>
            <div class="card-body small">
                <table class="table table-sm table-borderless">
                    <tr><td class="text-muted">No. Booking</td><td class="fw-semibold">{{ $booking->booking_number }}</td></tr>
                    <tr><td class="text-muted">Tgl Booking</td><td>{{ $booking->booking_date->format('d M Y') }}</td></tr>
                    <tr><td class="text-muted">Mulai</td><td class="fw-semibold">{{ $booking->start_date->format('d M Y') }}</td></tr>
                    <tr><td class="text-muted">Selesai</td><td class="fw-semibold">{{ $booking->end_date->format('d M Y') }}</td></tr>
                    @if($booking->start_time)<tr><td class="text-muted">Waktu</td><td>{{ $booking->start_time }} - {{ $booking->end_time }}</td></tr>@endif
                    <tr><td class="text-muted">Durasi</td><td>{{ $booking->duration_days }} hari</td></tr>
                    <tr><td class="text-muted">Tujuan</td><td>{{ $booking->purpose }}</td></tr>
                    @if($booking->participants)<tr><td class="text-muted">Peserta</td><td>{{ number_format($booking->participants) }}</td></tr>@endif
                </table>
            </div>
        </div>

        {{-- Customer --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold"><i class="fas fa-user me-2"></i>Data Penyewa</div>
            <div class="card-body small">
                <div class="fw-bold">{{ $booking->customer->name }}</div>
                <div class="text-muted">{{ $booking->customer->type_label }}</div>
                @if($booking->customer->institution_name)
                <div class="text-muted">{{ $booking->customer->institution_name }}</div>
                @endif
                <div class="mt-2">
                    <div><i class="fas fa-phone me-1 text-muted"></i>{{ $booking->customer->phone }}</div>
                    <div><i class="fas fa-envelope me-1 text-muted"></i>{{ $booking->customer->email }}</div>
                </div>
            </div>
        </div>

        {{-- Payment --}}
        @if($booking->payment)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between">
                <span><i class="fas fa-money-bill me-2"></i>Pembayaran</span>
                <a href="{{ route('admin.payment.show', $booking->payment->id) }}" class="btn btn-xs btn-outline-primary" style="font-size:.75rem;padding:2px 8px;">Detail</a>
            </div>
            <div class="card-body small">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Status</span>
                    <span class="badge bg-{{ $booking->payment->status_badge['color'] }}">{{ $booking->payment->status_badge['label'] }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Tagihan</span>
                    <span class="fw-semibold">{{ $booking->payment->formatted_amount_due }}</span>
                </div>
                @if($booking->payment->amount_paid)
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Dibayar</span>
                    <span class="fw-semibold text-success">{{ $booking->payment->formatted_amount_paid }}</span>
                </div>
                @endif
                @if($booking->payment->status === 'waiting_verification')
                <hr>
                <div class="d-flex gap-2">
                    <button class="btn btn-success btn-sm flex-grow-1" id="btnVerifyPayment"><i class="fas fa-check me-1"></i>Verifikasi</button>
                    <button class="btn btn-danger btn-sm flex-grow-1" id="btnRejectPayment"><i class="fas fa-times me-1"></i>Tolak</button>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
const bookingId = {{ $booking->id }};
@if($booking->payment) const paymentId = {{ $booking->payment->id }}; @endif

$('#btnApprove')?.on('click', () => {
    Swal.fire({ title:'Setujui booking ini?', icon:'question', showCancelButton:true, confirmButtonText:'Setujui', confirmButtonColor:'#198754' })
    .then(r => { if (r.isConfirmed) { $.post(`/admin/booking/${bookingId}/approve`).done(res => { if (res.success) { Swal.fire({icon:'success',timer:1500,showConfirmButton:false}).then(()=>location.reload()); } }); } });
});

$('#btnReject')?.on('click', () => {
    Swal.fire({ title:'Tolak booking?', input:'textarea', inputLabel:'Alasan', showCancelButton:true, confirmButtonColor:'#dc3545', confirmButtonText:'Tolak' })
    .then(r => { if (r.isConfirmed) { $.post(`/admin/booking/${bookingId}/reject`, { reason: r.value }).done(res => { if (res.success) { Swal.fire({icon:'success',timer:1500,showConfirmButton:false}).then(()=>location.reload()); } }); } });
});

$('#btnChangeStatus')?.on('click', () => {
    const st = document.getElementById('newStatus').value;
    const notes = document.getElementById('statusNotes').value;
    if (!st) { Swal.fire({icon:'warning',title:'Pilih status terlebih dahulu'}); return; }
    Swal.fire({ title:`Ubah status ke "${st}"?`, icon:'question', showCancelButton:true, confirmButtonText:'Ubah' })
    .then(r => { if (r.isConfirmed) { $.post(`/admin/booking/${bookingId}/status`, { status: st, notes }).done(res => { if (res.success) { Swal.fire({icon:'success',timer:1500,showConfirmButton:false}).then(()=>location.reload()); } }); } });
});

$('#btnVerifyPayment')?.on('click', () => {
    Swal.fire({ title:'Verifikasi pembayaran?', icon:'question', showCancelButton:true, confirmButtonText:'Verifikasi', confirmButtonColor:'#198754' })
    .then(r => { if (r.isConfirmed) { $.post(`/admin/pembayaran/${paymentId}/verify`).done(res => { if (res.success) { Swal.fire({icon:'success',timer:1500,showConfirmButton:false}).then(()=>location.reload()); } }); } });
});

$('#btnRejectPayment')?.on('click', () => {
    Swal.fire({ title:'Tolak pembayaran?', input:'textarea', inputLabel:'Alasan', showCancelButton:true, confirmButtonColor:'#dc3545' })
    .then(r => { if (r.isConfirmed) { $.post(`/admin/pembayaran/${paymentId}/reject`, { reason: r.value }).done(res => { if (res.success) { Swal.fire({icon:'success',timer:1500,showConfirmButton:false}).then(()=>location.reload()); } }); } });
});
</script>
@endpush
