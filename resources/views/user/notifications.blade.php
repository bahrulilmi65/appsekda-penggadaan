@extends('layouts.app')
@section('title', 'Notifikasi')
@section('breadcrumb')
    <li class="breadcrumb-item active">Notifikasi</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Notifikasi</h4>
    <button class="btn btn-sm btn-outline-secondary" id="markAllRead">
        <i class="fas fa-check-double me-1"></i>Tandai Semua Dibaca
    </button>
</div>

@forelse($notifications as $notif)
<div class="card border-0 shadow-sm mb-2 {{ !$notif->is_read ? 'border-start border-primary border-3' : '' }}" style="border-radius:8px;">
    <div class="card-body py-2 px-3">
        <div class="d-flex gap-3 align-items-start">
            <div class="mt-1">
                <i class="fas fa-circle text-{{ $notif->type }}" style="font-size:.6rem;"></i>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between">
                    <span class="fw-semibold small {{ !$notif->is_read ? '' : 'text-muted' }}">{{ $notif->title }}</span>
                    <span class="text-muted" style="font-size:.72rem;">{{ $notif->created_at->diffForHumans() }}</span>
                </div>
                <div class="text-muted small">{{ $notif->message }}</div>
                @if($notif->action_url)
                <a href="{{ $notif->action_url }}" class="btn btn-xs btn-outline-primary mt-1" style="font-size:.72rem; padding:2px 8px;">
                    Lihat Detail <i class="fas fa-arrow-right ms-1"></i>
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
@empty
<div class="card border-0 shadow-sm text-center py-5">
    <div class="card-body">
        <i class="fas fa-bell-slash text-muted mb-3" style="font-size:3rem; opacity:.3;"></i>
        <h5 class="text-muted">Tidak ada notifikasi</h5>
    </div>
</div>
@endforelse

<div class="mt-3">{{ $notifications->links() }}</div>
@endsection

@push('scripts')
<script>
$('#markAllRead').on('click', function() {
    $.post('{{ route("user.notifications.read-all") }}').done(res => {
        if (res.success) {
            Swal.fire({icon:'success', text:'Semua notifikasi ditandai dibaca', timer:1200, showConfirmButton:false})
            .then(() => location.reload());
        }
    });
});
</script>
@endpush
