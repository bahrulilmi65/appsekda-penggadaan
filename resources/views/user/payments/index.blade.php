@extends('layouts.app')
@section('title', 'Pembayaran Saya')
@section('breadcrumb')
    <li class="breadcrumb-item active">Pembayaran</li>
@endsection

@section('content')
<h4 class="fw-bold mb-4">Pembayaran Saya</h4>

@forelse($payments as $p)
<div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
    <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-start gap-3">
            <div class="flex-grow-1">
                <div class="d-flex gap-2 align-items-center mb-2">
                    <span class="fw-bold">{{ $p->payment_number }}</span>
                    <span class="badge bg-{{ $p->status_badge['color'] }}">{{ $p->status_badge['label'] }}</span>
                </div>
                <div class="text-muted small mb-1">
                    <i class="fas fa-calendar me-1"></i>Booking: <strong>{{ $p->booking->booking_number }}</strong>
                </div>
                <div class="text-muted small">
                    <i class="fas fa-money-bill me-1"></i>Tagihan:
                    <strong class="text-primary">{{ $p->formatted_amount_due }}</strong>
                    @if($p->amount_paid > 0)
                        &bull; Dibayar: <strong class="text-success">{{ $p->formatted_amount_paid }}</strong>
                    @endif
                </div>
            </div>
            <div class="text-end">
                @if($p->payment_date)
                <div class="text-muted small">{{ $p->payment_date->format('d M Y') }}</div>
                @endif
                <a href="{{ route('user.payments.show', $p->id) }}" class="btn btn-sm btn-outline-primary mt-2">Detail</a>
            </div>
        </div>
    </div>
</div>
@empty
<div class="card border-0 shadow-sm text-center py-5">
    <div class="card-body">
        <i class="fas fa-receipt text-muted mb-3" style="font-size:3rem; opacity:.3;"></i>
        <h5 class="text-muted">Belum ada data pembayaran</h5>
        <a href="{{ route('catalog') }}" class="btn btn-primary btn-sm mt-2">Mulai Pemesanan</a>
    </div>
</div>
@endforelse

<div class="mt-3">{{ $payments->links('pagination::bootstrap-5') }}</div>
@endsection

