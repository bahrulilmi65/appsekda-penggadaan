@extends('layouts.app')
@section('title', 'Booking Saya')
@section('breadcrumb')
    <li class="breadcrumb-item active">Booking Saya</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Booking Saya</h4>
    <a href="{{ route('catalog') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Buat Booking Baru</a>
</div>

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" class="d-flex gap-2 flex-wrap">
            <select name="status" class="form-select form-select-sm" style="width:200px;">
                <option value="">Semua Status</option>
                @foreach(\App\Models\Booking::STATUSES as $k => $v)
                    <option value="{{ $k }}" {{ request('status')===$k ? 'selected' : '' }}>{{ $v['label'] }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            <a href="{{ route('user.bookings.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        </form>
    </div>
</div>

@forelse($bookings as $booking)
<div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
    <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-start gap-3">
            <div class="flex-grow-1">
                <div class="d-flex gap-2 align-items-center mb-2">
                    <span class="fw-bold">{{ $booking->booking_number }}</span>
                    <span class="badge bg-{{ $booking->status_badge['color'] }}">{{ $booking->status_badge['label'] }}</span>
                    <span class="badge bg-{{ $booking->payment_status_badge['color'] }}">{{ $booking->payment_status_badge['label'] }}</span>
                </div>
                <div class="text-muted small mb-1">
                    <i class="fas fa-calendar me-1"></i>{{ $booking->start_date->format('d M Y') }} â€“ {{ $booking->end_date->format('d M Y') }}
                    <span class="ms-2"><i class="fas fa-clock me-1"></i>{{ $booking->duration_days }} hari</span>
                </div>
                <div class="text-muted small">
                    <i class="fas fa-tag me-1"></i>{{ $booking->items->map(fn($i)=>$i->facility->name)->join(', ') }}
                </div>
            </div>
            <div class="text-end">
                <div class="fw-bold text-primary">{{ $booking->formatted_total }}</div>
                <div class="text-muted" style="font-size:.75rem;">{{ $booking->booking_date->format('d M Y') }}</div>
                <a href="{{ route('user.bookings.show', $booking->id) }}" class="btn btn-sm btn-outline-primary mt-2">
                    <i class="fas fa-eye me-1"></i>Detail
                </a>
            </div>
        </div>

        @if($booking->isExpired())
        <div class="alert alert-warning py-1 mb-0 mt-2 small border-0"><i class="fas fa-exclamation-triangle me-1"></i>Booking ini akan segera kadaluarsa</div>
        @endif

        @if(in_array($booking->status, ['waiting_payment','approved']) && $booking->payment_status === 'unpaid')
        <div class="mt-2 pt-2 border-top">
            <a href="{{ route('user.bookings.show', $booking->id) }}" class="btn btn-sm btn-success">
                <i class="fas fa-upload me-1"></i>Upload Bukti Pembayaran
            </a>
        </div>
        @endif
    </div>
</div>
@empty
<div class="card border-0 shadow-sm text-center py-5">
    <div class="card-body">
        <i class="fas fa-calendar-times text-muted mb-3" style="font-size:3rem; opacity:.3;"></i>
        <h5 class="text-muted">Belum ada booking</h5>
        <p class="text-muted small">Buat pemesanan pertama Anda sekarang</p>
        <a href="{{ route('catalog') }}" class="btn btn-primary">Lihat Katalog Fasilitas</a>
    </div>
</div>
@endforelse

<div class="mt-3">{{ $bookings->links('pagination::bootstrap-5') }}</div>
@endsection

