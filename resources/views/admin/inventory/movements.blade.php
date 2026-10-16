@extends('layouts.app')
@section('title', 'Riwayat Inventory')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.inventory.index') }}">Inventory</a></li>
    <li class="breadcrumb-item active">{{ $facility->name }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Riwayat: {{ $facility->name }}</h4>
        <p class="text-muted small mb-0">{{ $facility->code }} &bull; Stok total: {{ number_format($facility->total_stock) }} {{ $facility->unit }}</p>
    </div>
    <a href="{{ route('admin.fasilitas.inventoryDetail', $facility->id) }}" class="btn btn-outline-primary btn-sm">
        <i class="fas fa-boxes me-1"></i>Detail Inventory
    </a>
</div>

<div class="card border-0 shadow-sm mb-4"><div class="card-body py-2">
    <form method="GET" class="d-flex gap-2">
        <select name="type" class="form-select form-select-sm" style="width:200px;">
            <option value="">Semua Jenis</option>
            @foreach(\App\Models\InventoryMovement::MOVEMENT_LABELS as $k=>$v)
                <option value="{{ $k }}" {{ request('type')===$k?'selected':'' }}>{{ $v }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="{{ route('admin.inventory.movements', $facility->id) }}" class="btn btn-outline-secondary btn-sm">Reset</a>
    </form>
</div></div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 small">
            <thead class="table-light">
                <tr>
                    <th>Tanggal</th><th>Jenis Transaksi</th><th>Dari Status</th><th>Ke Status</th>
                    <th class="text-end">Qty</th><th class="text-end">Sebelum</th><th class="text-end">Sesudah</th>
                    <th>Referensi</th><th>Oleh</th><th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $m)
                <tr>
                    <td class="text-muted" style="white-space:nowrap;">{{ $m->created_at->format('d M Y H:i') }}</td>
                    <td><span class="badge bg-secondary">{{ $m->movement_label }}</span></td>
                    <td class="text-muted">{{ $m->from_status ?? '–' }}</td>
                    <td><span class="badge bg-info text-dark">{{ $m->to_status }}</span></td>
                    <td class="text-end fw-bold {{ $m->quantity > 0 ? 'text-success' : 'text-danger' }}">
                        {{ $m->quantity > 0 ? '+' : '' }}{{ $m->quantity }}
                    </td>
                    <td class="text-end">{{ number_format($m->qty_before) }}</td>
                    <td class="text-end fw-semibold">{{ number_format($m->qty_after) }}</td>
                    <td class="text-muted small">{{ $m->reference_type ? ucfirst($m->reference_type).' #'.$m->reference_id : '–' }}</td>
                    <td>{{ $m->performedBy?->name ?? 'System' }}</td>
                    <td class="text-muted">{{ Str::limit($m->notes ?? '', 40) }}</td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center py-4 text-muted">Belum ada riwayat</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-transparent">{{ $movements->links() }}</div>
</div>
@endsection
