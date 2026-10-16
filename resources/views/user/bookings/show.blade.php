@extends('layouts.app')
@section('title', 'Detail Booking '.$booking->booking_number)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('user.bookings.index') }}">Booking Saya</a></li>
    <li class="breadcrumb-item active">{{ $booking->booking_number }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">{{ $booking->booking_number }}</h4>
        <span class="badge bg-{{ $booking->status_badge['color'] }} me-2">{{ $booking->status_badge['label'] }}</span>
        <span class="badge bg-{{ $booking->payment_status_badge['color'] }}">{{ $booking->payment_status_badge['label'] }}</span>
    </div>
    <div class="d-flex gap-2">
        @if($booking->canBeCancelled())
        <button class="btn btn-sm btn-outline-danger" id="cancelBookingBtn">
            <i class="fas fa-times me-1"></i>Batalkan
        </button>
        @endif
        @if($booking->invoice)
        <a href="{{ route('user.invoice.download', $booking->invoice->id) }}" class="btn btn-sm btn-outline-success">
            <i class="fas fa-file-pdf me-1"></i>Invoice
        </a>
        @endif
    </div>
</div>

{{-- Hold timer --}}
@if($booking->hold_until && in_array($booking->status, ['pending','waiting_payment']))
<div class="alert alert-warning border-0 mb-4" style="border-radius:10px;">
    <div class="d-flex align-items-center gap-2">
        <i class="fas fa-clock fs-5 text-warning"></i>
        <div>
            <strong>Batas Waktu Pembayaran</strong>
            <div class="small">Booking akan kadaluarsa pada: <strong>{{ $booking->hold_until->format('d M Y, H:i') }}</strong>
                <span class="ms-2" id="countdown"></span>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        {{-- Timeline --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold"><i class="fas fa-timeline me-2 text-primary"></i>Status Timeline</div>
            <div class="card-body">
                @php
                $allStatuses = ['draft','pending','waiting_approval','approved','waiting_payment','paid','preparing','in_delivery','in_use','return_process','completed'];
                $currentIdx = array_search($booking->status, $allStatuses);
                @endphp
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @foreach($allStatuses as $idx => $st)
                    @php $badge = \App\Models\Booking::STATUSES[$st] ?? ['label'=>$st,'color'=>'secondary']; @endphp
                    <div class="d-flex align-items-center">
                        <div class="rounded-pill px-3 py-1 small fw-semibold
                            {{ $idx < $currentIdx ? 'bg-success text-white' : ($idx === $currentIdx ? 'bg-'.$badge['color'].' text-white' : 'bg-light text-muted') }}">
                            @if($idx < $currentIdx)<i class="fas fa-check me-1"></i>@endif
                            {{ $badge['label'] }}
                        </div>
                        @if($idx < count($allStatuses)-1)
                            <i class="fas fa-chevron-right text-muted mx-1 small"></i>
                        @endif
                    </div>
                    @endforeach
                </div>

                {{-- History --}}
                <div class="mt-3">
                    @foreach($booking->statusHistories as $history)
                    <div class="d-flex gap-3 mb-2">
                        <div class="mt-1"><i class="fas fa-circle text-primary" style="font-size:.5rem;"></i></div>
                        <div>
                            <div class="small fw-semibold">{{ \App\Models\Booking::STATUSES[$history->to_status]['label'] ?? $history->to_status }}</div>
                            <div class="text-muted" style="font-size:.75rem;">{{ $history->created_at->format('d M Y H:i') }} &bull; {{ $history->changedBy?->name ?? 'System' }}</div>
                            @if($history->notes) <div class="text-muted" style="font-size:.75rem;">{{ $history->notes }}</div> @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Booking Items --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold"><i class="fas fa-list me-2 text-primary"></i>Item Pesanan</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 small">
                    <thead class="table-light"><tr><th>Fasilitas</th><th>Qty</th><th>Harga</th><th>Subtotal</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach($booking->items as $item)
                        <tr>
                            <td class="fw-semibold">{{ $item->facility->name }}</td>
                            <td>{{ $item->quantity }} {{ $item->facility->unit }}</td>
                            <td>Rp {{ number_format($item->unit_price,0,',','.') }}<br><span class="text-muted" style="font-size:.7rem;">{{ $item->duration }} hari</span></td>
                            <td class="fw-semibold">{{ $item->formatted_subtotal }}</td>
                            <td><span class="badge bg-{{ $item->status_badge['color'] }}">{{ $item->status_badge['label'] }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Subtotal</td>
                            <td colspan="2" class="fw-bold">Rp {{ number_format($booking->subtotal,0,',','.') }}</td>
                        </tr>
                        @if($booking->discount_amount > 0)
                        <tr>
                            <td colspan="3" class="text-end">Diskon</td>
                            <td colspan="2" class="text-danger">- Rp {{ number_format($booking->discount_amount,0,',','.') }}</td>
                        </tr>
                        @endif
                        @if($booking->additional_fee > 0)
                        <tr>
                            <td colspan="3" class="text-end">Biaya Tambahan</td>
                            <td colspan="2">+ Rp {{ number_format($booking->additional_fee,0,',','.') }}</td>
                        </tr>
                        @endif
                        <tr class="fw-bold">
                            <td colspan="3" class="text-end fs-6">Total</td>
                            <td colspan="2" class="text-primary fs-6">{{ $booking->formatted_total }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Payment --}}
        @if($booking->payment && in_array($booking->status, ['waiting_payment','paid']))
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between">
                <span><i class="fas fa-money-bill me-2 text-success"></i>Pembayaran</span>
                <span class="badge bg-{{ $booking->payment->status_badge['color'] }}">{{ $booking->payment->status_badge['label'] }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3">
                            <div class="text-muted small">Total Tagihan</div>
                            <div class="fw-bold fs-5 text-primary">{{ $booking->payment->formatted_amount_due }}</div>
                        </div>
                    </div>
                    @if($booking->payment->amount_paid > 0)
                    <div class="col-md-6">
                        <div class="p-3 bg-success bg-opacity-10 rounded-3">
                            <div class="text-muted small">Sudah Dibayar</div>
                            <div class="fw-bold fs-5 text-success">{{ $booking->payment->formatted_amount_paid }}</div>
                        </div>
                    </div>
                    @endif
                </div>

                @if($booking->payment->status === 'unpaid' && $booking->status === 'waiting_payment')
                {{-- Upload bukti --}}
                <hr class="my-3">
                <h6 class="fw-semibold mb-3">Upload Bukti Pembayaran</h6>
                <div class="row g-3 mb-3 p-3 bg-info bg-opacity-10 rounded-3">
                    <div class="col-12">
                        <div class="fw-semibold small">Rekening Tujuan:</div>
                        <div class="small">
                            Bank: <strong>{{ \App\Models\Setting::get('bank_name') }}</strong><br>
                            No. Rek: <strong>{{ \App\Models\Setting::get('bank_account_number') }}</strong><br>
                            Atas Nama: <strong>{{ \App\Models\Setting::get('bank_account_name') }}</strong>
                        </div>
                    </div>
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
                            <label class="form-label small fw-semibold">Tanggal Pembayaran</label>
                            <input type="date" name="payment_date" class="form-control form-control-sm" max="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nama Bank</label>
                            <input type="text" name="bank_name" class="form-control form-control-sm" placeholder="Nama bank pengirim">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">No. Transaksi/Referensi</label>
                            <input type="text" name="transaction_number" class="form-control form-control-sm" placeholder="Nomor referensi">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Jumlah Dibayar</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="amount_paid" class="form-control" value="{{ $booking->total_amount }}" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Bukti Pembayaran <span class="text-danger">*</span></label>
                            <input type="file" name="proof_of_payment" class="form-control form-control-sm" accept="image/*,.pdf" required>
                            <div class="form-text">Format: JPG, PNG, atau PDF. Maks 5MB.</div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-success mt-3" id="uploadPaymentBtn">
                        <i class="fas fa-upload me-2"></i>Upload Bukti Pembayaran
                    </button>
                </form>
                @elseif($booking->payment->proof_of_payment)
                <div class="mt-3">
                    <div class="text-muted small mb-2">Bukti Pembayaran:</div>
                    <a href="{{ $booking->payment->proof_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye me-1"></i>Lihat Bukti
                    </a>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- Right: Summary --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold"><i class="fas fa-info-circle me-2 text-primary"></i>Informasi Booking</div>
            <div class="card-body small">
                <table class="table table-sm table-borderless">
                    <tr><td class="text-muted">No. Booking</td><td class="fw-semibold">{{ $booking->booking_number }}</td></tr>
                    <tr><td class="text-muted">Tanggal Booking</td><td>{{ $booking->booking_date->format('d M Y') }}</td></tr>
                    <tr><td class="text-muted">Tanggal Mulai</td><td class="fw-semibold">{{ $booking->start_date->format('d M Y') }}</td></tr>
                    <tr><td class="text-muted">Tanggal Selesai</td><td class="fw-semibold">{{ $booking->end_date->format('d M Y') }}</td></tr>
                    @if($booking->start_time)<tr><td class="text-muted">Waktu</td><td>{{ $booking->start_time }} – {{ $booking->end_time }}</td></tr>@endif
                    <tr><td class="text-muted">Tujuan</td><td>{{ $booking->purpose }}</td></tr>
                    @if($booking->participants)<tr><td class="text-muted">Peserta</td><td>{{ number_format($booking->participants) }} orang</td></tr>@endif
                    @if($booking->notes)<tr><td class="text-muted">Catatan</td><td>{{ $booking->notes }}</td></tr>@endif
                </table>
            </div>
        </div>

        @if($booking->delivery)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="fas fa-truck me-2 text-info"></i>Status Pengiriman</div>
            <div class="card-body small">
                <span class="badge bg-{{ $booking->delivery->status_badge['color'] }} mb-2">{{ $booking->delivery->status_badge['label'] }}</span>
                @if($booking->delivery->driver_name)
                <p class="text-muted mb-1">Pengantar: {{ $booking->delivery->driver_name }}</p>
                @endif
                @if($booking->delivery->vehicle)
                <p class="text-muted mb-1">Kendaraan: {{ $booking->delivery->vehicle }}</p>
                @endif
                @if($booking->delivery->arrived_at)
                <p class="text-muted mb-0">Tiba: {{ $booking->delivery->arrived_at->format('d M Y H:i') }}</p>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
// Countdown timer
@if($booking->hold_until && in_array($booking->status, ['pending','waiting_payment']))
const holdUntil = new Date('{{ $booking->hold_until->toISOString() }}');
const timerEl = document.getElementById('countdown');
const interval = setInterval(() => {
    const now = new Date();
    const diff = holdUntil - now;
    if (diff <= 0) { timerEl.textContent = '(Kadaluarsa)'; clearInterval(interval); return; }
    const m = Math.floor(diff / 60000);
    const s = Math.floor((diff % 60000) / 1000);
    timerEl.textContent = `(${m}m ${s}s tersisa)`;
}, 1000);
@endif

// Cancel booking
$('#cancelBookingBtn').on('click', function() {
    Swal.fire({
        title: 'Batalkan Booking?',
        input: 'textarea', inputLabel: 'Alasan pembatalan (opsional)', inputPlaceholder: 'Masukkan alasan...',
        icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Ya, Batalkan'
    }).then(r => {
        if (r.isConfirmed) {
            $.post('/user/booking/{{ $booking->id }}/cancel', { reason: r.value })
            .done(res => {
                if (res.success) {
                    Swal.fire({ icon:'success', title:'Dibatalkan', timer:1500, showConfirmButton:false }).then(() => location.reload());
                }
            });
        }
    });
});

// Upload payment
$('#uploadPaymentBtn').on('click', function() {
    const form = document.getElementById('paymentForm');
    const formData = new FormData(form);
    $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Mengupload...');

    $.ajax({
        url: '/user/pembayaran/{{ $booking->payment?->id }}/upload',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
    }).done(res => {
        if (res.success) {
            Swal.fire({ icon:'success', title:'Upload Berhasil!', text: res.message, timer:2000, showConfirmButton:false })
            .then(() => location.reload());
        } else {
            Swal.fire({ icon:'error', title:'Gagal', text: res.message });
            $('#uploadPaymentBtn').prop('disabled',false).html('<i class="fas fa-upload me-2"></i>Upload Bukti Pembayaran');
        }
    }).fail(xhr => {
        Swal.fire({ icon:'error', title:'Error', text: xhr.responseJSON?.message || 'Terjadi kesalahan.' });
        $('#uploadPaymentBtn').prop('disabled',false).html('<i class="fas fa-upload me-2"></i>Upload Bukti Pembayaran');
    });
});
</script>
@endpush
