@extends('layouts.app')
@section('title', 'Buat Pemesanan')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('user.cart.index') }}">Keranjang</a></li>
    <li class="breadcrumb-item active">Buat Pemesanan</li>
@endsection

@section('content')
<h4 class="fw-bold mb-4">Konfirmasi Pemesanan</h4>

<form id="bookingForm">
    @csrf
    <div class="row g-4">
        <div class="col-lg-8">
            {{-- Booking details --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">
                    <i class="fas fa-calendar me-2 text-primary"></i>Detail Pemesanan
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="startDate" class="form-control" min="{{ now()->addDay()->toDateString() }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" id="endDate" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jam Mulai</label>
                            <input type="time" name="start_time" class="form-control" value="08:00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jam Selesai</label>
                            <input type="time" name="end_time" class="form-control" value="17:00">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Tujuan Penggunaan <span class="text-danger">*</span></label>
                            <textarea name="purpose" class="form-control" rows="2" placeholder="Contoh: Rapat Koordinasi OPD, Seminar Nasional, dll." required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jumlah Peserta</label>
                            <input type="number" name="participants" class="form-control" min="1" placeholder="Jumlah orang">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Catatan Tambahan</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Permintaan khusus atau informasi tambahan..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Data Penyewa --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">
                    <i class="fas fa-user me-2 text-primary"></i>Data Penyewa
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Nama</label>
                            <div class="fw-semibold">{{ $customer->name }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Jenis Penyewa</label>
                            <div class="fw-semibold">{{ $customer->type_label }}</div>
                        </div>
                        @if($customer->institution_name)
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Instansi</label>
                            <div class="fw-semibold">{{ $customer->institution_name }}</div>
                        </div>
                        @endif
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Telepon</label>
                            <div class="fw-semibold">{{ $customer->phone }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Email</label>
                            <div class="fw-semibold">{{ $customer->email }}</div>
                        </div>
                    </div>
                    <a href="{{ route('user.profile') }}" class="btn btn-sm btn-outline-secondary mt-3">
                        <i class="fas fa-edit me-1"></i>Edit Data Penyewa
                    </a>
                </div>
            </div>

            {{-- Items --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">
                    <i class="fas fa-list me-2 text-primary"></i>Item yang Dipesan
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Fasilitas</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Harga Satuan</th>
                                <th class="text-end">Subtotal*</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($carts as $cart)
                            <input type="hidden" name="cart_ids[]" value="{{ $cart->id }}">
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $cart->facility->name }}</div>
                                    <div class="text-muted small">{{ $cart->facility->category->name }}</div>
                                </td>
                                <td class="text-center">{{ $cart->quantity }} {{ $cart->facility->unit }}</td>
                                <td class="text-end">Rp {{ number_format($cart->facility->price,0,',','.') }}<br><span class="text-muted small">{{ $cart->facility->price_type_label }}</span></td>
                                <td class="text-end fw-semibold">Rp {{ number_format($cart->facility->price * $cart->quantity,0,',','.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-transparent text-muted small">
                    * Subtotal akan dikalkulasi ulang berdasarkan durasi setelah konfirmasi
                </div>
            </div>
        </div>

        {{-- Right: Summary --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top:80px;">
                <div class="card-header bg-primary text-white border-0 fw-semibold">
                    <i class="fas fa-receipt me-2"></i>Ringkasan Pemesanan
                </div>
                <div class="card-body">
                    <div id="durationInfo" class="alert alert-info border-0 py-2 small mb-3 d-none">
                        <i class="fas fa-clock me-1"></i><span id="durationText"></span>
                    </div>

                    <div class="alert alert-warning border-0 py-2 small mb-3">
                        <i class="fas fa-clock me-1"></i>
                        Booking akan otomatis kadaluarsa dalam <strong>30 menit</strong> jika pembayaran tidak dilakukan.
                    </div>

                    <div class="mb-3 small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Jumlah Item</span>
                            <span>{{ $carts->count() }} fasilitas</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Status Persetujuan</span>
                            <span class="badge bg-info">Perlu Persetujuan</span>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="fw-bold">Estimasi Total</span>
                        <span class="fw-bold text-primary fs-5" id="estimatedTotal">-</span>
                    </div>

                    <button type="button" class="btn btn-primary w-100 py-2 fw-semibold" id="submitBooking">
                        <i class="fas fa-check-circle me-2"></i>Konfirmasi Pemesanan
                    </button>
                    <a href="{{ route('user.cart.index') }}" class="btn btn-outline-secondary w-100 mt-2 btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Kembali ke Keranjang
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
// Update duration display
function updateDuration() {
    const start = document.getElementById('startDate').value;
    const end = document.getElementById('endDate').value;
    if (start && end) {
        const days = Math.ceil((new Date(end) - new Date(start)) / 86400000) + 1;
        document.getElementById('durationInfo').classList.remove('d-none');
        document.getElementById('durationText').textContent = `Durasi: ${days} hari (${start} – ${end})`;
        document.getElementById('estimatedTotal').textContent = 'Dihitung admin';
    }
}
document.getElementById('startDate').addEventListener('change', function() {
    document.getElementById('endDate').min = this.value;
    updateDuration();
});
document.getElementById('endDate').addEventListener('change', updateDuration);

// Submit booking
$('#submitBooking').on('click', function() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const purpose = document.querySelector('[name=purpose]').value;

    if (!startDate || !endDate) {
        Swal.fire({ icon: 'warning', title: 'Lengkapi Data', text: 'Tanggal mulai dan selesai harus diisi.' });
        return;
    }
    if (!purpose) {
        Swal.fire({ icon: 'warning', title: 'Lengkapi Data', text: 'Tujuan penggunaan harus diisi.' });
        return;
    }

    Swal.fire({
        title: 'Konfirmasi Pemesanan',
        html: `<p>Anda akan membuat booking untuk:<br><strong>${startDate} – ${endDate}</strong></p><p class="text-muted small">Booking akan menunggu persetujuan admin sebelum dapat dibayar.</p>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-check me-1"></i>Ya, Buat Booking',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#0d6efd',
    }).then(r => {
        if (!r.isConfirmed) return;

        $('#submitBooking').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Memproses...');

        $.post('/user/booking', $('#bookingForm').serialize())
        .done(res => {
            if (res.success) {
                Swal.fire({ icon: 'success', title: 'Booking Berhasil!', text: res.message, confirmButtonText: 'Lihat Booking' })
                .then(() => window.location.href = res.redirect);
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                $('#submitBooking').prop('disabled', false).html('<i class="fas fa-check-circle me-2"></i>Konfirmasi Pemesanan');
            }
        })
        .fail(xhr => {
            const msg = xhr.responseJSON?.message || 'Terjadi kesalahan pada server.';
            Swal.fire({ icon: 'error', title: 'Error', text: msg });
            $('#submitBooking').prop('disabled', false).html('<i class="fas fa-check-circle me-2"></i>Konfirmasi Pemesanan');
        });
    });
});
</script>
@endpush
