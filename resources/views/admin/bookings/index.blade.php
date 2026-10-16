@extends('layouts.app')
@section('title', 'Manajemen Booking')
@section('breadcrumb')
    <li class="breadcrumb-item active">Booking</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Manajemen Booking</h4>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#manualBookingModal">
        <i class="fas fa-plus me-1"></i>Booking Manual
    </button>
</div>

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2"><input type="text" name="search" class="form-control form-control-sm" placeholder="Cari booking/penyewa..." value="{{ request('search') }}"></div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    @foreach(\App\Models\Booking::STATUSES as $k=>$v)
                        <option value="{{ $k }}" {{ request('status')===$k?'selected':'' }}>{{ $v['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="payment_status" class="form-select form-select-sm">
                    <option value="">Semua Pembayaran</option>
                    @foreach(\App\Models\Booking::PAYMENT_STATUSES as $k=>$v)
                        <option value="{{ $k }}" {{ request('payment_status')===$k?'selected':'' }}>{{ $v['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}" placeholder="Dari tanggal"></div>
            <div class="col-md-2"><input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}" placeholder="Sampai tanggal"></div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">Filter</button>
                <a href="{{ route('admin.booking.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0" id="bookingsTable">
            <thead class="table-light">
                <tr>
                    <th>No. Booking</th>
                    <th>Penyewa</th>
                    <th>Fasilitas</th>
                    <th>Tanggal</th>
                    <th class="text-end">Total</th>
                    <th>Status</th>
                    <th>Pembayaran</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                <tr>
                    <td><a href="{{ route('admin.booking.show', $booking->id) }}" class="fw-semibold text-decoration-none">{{ $booking->booking_number }}</a></td>
                    <td>
                        <div class="fw-semibold small">{{ $booking->customer->name }}</div>
                        <div class="text-muted" style="font-size:.75rem;">{{ $booking->customer->type_label }}</div>
                    </td>
                    <td class="small text-muted">{{ $booking->items->map(fn($i)=>$i->facility->name)->join(', ') }}</td>
                    <td class="small">
                        {{ $booking->start_date->format('d M Y') }}<br>
                        <span class="text-muted">s/d {{ $booking->end_date->format('d M Y') }}</span>
                    </td>
                    <td class="text-end fw-semibold">{{ $booking->formatted_total }}</td>
                    <td><span class="badge bg-{{ $booking->status_badge['color'] }}">{{ $booking->status_badge['label'] }}</span></td>
                    <td><span class="badge bg-{{ $booking->payment_status_badge['color'] }}">{{ $booking->payment_status_badge['label'] }}</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.booking.show', $booking->id) }}" class="btn btn-sm btn-outline-primary" title="Detail"><i class="fas fa-eye"></i></a>
                            @if(in_array($booking->status, ['pending','waiting_approval']))
                                <button class="btn btn-sm btn-success btn-approve" data-id="{{ $booking->id }}" title="Setujui"><i class="fas fa-check"></i></button>
                                <button class="btn btn-sm btn-danger btn-reject" data-id="{{ $booking->id }}" title="Tolak"><i class="fas fa-times"></i></button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-4 text-muted">Tidak ada data booking</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-transparent">{{ $bookings->links('pagination::bootstrap-5') }}</div>
</div>

{{-- Manual Booking Modal --}}
<div class="modal fade" id="manualBookingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-plus me-2"></i>Booking Manual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="manualBookingForm">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Penyewa <span class="text-danger">*</span></label>
                            <select name="customer_id" class="form-select select2" required>
                                <option value="">Pilih penyewa...</option>
                                @foreach(\App\Models\Customer::with('user')->verified()->get() as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->type_label }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Tanggal Mulai</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Tanggal Selesai</label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Tujuan</label>
                            <input type="text" name="purpose" class="form-control" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Fasilitas yang Dipesan</label>
                            <div id="facilitiesContainer">
                                <div class="row g-2 mb-2 facility-row">
                                    <div class="col-md-8">
                                        <select name="facilities[0][facility_id]" class="form-select form-select-sm select2">
                                            <option value="">Pilih fasilitas...</option>
                                            @foreach(\App\Models\Facility::active()->orderBy('name')->get() as $f)
                                                <option value="{{ $f->id }}">{{ $f->name }} ({{ $f->type_label }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" name="facilities[0][quantity]" class="form-control form-control-sm" placeholder="Qty" value="1" min="1">
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-facility" style="display:none;"><i class="fas fa-times"></i></button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="addFacility">
                                <i class="fas fa-plus me-1"></i>Tambah Fasilitas
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="submitManualBooking">
                    <i class="fas fa-save me-1"></i>Buat Booking
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Initialize Select2
$('.select2').select2({ theme: 'bootstrap-5', dropdownParent: $('#manualBookingModal') });

// Approve/Reject
$('.btn-approve').on('click', function() {
    const id = $(this).data('id');
    Swal.fire({ title:'Setujui booking?', icon:'question', showCancelButton:true, confirmButtonText:'Setujui', confirmButtonColor:'#198754' })
    .then(r => {
        if (r.isConfirmed) {
            $.post(`/admin/booking/${id}/approve`).done(res => {
                if (res.success) { Swal.fire({icon:'success',title:'Berhasil',text:res.message,timer:1500,showConfirmButton:false}).then(()=>location.reload()); }
            });
        }
    });
});

$('.btn-reject').on('click', function() {
    const id = $(this).data('id');
    Swal.fire({ title:'Tolak booking?', input:'textarea', inputLabel:'Alasan', showCancelButton:true, confirmButtonColor:'#dc3545', confirmButtonText:'Tolak' })
    .then(r => {
        if (r.isConfirmed) {
            $.post(`/admin/booking/${id}/reject`, { reason: r.value }).done(res => {
                if (res.success) { Swal.fire({icon:'success',timer:1500,showConfirmButton:false}).then(()=>location.reload()); }
            });
        }
    });
});

// Add facility row
let facilityIdx = 1;
$('#addFacility').on('click', function() {
    const row = `<div class="row g-2 mb-2 facility-row">
        <div class="col-md-8"><select name="facilities[${facilityIdx}][facility_id]" class="form-select form-select-sm">
            <option value="">Pilih fasilitas...</option>
            @foreach(\App\Models\Facility::active()->orderBy('name')->get() as $f)
            <option value="{{ $f->id }}">{{ $f->name }}</option>
            @endforeach
        </select></div>
        <div class="col-md-3"><input type="number" name="facilities[${facilityIdx}][quantity]" class="form-control form-control-sm" value="1" min="1"></div>
        <div class="col-md-1"><button type="button" class="btn btn-outline-danger btn-sm w-100 remove-facility"><i class="fas fa-times"></i></button></div>
    </div>`;
    $('#facilitiesContainer').append(row);
    facilityIdx++;
});
$(document).on('click', '.remove-facility', function() { $(this).closest('.facility-row').remove(); });

// Submit manual booking
$('#submitManualBooking').on('click', function() {
    $.post('/admin/booking/manual', $('#manualBookingForm').serialize())
    .done(res => {
        if (res.success) {
            Swal.fire({icon:'success',title:'Berhasil',text:res.message,timer:2000,showConfirmButton:false}).then(()=>location.reload());
            bootstrap.Modal.getInstance(document.getElementById('manualBookingModal')).hide();
        } else {
            Swal.fire({icon:'error',title:'Gagal',text:res.message});
        }
    })
    .fail(xhr => Swal.fire({icon:'error',title:'Error',text:xhr.responseJSON?.message||'Terjadi kesalahan'}));
});
</script>
@endpush

