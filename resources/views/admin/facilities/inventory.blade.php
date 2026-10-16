@extends('layouts.app')
@section('title', 'Inventory: '.$facility->name)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.fasilitas.index') }}">Fasilitas</a></li>
    <li class="breadcrumb-item active">Inventory: {{ $facility->name }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Inventory: {{ $facility->name }}</h4>
    <div class="d-flex gap-2">
        <button class="btn btn-success btn-sm" id="btnAddStock"><i class="fas fa-plus me-1"></i>Tambah Stok</button>
        <button class="btn btn-warning btn-sm" id="btnAdjust"><i class="fas fa-sliders-h me-1"></i>Sesuaikan</button>
    </div>
</div>

{{-- Inventory Status --}}
@if($facility->inventory)
@php $inv = $facility->inventory; @endphp
<div class="row g-3 mb-4">
    @foreach([
        ['Total Stok','qty_total','secondary','fa-boxes'],
        ['Tersedia','qty_available','success','fa-check-circle'],
        ['Dipesan','qty_reserved','warning','fa-calendar-check'],
        ['Digunakan','qty_in_use','primary','fa-users'],
        ['Pengantaran','qty_in_delivery','info','fa-truck'],
        ['Laundry','qty_laundry','info','fa-soap'],
        ['Rusak','qty_damaged','danger','fa-tools'],
        ['Hilang','qty_lost','dark','fa-question-circle'],
    ] as $card)
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-{{ $card[2] }} bg-opacity-10 text-{{ $card[2] }}">
                    <i class="fas {{ $card[3] }}"></i>
                </div>
                <div>
                    <div class="fw-bold fs-4">{{ number_format($inv->{$card[1]}) }}</div>
                    <div class="text-muted small">{{ $card[0] }} ({{ $facility->unit }})</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Availability for today --}}
@if(isset($availabilityDetail) && $availabilityDetail)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold"><i class="fas fa-calendar-day me-2 text-primary"></i>Status Hari Ini ({{ now()->format('d M Y') }})</div>
    <div class="card-body">
        @if($availabilityDetail['type'] === 'building')
            <div class="alert alert-{{ $availabilityDetail['is_available'] ? 'success' : 'danger' }} border-0 py-2 mb-3">
                <strong>{{ $availabilityDetail['is_available'] ? 'TERSEDIA' : 'TERPESAN' }}</strong>
            </div>
            @if(!empty($availabilityDetail['bookings']))
            <h6 class="small fw-semibold">Booking Hari Ini:</h6>
            @foreach($availabilityDetail['bookings'] as $b)
            <div class="d-flex gap-2 small mb-1">
                <span class="badge bg-primary">{{ $b['booking_number'] }}</span>
                <span>{{ $b['customer_name'] }}</span>
                <span class="text-muted">{{ $b['start_time'] }} – {{ $b['end_time'] }}</span>
            </div>
            @endforeach
            @endif
        @else
            <div class="row g-3">
                @foreach([
                    ['qty_total','Total','secondary'],
                    ['qty_reserved','Dipesan','warning'],
                    ['qty_active','Digunakan','primary'],
                    ['qty_laundry','Laundry','info'],
                    ['qty_damaged','Rusak','danger'],
                    ['qty_available','Tersedia','success'],
                ] as $r)
                <div class="col-md-2 text-center">
                    <div class="fw-bold fs-3 text-{{ $r[2] }}">{{ $availabilityDetail[$r[0]] ?? 0 }}</div>
                    <div class="text-muted small">{{ $r[1] }}</div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endif

{{-- Movement History --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold d-flex justify-content-between">
        <span><i class="fas fa-history me-2 text-primary"></i>Riwayat Pergerakan Inventory</span>
        <a href="{{ route('admin.inventory.movements', $facility->id) }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 small">
            <thead class="table-light">
                <tr><th>Tanggal</th><th>Jenis</th><th>Dari</th><th>Ke</th><th class="text-end">Qty</th><th>Oleh</th><th>Keterangan</th></tr>
            </thead>
            <tbody>
                @forelse($movements as $m)
                <tr>
                    <td class="text-muted">{{ $m->created_at->format('d M Y H:i') }}</td>
                    <td><span class="badge bg-secondary">{{ $m->movement_label }}</span></td>
                    <td class="text-muted">{{ $m->from_status ?? '–' }}</td>
                    <td>{{ $m->to_status }}</td>
                    <td class="text-end fw-bold">{{ $m->quantity }}</td>
                    <td>{{ $m->performedBy?->name ?? 'System' }}</td>
                    <td class="text-muted">{{ Str::limit($m->notes, 50) }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-3 text-muted">Belum ada riwayat pergerakan</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-transparent">{{ $movements->links() }}</div>
</div>
@endsection

@push('scripts')
<script>
const facilityId = {{ $facility->id }};
const unit = '{{ $facility->unit }}';

$('#btnAddStock').on('click', () => {
    Swal.fire({
        title: 'Tambah Stok',
        html: `<div class="mb-2 text-start"><label class="form-label small">Jumlah (${unit})</label>
            <input id="addQty" type="number" class="form-control" value="1" min="1"></div>
            <div class="text-start"><label class="form-label small">Keterangan</label>
            <input id="addNotes" type="text" class="form-control" placeholder="Contoh: Pembelian baru"></div>`,
        showCancelButton: true, confirmButtonText: 'Tambah', confirmButtonColor: '#198754',
        preConfirm: () => ({ quantity: document.getElementById('addQty').value, notes: document.getElementById('addNotes').value })
    }).then(r => {
        if (r.isConfirmed) {
            $.post(`/admin/inventory/${facilityId}/add-stock`, r.value)
            .done(res => { if (res.success) { Swal.fire({icon:'success',text:res.message,timer:1500,showConfirmButton:false}).then(()=>location.reload()); } });
        }
    });
});

$('#btnAdjust').on('click', () => {
    Swal.fire({
        title: 'Sesuaikan Stok',
        html: `<div class="mb-2 text-start"><label class="form-label small">Status</label>
            <select id="adjSt" class="form-select">
                ${['available','laundry','damaged','lost','maintenance'].map(s=>`<option value="${s}">${s}</option>`).join('')}
            </select></div>
            <div class="mb-2 text-start"><label class="form-label small">Jumlah Baru</label>
            <input id="adjQty" type="number" class="form-control" min="0" value="0"></div>
            <div class="text-start"><label class="form-label small">Keterangan *</label>
            <input id="adjNotes" type="text" class="form-control" placeholder="Wajib diisi" required></div>`,
        showCancelButton: true, confirmButtonText: 'Sesuaikan',
        preConfirm: () => {
            if (!document.getElementById('adjNotes').value) { Swal.showValidationMessage('Keterangan wajib'); return false; }
            return { status: document.getElementById('adjSt').value, quantity: document.getElementById('adjQty').value, notes: document.getElementById('adjNotes').value };
        }
    }).then(r => {
        if (r.isConfirmed) {
            $.post(`/admin/inventory/${facilityId}/adjust`, r.value)
            .done(res => { if (res.success) { Swal.fire({icon:'success',timer:1500,showConfirmButton:false}).then(()=>location.reload()); } });
        }
    });
});
</script>
@endpush
