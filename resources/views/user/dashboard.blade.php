@extends('layouts.app')
@section('title', 'Dashboard')
@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
@if($customer && !$customer->isVerified())
<div class="alert alert-warning border-0 shadow-sm mb-4" style="border-radius:10px;">
    <div class="d-flex gap-3 align-items-center">
        <i class="fas fa-exclamation-triangle fs-4 text-warning"></i>
        <div>
            <strong>Akun Belum Diverifikasi</strong>
            <p class="mb-0 small">Akun Anda masih menunggu verifikasi dari admin. Anda belum dapat melakukan pemesanan. Hubungi admin jika perlu.</p>
        </div>
    </div>
</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Selamat Datang, {{ auth()->user()->name }}!</h4>
        <p class="text-muted small mb-0">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
    </div>
    <a href="{{ route('catalog') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-store me-1"></i>Katalog Fasilitas
    </a>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    @foreach([
        ['Total Booking','total_bookings','fa-calendar-check','primary'],
        ['Booking Aktif','active_bookings','fa-spinner','warning'],
        ['Booking Selesai','completed_bookings','fa-check-circle','success'],
        ['Menunggu Pembayaran','pending_payment','fa-money-bill','danger'],
    ] as $card)
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-{{ $card[3] }} bg-opacity-10 text-{{ $card[3] }}">
                    <i class="fas {{ $card[2] }}"></i>
                </div>
                <div>
                    <div class="fw-bold fs-4">{{ $stats[$card[1]] }}</div>
                    <div class="text-muted small">{{ $card[0] }}</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-3">
    {{-- Recent bookings --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-list me-2 text-primary"></i>Booking Terbaru</span>
                <a href="{{ route('user.bookings.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                @forelse($recentBookings as $b)
                <div class="d-flex align-items-center gap-3 px-3 py-3 border-bottom">
                    <div class="bg-primary bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center" style="width:42px;height:42px;min-width:42px;">
                        <i class="fas fa-calendar-check text-primary"></i>
                    </div>
                    <div class="flex-grow-1 min-width-0">
                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold small">{{ $b->booking_number }}</span>
                            <span class="badge bg-{{ $b->status_badge['color'] }} ms-2">{{ $b->status_badge['label'] }}</span>
                        </div>
                        <div class="text-muted" style="font-size:.78rem;">
                            {{ $b->items->map(fn($i)=>$i->facility->name)->join(', ') }}
                        </div>
                        <div class="text-muted" style="font-size:.75rem;">
                            <i class="fas fa-calendar me-1"></i>{{ $b->start_date->format('d M Y') }} – {{ $b->end_date->format('d M Y') }}
                            &bull; <strong class="text-primary">{{ 'Rp '.number_format($b->total_amount,0,',','.') }}</strong>
                        </div>
                    </div>
                    <a href="{{ route('user.bookings.show', $b->id) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                </div>
                @empty
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times text-muted fs-2 d-block mb-3" style="opacity:.4;"></i>
                    <p class="text-muted small">Belum ada booking. Yuk buat pemesanan pertama Anda!</p>
                    <a href="{{ route('catalog') }}" class="btn btn-primary btn-sm">Lihat Fasilitas</a>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Notifications + Profile --}}
    <div class="col-lg-4">
        {{-- Profile card --}}
        <div class="card mb-3">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ auth()->user()->avatar_url }}" class="rounded-circle" width="50" height="50">
                    <div>
                        <div class="fw-bold">{{ auth()->user()->name }}</div>
                        <div class="text-muted small">{{ $customer?->type_label ?? 'Penyewa' }}</div>
                        @if($customer)
                        <span class="badge bg-{{ $customer->verification_badge['color'] }}" style="font-size:.68rem;">
                            {{ $customer->verification_badge['label'] }}
                        </span>
                        @endif
                    </div>
                </div>
                <a href="{{ route('user.profile') }}" class="btn btn-outline-secondary btn-sm w-100 mt-3">
                    <i class="fas fa-user-edit me-1"></i>Edit Profil
                </a>
            </div>
        </div>

        {{-- Notifications --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-bell me-2 text-warning"></i>Notifikasi</span>
                <a href="{{ route('user.notifications') }}" class="btn btn-sm btn-outline-secondary">Semua</a>
            </div>
            <div class="card-body p-0">
                @forelse($unreadNotifications as $notif)
                <div class="px-3 py-2 border-bottom bg-light">
                    <div class="d-flex gap-2">
                        <i class="fas fa-circle text-{{ $notif->type }} mt-1 small"></i>
                        <div>
                            <div class="fw-semibold small">{{ $notif->title }}</div>
                            <div class="text-muted" style="font-size:.75rem;">{{ Str::limit($notif->message, 60) }}</div>
                            <div class="text-muted" style="font-size:.7rem;">{{ $notif->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-3 small"><i class="fas fa-check-circle text-success me-1"></i>Tidak ada notifikasi baru</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
