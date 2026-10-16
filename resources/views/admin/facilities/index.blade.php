@extends('layouts.app')
@section('title', 'Manajemen Fasilitas')
@section('breadcrumb')
    <li class="breadcrumb-item">Master Data</li>
    <li class="breadcrumb-item active">Fasilitas</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Manajemen Fasilitas</h4>
    <a href="{{ route('admin.fasilitas.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i>Tambah Fasilitas
    </a>
</div>

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
            <input type="text" name="search" class="form-control form-control-sm" style="width:200px;" placeholder="Cari nama/kode..." value="{{ request('search') }}">
            <select name="category" class="form-select form-select-sm" style="width:160px;">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category')==$cat->id?'selected':'' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <select name="type" class="form-select form-select-sm" style="width:150px;">
                <option value="">Semua Jenis</option>
                @foreach(\App\Models\Facility::TYPES as $k=>$v)
                    <option value="{{ $k }}" {{ request('type')===$k?'selected':'' }}>{{ $v }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            <a href="{{ route('admin.fasilitas.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0" id="facilityTable">
            <thead class="table-light">
                <tr><th>Kode</th><th>Nama</th><th>Kategori</th><th>Jenis</th><th>Stok</th><th>Tersedia</th><th>Harga</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @foreach($facilities as $f)
                <tr>
                    <td><code class="small">{{ $f->code }}</code></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $f->primary_image_url }}" class="rounded" width="36" height="36" style="object-fit:cover;"
                                onerror="this.src='https://placehold.co/36x36/e9ecef/6c757d?text=F'">
                            <div>
                                <div class="fw-semibold small">{{ $f->name }}</div>
                                @if($f->location)<div class="text-muted" style="font-size:.72rem;">{{ $f->location }}</div>@endif
                            </div>
                        </div>
                    </td>
                    <td><span class="badge bg-secondary">{{ $f->category->name }}</span></td>
                    <td><span class="badge bg-{{ $f->type==='building'?'primary':'info' }}">{{ $f->type_label }}</span></td>
                    <td class="text-end">{{ number_format($f->total_stock) }} {{ $f->unit }}</td>
                    <td class="text-end">
                        @if(!$f->isBuilding() && $f->inventory)
                            <span class="{{ $f->inventory->qty_available <= $f->minimum_stock ? 'text-danger fw-bold' : 'text-success fw-bold' }}">
                                {{ number_format($f->inventory->qty_available) }}
                            </span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="small">
                        Rp {{ number_format($f->price,0,',','.') }}<br>
                        <span class="text-muted" style="font-size:.7rem;">{{ $f->price_type_label }}</span>
                    </td>
                    <td>
                        <span class="badge bg-{{ $f->is_active ? 'success' : 'secondary' }}">{{ $f->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        @if(!$f->is_bookable) <span class="badge bg-warning text-dark">Tidak Dapat Dipesan</span> @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.fasilitas.inventoryDetail', $f->id) }}" class="btn btn-sm btn-outline-info" title="Inventory"><i class="fas fa-boxes"></i></a>
                            <a href="{{ route('admin.fasilitas.edit', $f->id) }}" class="btn btn-sm btn-outline-warning" title="Edit"><i class="fas fa-edit"></i></a>
                            <button class="btn btn-sm btn-outline-danger btn-delete" data-id="{{ $f->id }}" data-name="{{ $f->name }}"><i class="fas fa-trash"></i></button>
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
$('.btn-delete').on('click', function() {
    const id = $(this).data('id'), name = $(this).data('name');
    Swal.fire({ title:`Hapus "${name}"?`, text:'Fasilitas akan dihapus (soft delete).', icon:'warning',
        showCancelButton:true, confirmButtonColor:'#dc3545', confirmButtonText:'Hapus' })
    .then(r => {
        if (r.isConfirmed) {
            $.ajax({ url: `/admin/fasilitas/${id}`, type: 'DELETE' })
            .done(res => { if (res.success) { Swal.fire({icon:'success',timer:1500,showConfirmButton:false}).then(()=>location.reload()); } });
        }
    });
});
</script>
@endpush

