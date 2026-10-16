@extends('layouts.app')
@section('title', 'Detail Pembayaran')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.payment.index') }}">Pembayaran</a></li>
    <li class="breadcrumb-item active">{{ $payment->payment_number }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">{{ $payment->payment_number }}</h4>
        <span class="badge bg-{{ $payment->status_badge['color'] }} fs-6">{{ $payment->status_badge['label'] }}</span>
    </div>
    <div class="d-flex gap-2">
        @if($payment->invoice)
        <a href="{{ route('admin.invoice.pdf', $payment->invoice->id) }}" class="btn btn-sm btn-outline-success" target="_blank">
            <i class="fas fa-file-pdf me-1"></i>Invoice PDF
        </a>
        @endif
        @if($payment->status === 'waiting_verification')
        <button class="btn btn-success btn-sm" id="btnVerify"><i class="fas fa-check me-1"></i>Verifikasi</button>
        <button class="btn btn-danger btn-sm" id="btnReject"><i class="fas fa-times me-1"></i>Tolak</button>
        @endif
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        {{-- Payment info --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold"><i class="fas fa-receipt me-2 text-primary"></i>Informasi Pembayaran</div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3">
                            <div class="text-muted small">Total Tagihan</div>
                            <div class="fw-bold fs-4 text-primary">{{ $payment->formatted_amount_due }}</div>
                        </div>
                    </div>
                    @if($payment->amount_paid > 0)
                    <div class="col-md-6">
                        <div class="p-3 bg-success bg-opacity-10 rounded-3">
                            <div class="text-muted small">Jumlah Dibayar</div>
                            <div class="fw-bold fs-4 text-success">{{ $payment->formatted_amount_paid }}</div>
                        </div>
                    </div>
                    @endif
                </div>
                <table class="table table-sm small">
                    <tr><td class="text-muted w-40">No. Payment</td><td class="fw-semibold">{{ $payment->payment_number }}</td></tr>
                    <tr><td class="text-muted">No. Booking</td><td><a href="{{ route('admin.booking.show', $payment->booking_id) }}">{{ $payment->booking->booking_number }}</a></td></tr>
                    <tr><td class="text-muted">Penyewa</td><td>{{ $payment->booking->customer->name }}</td></tr>
                    @if($payment->payment_method)
                    <tr><td class="text-muted">Metode</td><td>{{ \App\Models\Payment::PAYMENT_METHODS[$payment->payment_method] ?? '-' }}</td></tr>
                    @endif
                    @if($payment->bank_name)
                    <tr><td class="text-muted">Bank</td><td>{{ $payment->bank_name }}</td></tr>
                    @endif
                    @if($payment->account_number)
                    <tr><td class="text-muted">No. Rekening</td><td>{{ $payment->account_number }}</td></tr>
                    @endif
                    @if($payment->transaction_number)
                    <tr><td class="text-muted">No. Transaksi</td><td>{{ $payment->transaction_number }}</td></tr>
                    @endif
                    @if($payment->payment_date)
                    <tr><td class="text-muted">Tgl Pembayaran</td><td>{{ $payment->payment_date->format('d M Y') }}</td></tr>
                    @endif
                    @if($payment->verified_at)
                    <tr><td class="text-muted">Diverifikasi</td><td>{{ $payment->verified_at->format('d M Y H:i') }} oleh {{ $payment->verifiedBy?->name }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        {{-- Proof --}}
        @if($payment->proof_of_payment)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold"><i class="fas fa-file-image me-2"></i>Bukti Pembayaran</div>
            <div class="card-body text-center">
                @if(str_ends_with($payment->proof_of_payment, '.pdf'))
                    <a href="{{ $payment->proof_url }}" target="_blank" class="btn btn-outline-danger">
                        <i class="fas fa-file-pdf fs-3 me-2"></i>Buka PDF
                    </a>
                @else
                    <img src="{{ $payment->proof_url }}" class="img-fluid rounded shadow" style="max-height:400px;">
                @endif
                <div class="mt-2">
                    <a href="{{ $payment->proof_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-external-link-alt me-1"></i>Buka di Tab Baru
                    </a>
                </div>
            </div>
        </div>
        @endif

        {{-- Items --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="fas fa-list me-2"></i>Item Booking</div>
            <div class="table-responsive">
                <table class="table table-sm small mb-0">
                    <thead class="table-light"><tr><th>Fasilitas</th><th>Qty</th><th>Subtotal</th></tr></thead>
                    <tbody>
                        @foreach($payment->booking->items as $item)
                        <tr><td>{{ $item->facility->name }}</td><td>{{ $item->quantity }} {{ $item->facility->unit }}</td><td>{{ $item->formatted_subtotal }}</td></tr>
                        @endforeach
                    </tbody>
                    <tfoot class="fw-bold table-light"><tr><td colspan="2" class="text-end">Total</td><td class="text-primary">{{ $payment->formatted_amount_due }}</td></tr></tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        @if($payment->rejection_reason)
        <div class="alert alert-danger border-0 mb-3">
            <strong>Alasan Penolakan:</strong><br>{{ $payment->rejection_reason }}
        </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="fas fa-user me-2"></i>Data Penyewa</div>
            <div class="card-body small">
                <div class="fw-bold">{{ $payment->booking->customer->name }}</div>
                <div class="text-muted">{{ $payment->booking->customer->type_label }}</div>
                @if($payment->booking->customer->institution_name)
                <div class="text-muted">{{ $payment->booking->customer->institution_name }}</div>
                @endif
                <div class="mt-2">
                    <div><i class="fas fa-phone me-1 text-muted"></i>{{ $payment->booking->customer->phone }}</div>
                    <div><i class="fas fa-envelope me-1 text-muted"></i>{{ $payment->booking->customer->email }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const paymentId = {{ $payment->id }};
$('#btnVerify')?.on('click', () => {
    Swal.fire({ title:'Verifikasi pembayaran ini?', text:'Booking akan otomatis disetujui dan invoice dibuat.', icon:'question', showCancelButton:true, confirmButtonText:'Verifikasi', confirmButtonColor:'#198754' })
    .then(r => { if (r.isConfirmed) { $.post(`/admin/pembayaran/${paymentId}/verify`).done(res => { if (res.success) Swal.fire({icon:'success',timer:1500,showConfirmButton:false}).then(()=>location.reload()); }); } });
});
$('#btnReject')?.on('click', () => {
    Swal.fire({ title:'Tolak pembayaran?', input:'textarea', inputLabel:'Alasan penolakan', showCancelButton:true, confirmButtonColor:'#dc3545' })
    .then(r => { if (r.isConfirmed) { $.post(`/admin/pembayaran/${paymentId}/reject`, { reason: r.value }).done(res => { if (res.success) Swal.fire({icon:'success',timer:1500,showConfirmButton:false}).then(()=>location.reload()); }); } });
});
</script>
@endpush
