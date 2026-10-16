@extends('layouts.app')
@section('title', 'Detail Pembayaran')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('user.payments.index') }}">Pembayaran</a></li>
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
        <a href="{{ route('user.invoice.download', $payment->invoice->id) }}" class="btn btn-sm btn-success" target="_blank">
            <i class="fas fa-file-pdf me-1"></i>Download Invoice
        </a>
        @endif
        <a href="{{ route('user.bookings.show', $payment->booking_id) }}" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-calendar me-1"></i>Lihat Booking
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold"><i class="fas fa-list me-2 text-primary"></i>Detail Pesanan</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 small">
                    <thead class="table-light"><tr><th>Fasilitas</th><th>Qty</th><th>Subtotal</th></tr></thead>
                    <tbody>
                        @foreach($payment->booking->items as $item)
                        <tr>
                            <td class="fw-semibold">{{ $item->facility->name }}</td>
                            <td>{{ $item->quantity }} {{ $item->facility->unit }}</td>
                            <td>{{ $item->formatted_subtotal }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr><td colspan="2" class="text-end">Total</td><td class="text-primary">{{ $payment->formatted_amount_due }}</td></tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Upload if unpaid --}}
        @if($payment->status === 'unpaid')
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="fas fa-upload me-2 text-success"></i>Upload Bukti Pembayaran</div>
            <div class="card-body">
                <div class="alert alert-info border-0 py-2 small mb-3">
                    <strong>Rekening Tujuan:</strong><br>
                    Bank: {{ \App\Models\Setting::get('bank_name') }}<br>
                    No. Rek: {{ \App\Models\Setting::get('bank_account_number') }}<br>
                    Atas Nama: {{ \App\Models\Setting::get('bank_account_name') }}<br>
                    <strong>Nominal: {{ $payment->formatted_amount_due }}</strong>
                </div>
                <form id="paymentForm" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Metode Pembayaran</label>
                            <select name="payment_method" class="form-select form-select-sm" required>
                                @foreach(\App\Models\Payment::PAYMENT_METHODS as $k=>$v)
                                    <option value="{{ $k }}">{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Tanggal Bayar</label>
                            <input type="date" name="payment_date" class="form-control form-control-sm" max="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nama Bank Pengirim</label>
                            <input type="text" name="bank_name" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">No. Transaksi</label>
                            <input type="text" name="transaction_number" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Jumlah Dibayar (Rp)</label>
                            <input type="number" name="amount_paid" class="form-control form-control-sm"
                                value="{{ $payment->amount_due }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Bukti Pembayaran <span class="text-danger">*</span></label>
                            <input type="file" name="proof_of_payment" class="form-control form-control-sm"
                                accept="image/jpeg,image/png,image/jpg,application/pdf" required>
                            <div class="form-text">Format: JPG, PNG, PDF. Maks 5MB.</div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-success mt-3" id="uploadBtn">
                        <i class="fas fa-upload me-2"></i>Upload Bukti
                    </button>
                </form>
            </div>
        </div>
        @elseif($payment->proof_of_payment)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="fas fa-file-image me-2"></i>Bukti Pembayaran</div>
            <div class="card-body">
                <a href="{{ $payment->proof_url }}" target="_blank" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-eye me-1"></i>Lihat Bukti Pembayaran
                </a>
                @if($payment->verified_at)
                <div class="text-muted small mt-2">
                    Diverifikasi: {{ $payment->verified_at->format('d M Y H:i') }} oleh {{ $payment->verifiedBy?->name }}
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="fas fa-info-circle me-2"></i>Info Pembayaran</div>
            <div class="card-body small">
                <table class="table table-sm table-borderless">
                    <tr><td class="text-muted">No. Payment</td><td class="fw-semibold">{{ $payment->payment_number }}</td></tr>
                    <tr><td class="text-muted">No. Booking</td><td>{{ $payment->booking->booking_number }}</td></tr>
                    <tr><td class="text-muted">Total Tagihan</td><td class="fw-bold text-primary fs-6">{{ $payment->formatted_amount_due }}</td></tr>
                    @if($payment->amount_paid > 0)
                    <tr><td class="text-muted">Sudah Dibayar</td><td class="fw-bold text-success">{{ $payment->formatted_amount_paid }}</td></tr>
                    @endif
                    @if($payment->payment_method)
                    <tr><td class="text-muted">Metode</td><td>{{ \App\Models\Payment::PAYMENT_METHODS[$payment->payment_method] ?? '-' }}</td></tr>
                    @endif
                    @if($payment->payment_date)
                    <tr><td class="text-muted">Tgl Bayar</td><td>{{ $payment->payment_date->format('d M Y') }}</td></tr>
                    @endif
                    @if($payment->transaction_number)
                    <tr><td class="text-muted">No. Transaksi</td><td>{{ $payment->transaction_number }}</td></tr>
                    @endif
                    @if($payment->due_date)
                    <tr><td class="text-muted">Batas Waktu</td>
                        <td class="{{ $payment->due_date->isPast() && $payment->status==='unpaid' ? 'text-danger fw-bold' : '' }}">
                            {{ $payment->due_date->format('d M Y H:i') }}
                        </td>
                    </tr>
                    @endif
                </table>

                @if($payment->rejection_reason)
                <div class="alert alert-danger border-0 py-2 mt-2 small">
                    <strong>Alasan Penolakan:</strong><br>{{ $payment->rejection_reason }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#uploadBtn').on('click', function() {
    const form = document.getElementById('paymentForm');
    const formData = new FormData(form);
    $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Mengupload...');

    $.ajax({
        url: '{{ route("user.payments.upload", $payment->id) }}',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false
    }).done(res => {
        if (res.success) {
            Swal.fire({ icon:'success', title:'Berhasil!', text: res.message, timer:2000, showConfirmButton:false })
            .then(() => location.reload());
        } else {
            Swal.fire({ icon:'error', title:'Gagal', text: res.message });
            $('#uploadBtn').prop('disabled', false).html('<i class="fas fa-upload me-2"></i>Upload Bukti');
        }
    }).fail(xhr => {
        Swal.fire({ icon:'error', title:'Error', text: xhr.responseJSON?.message || 'Terjadi kesalahan.' });
        $('#uploadBtn').prop('disabled', false).html('<i class="fas fa-upload me-2"></i>Upload Bukti');
    });
});
</script>
@endpush
