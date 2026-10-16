@extends('layouts.app')
@section('title', 'Keranjang Pesanan')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Keranjang</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Keranjang Pesanan</h4>
    <a href="{{ route('catalog') }}" class="btn btn-outline-primary btn-sm">
        <i class="fas fa-plus me-1"></i>Tambah Fasilitas
    </a>
</div>

@if($carts->isEmpty())
<div class="card border-0 shadow-sm text-center py-5">
    <div class="card-body">
        <i class="fas fa-shopping-cart text-muted mb-3" style="font-size:3rem; opacity:.3;"></i>
        <h5 class="text-muted">Keranjang Anda kosong</h5>
        <p class="text-muted small">Tambahkan fasilitas dari katalog untuk memulai pemesanan</p>
        <a href="{{ route('catalog') }}" class="btn btn-primary">Lihat Katalog Fasilitas</a>
    </div>
</div>
@else
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="fas fa-list me-2 text-primary"></i>Item Pesanan ({{ $carts->count() }} item)
            </div>
            <div class="card-body p-0">
                @foreach($carts as $cart)
                <div class="d-flex gap-3 p-3 border-bottom cart-item" data-id="{{ $cart->id }}">
                    <img src="{{ $cart->facility->primary_image_url }}"
                        class="rounded-3" style="width:80px;height:80px;object-fit:cover;"
                        onerror="this.src='https://placehold.co/80x80/1a3a6c/fff?text=F'">
                    <div class="flex-grow-1">
                        <div class="fw-semibold">{{ $cart->facility->name }}</div>
                        <div class="text-muted small mb-1">{{ $cart->facility->category->name }} &bull; {{ $cart->facility->type_label }}</div>
                        <div class="text-primary small">
                            Rp {{ number_format($cart->facility->price,0,',','.') }} {{ $cart->facility->price_type_label }}
                        </div>

                        @if($cart->start_date)
                        <div class="text-muted small mt-1">
                            <i class="fas fa-calendar me-1"></i>{{ $cart->start_date->format('d M Y') }}
                            @if($cart->end_date && $cart->end_date != $cart->start_date) – {{ $cart->end_date->format('d M Y') }} @endif
                            <span class="ms-2 badge bg-secondary">{{ $cart->duration_days }} hari</span>
                        </div>
                        @else
                        <div class="text-warning small mt-1"><i class="fas fa-exclamation-triangle me-1"></i>Tanggal belum dipilih</div>
                        @endif
                    </div>
                    <div class="d-flex flex-column align-items-end gap-2">
                        @if(!$cart->facility->isBuilding())
                        <div class="input-group input-group-sm" style="width:100px;">
                            <button class="btn btn-outline-secondary btn-qty-minus" type="button" data-id="{{ $cart->id }}">-</button>
                            <input type="number" class="form-control text-center qty-input" value="{{ $cart->quantity }}" min="1" data-id="{{ $cart->id }}">
                            <button class="btn btn-outline-secondary btn-qty-plus" type="button" data-id="{{ $cart->id }}">+</button>
                        </div>
                        @else
                        <span class="badge bg-primary">1 unit</span>
                        @endif
                        <div class="fw-bold text-primary small">
                            Rp {{ number_format($cart->subtotal,0,',','.') }}
                        </div>
                        <button class="btn btn-sm btn-outline-danger btn-remove-cart" data-id="{{ $cart->id }}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm sticky-top" style="top:80px;">
            <div class="card-header bg-white fw-semibold">
                <i class="fas fa-receipt me-2 text-primary"></i>Ringkasan Pesanan
            </div>
            <div class="card-body">
                <div class="mb-3 p-3 bg-light rounded-3">
                    <label class="form-label small fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
                    <input type="date" id="globalStartDate" class="form-control form-control-sm mb-2" min="{{ now()->addDay()->toDateString() }}">
                    <label class="form-label small fw-semibold">Tanggal Selesai <span class="text-danger">*</span></label>
                    <input type="date" id="globalEndDate" class="form-control form-control-sm">
                    <div class="row g-2 mt-1">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Jam Mulai</label>
                            <input type="time" id="globalStartTime" class="form-control form-control-sm" value="08:00">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Jam Selesai</label>
                            <input type="time" id="globalEndTime" class="form-control form-control-sm" value="17:00">
                        </div>
                    </div>
                </div>

                <table class="table table-sm mb-3">
                    @foreach($carts as $cart)
                    <tr>
                        <td class="text-muted small">{{ Str::limit($cart->facility->name,25) }}</td>
                        <td class="text-end small fw-semibold">Rp {{ number_format($cart->subtotal,0,',','.') }}</td>
                    </tr>
                    @endforeach
                    <tr class="border-top">
                        <td class="fw-bold">Total</td>
                        <td class="text-end fw-bold text-primary">
                            Rp {{ number_format($carts->sum('subtotal'),0,',','.') }}
                        </td>
                    </tr>
                </table>

                <div class="alert alert-info border-0 py-2 small mb-3">
                    <i class="fas fa-info-circle me-1"></i>
                    Estimasi biaya dapat berubah sesuai durasi dan persetujuan admin.
                </div>

                <a href="{{ route('user.bookings.create') }}" class="btn btn-primary w-100 py-2 fw-semibold">
                    <i class="fas fa-arrow-right me-2"></i>Lanjutkan Pemesanan
                </a>
                <button class="btn btn-outline-danger btn-sm w-100 mt-2" id="clearCart">
                    <i class="fas fa-trash me-1"></i>Kosongkan Keranjang
                </button>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
// Remove cart item
$(document).on('click', '.btn-remove-cart', function() {
    const id = $(this).data('id');
    Swal.fire({ title: 'Hapus item ini?', icon: 'question', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Hapus' })
    .then(r => {
        if (r.isConfirmed) {
            $.ajax({ url: `/user/keranjang/${id}`, type: 'DELETE' }).done(res => {
                if (res.success) { location.reload(); }
            });
        }
    });
});

// Clear cart
$('#clearCart').on('click', function() {
    Swal.fire({ title: 'Kosongkan keranjang?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545' })
    .then(r => {
        if (r.isConfirmed) {
            $.ajax({ url: '/user/keranjang', type: 'DELETE' }).done(() => location.reload());
        }
    });
});

// Qty +/-
$('.btn-qty-plus').on('click', function() {
    const id = $(this).data('id');
    const input = $(`.qty-input[data-id="${id}"]`);
    input.val(parseInt(input.val()) + 1);
    updateQty(id, input.val());
});
$('.btn-qty-minus').on('click', function() {
    const id = $(this).data('id');
    const input = $(`.qty-input[data-id="${id}"]`);
    const newVal = Math.max(1, parseInt(input.val()) - 1);
    input.val(newVal);
    updateQty(id, newVal);
});
function updateQty(id, qty) {
    $.ajax({ url: `/user/keranjang/${id}`, type: 'PUT', data: { quantity: qty } });
}
</script>
@endpush
