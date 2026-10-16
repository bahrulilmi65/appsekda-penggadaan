@extends('layouts.app')
@section('title', 'Manajemen Inventory')
@section('breadcrumb')
    <li class="breadcrumb-item">Master Data</li>
    <li class="breadcrumb-item active">Inventory</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Manajemen Inventory</h4>
    <a href="{{ route('admin.monitoring.index') }}" class="btn btn-outline-info btn-sm">
        <i class="fas fa-tv me-1"></i>Monitoring Real-Time
    </a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" class="d-flex gap-2 flex-wrap">
            <input type="text" name="search" class="form-control form-control-sm" style="width:200px;" placeholder="Cari..." value="{{ request('search') }}">
            <select name="category" class="form-select form-select-sm" style="width:160px;">
                <option value="">Semua Kategori</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" {{ request('category')==$c->id?'selected':'' }}>{{ $c->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 small" id="inventoryTable">
            <thead class="table-light">
                <tr>
                    <th>Fasilitas</th><th>Kategori</th>
                    <th class="text-end">Total</th>
                    <th class="text-end text-success">Tersedia</th>
                    <th class="text-end text-warning">Dipesan</th>
                    <th class="text-end text-primary">Digunakan</th>
                    <th class="text-end text-info">Laundry</th>
                    <th class="text-end text-danger">Rusak</th>
                    <th class="text-end text-dark">Hilang</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($facilities as $f)
                @php $inv = $f->inventory; @endphp
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $f->name }}</div>
                        <div class="text-muted" style="font-size:.72rem;">{{ $f->code }} &bull; {{ $f->unit }}</div>
                    </td>
                    <td><span class="badge bg-secondary">{{ $f->category->name }}</span></td>
                    <td class="text-end fw-bold">{{ number_format($inv?->qty_total ?? 0) }}</td>
                    <td class="text-end">
                        <span class="{{ ($inv?->qty_available ?? 0) <= $f->minimum_stock ? 'text-danger fw-bold' : 'text-success fw-bold' }}">
                            {{ number_format($inv?->qty_available ?? 0) }}
                        </span>
                    </td>
                    <td class="text-end text-warning">{{ number_format($inv?->qty_reserved ?? 0) }}</td>
                    <td class="text-end text-primary">{{ number_format(($inv?->qty_in_use ?? 0) + ($inv?->qty_in_delivery ?? 0) + ($inv?->qty_preparing ?? 0)) }}</td>
                    <td class="text-end text-info">{{ number_format($inv?->qty_laundry ?? 0) }}</td>
                    <td class="text-end text-danger">{{ number_format($inv?->qty_damaged ?? 0) }}</td>
                    <td class="text-end text-dark">{{ number_format($inv?->qty_lost ?? 0) }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.inventory.movements', $f->id) }}" class="btn btn-sm btn-outline-info" title="Histori"><i class="fas fa-history"></i></a>
                            <button class="btn btn-sm btn-outline-success btn-add-stock" data-id="{{ $f->id }}" data-name="{{ $f->name }}" data-unit="{{ $f->unit }}" title="Tambah Stok"><i class="fas fa-plus"></i></button>
                            <button class="btn btn-sm btn-outline-warning btn-adjust" data-id="{{ $f->id }}" data-name="{{ $f->name }}" title="Sesuaikan"><i class="fas fa-sliders-h"></i></button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-transparent d-flex flex-column flex-sm-row gap-2 justify-content-sm-between align-items-sm-center">
        <small class="text-muted">Menampilkan {{ $facilities->firstItem() ?? 0 }}–{{ $facilities->lastItem() ?? 0 }} dari {{ $facilities->total() }} data</small>
        <div>{{ $facilities->links('pagination::bootstrap-5') }}</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Add stock
$('.btn-add-stock').on('click', function() {
    const id = $(this).data('id'), name = $(this).data('name'), unit = $(this).data('unit');
    Swal.fire({
        title: `Tambah Stok: ${name}`,
        html: `<div class="mb-2 text-start"><label class="form-label small">Jumlah (${unit})</label>
            <input id="addQty" type="number" class="form-control" value="1" min="1"></div>
            <div class="mb-2 text-start"><label class="form-label small">Keterangan</label>
            <input id="addNotes" type="text" class="form-control" placeholder="Contoh: Pembelian baru"></div>`,
        showCancelButton: true, confirmButtonText: 'Tambah', confirmButtonColor: '#198754',
        preConfirm: () => ({ qty: document.getElementById('addQty').value, notes: document.getElementById('addNotes').value })
    }).then(r => {
        if (r.isConfirmed) {
            $.post(`/admin/inventory/${id}/add-stock`, { quantity: r.value.qty, notes: r.value.notes })
            .done(res => { if (res.success) { Swal.fire({icon:'success',text:res.message,timer:1500,showConfirmButton:false}).then(()=>location.reload()); } });
        }
    });
});

// Adjust stock
$('.btn-adjust').on('click', function() {
    const id = $(this).data('id'), name = $(this).data('name');
    Swal.fire({
        title: `Sesuaikan Stok: ${name}`,
        html: `<div class="mb-2 text-start"><label class="form-label small">Status</label>
            <select id="adjStatus" class="form-select">
                ${['available','laundry','damaged','lost','maintenance'].map(s=>`<option value="${s}">${s}</option>`).join('')}
            </select></div>
            <div class="mb-2 text-start"><label class="form-label small">Jumlah Baru</label>
            <input id="adjQty" type="number" class="form-control" min="0" value="0"></div>
            <div class="mb-2 text-start"><label class="form-label small">Keterangan <span class="text-danger">*</span></label>
            <input id="adjNotes" type="text" class="form-control" placeholder="Alasan penyesuaian..." required></div>`,
        showCancelButton: true, confirmButtonText: 'Sesuaikan', confirmButtonColor: '#ffc107',
        preConfirm: () => {
            if (!document.getElementById('adjNotes').value) { Swal.showValidationMessage('Keterangan wajib diisi'); return false; }
            return { status: document.getElementById('adjStatus').value, qty: document.getElementById('adjQty').value, notes: document.getElementById('adjNotes').value };
        }
    }).then(r => {
        if (r.isConfirmed) {
            $.post(`/admin/inventory/${id}/adjust`, { status: r.value.status, quantity: r.value.qty, notes: r.value.notes })
            .done(res => { if (res.success) { Swal.fire({icon:'success',timer:1500,showConfirmButton:false}).then(()=>location.reload()); } });
        }
    });
});
</script>
@endpush

