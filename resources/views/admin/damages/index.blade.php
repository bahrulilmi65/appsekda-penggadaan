@extends('layouts.app')
@section('title', 'Manajemen Kerusakan')
@section('breadcrumb')
    <li class="breadcrumb-item">Operasional</li>
    <li class="breadcrumb-item active">Kerusakan</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Manajemen Kerusakan & Kehilangan</h4>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createDamageModal">
        <i class="fas fa-plus me-1"></i>Laporkan Kerusakan
    </button>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" class="d-flex gap-2 flex-wrap">
            <select name="status" class="form-select form-select-sm" style="width:160px;">
                <option value="">Semua Status</option>
                @foreach(\App\Models\Damage::STATUSES as $k=>$v)
                    <option value="{{ $k }}" {{ request('status')===$k?'selected':'' }}>{{ $v['label'] }}</option>
                @endforeach
            </select>
            <select name="type" class="form-select form-select-sm" style="width:140px;">
                <option value="">Semua Jenis</option>
                <option value="damage" {{ request('type')==='damage'?'selected':'' }}>Rusak</option>
                <option value="loss" {{ request('type')==='loss'?'selected':'' }}>Hilang</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 small">
            <thead class="table-light">
                <tr><th>No.</th><th>Fasilitas</th><th>Jenis</th><th class="text-end">Qty</th><th>Deskripsi</th><th>Tgl Lapor</th><th>Biaya</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($damages as $d)
                <tr>
                    <td><code>{{ $d->damage_number }}</code></td>
                    <td>{{ $d->facility->name }}</td>
                    <td><span class="badge bg-{{ $d->damage_type==='damage'?'danger':'dark' }}">{{ $d->damage_type==='damage'?'Rusak':'Hilang' }}</span></td>
                    <td class="text-end">{{ $d->quantity }}</td>
                    <td>{{ Str::limit($d->description,50) }}</td>
                    <td>{{ $d->reported_date->format('d M Y') }}</td>
                    <td>{{ $d->repair_cost > 0 ? 'Rp '.number_format($d->repair_cost,0,',','.') : '-' }}</td>
                    <td><span class="badge bg-{{ $d->status_badge['color'] }}">{{ $d->status_badge['label'] }}</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            @if($d->status !== 'repaired' && $d->damage_type === 'damage')
                                <button class="btn btn-sm btn-success btn-repair" data-id="{{ $d->id }}" title="Selesai Diperbaiki"><i class="fas fa-wrench"></i></button>
                            @endif
                            <button class="btn btn-sm btn-outline-danger btn-delete-damage" data-id="{{ $d->id }}"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center py-4 text-muted">Tidak ada data kerusakan</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-transparent">{{ $damages->links('pagination::bootstrap-5') }}</div>
</div>

{{-- Create Modal --}}
<div class="modal fade" id="createDamageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-tools me-2"></i>Laporkan Kerusakan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="damageForm" enctype="multipart/form-data">@csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Fasilitas <span class="text-danger">*</span></label>
                            <select name="facility_id" class="form-select" required>
                                <option value="">Pilih fasilitas...</option>
                                @foreach($facilities as $f)
                                    <option value="{{ $f->id }}">{{ $f->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jenis</label>
                            <select name="damage_type" class="form-select">
                                <option value="damage">Rusak</option>
                                <option value="loss">Hilang</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jumlah <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control" min="1" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Deskripsi <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Biaya Perbaikan</label>
                            <input type="number" name="repair_cost" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal Lapor</label>
                            <input type="date" name="reported_date" class="form-control" value="{{ now()->toDateString() }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Foto</label>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="saveDamage"><i class="fas fa-save me-1"></i>Laporkan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#saveDamage').on('click', function() {
    const formData = new FormData(document.getElementById('damageForm'));
    $.ajax({ url: '/admin/kerusakan', type: 'POST', data: formData, processData: false, contentType: false })
    .done(res => {
        if (res.success) {
            bootstrap.Modal.getInstance(document.getElementById('createDamageModal')).hide();
            Swal.fire({icon:'success',text:res.message,timer:1500,showConfirmButton:false}).then(()=>location.reload());
        }
    });
});

$('.btn-repair').on('click', function() {
    const id = $(this).data('id');
    Swal.fire({ title:'Tandai sebagai selesai diperbaiki?', icon:'question', showCancelButton:true, confirmButtonText:'Selesai', confirmButtonColor:'#198754' })
    .then(r => {
        if (r.isConfirmed) {
            $.ajax({ url: `/admin/kerusakan/${id}`, type: 'PUT', data: { status: 'repaired', repaired_date: new Date().toISOString().split('T')[0] } })
            .done(res => { if (res.success) { Swal.fire({icon:'success',timer:1500,showConfirmButton:false}).then(()=>location.reload()); } });
        }
    });
});

$('.btn-delete-damage').on('click', function() {
    const id = $(this).data('id');
    Swal.fire({ title:'Hapus data kerusakan?', icon:'warning', showCancelButton:true, confirmButtonColor:'#dc3545' })
    .then(r => { if (r.isConfirmed) { $.ajax({url:`/admin/kerusakan/${id}`,type:'DELETE'}).done(()=>location.reload()); } });
});
</script>
@endpush

