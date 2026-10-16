@extends('layouts.app')
@section('title', 'Manajemen Laundry')
@section('breadcrumb')
    <li class="breadcrumb-item">Operasional</li>
    <li class="breadcrumb-item active">Laundry</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Manajemen Laundry</h4>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createLaundryModal">
        <i class="fas fa-plus me-1"></i>Catat Laundry
    </button>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" class="d-flex gap-2 flex-wrap">
            <select name="status" class="form-select form-select-sm" style="width:160px;">
                <option value="">Semua Status</option>
                @foreach(\App\Models\Laundry::STATUSES as $k=>$v)
                    <option value="{{ $k }}" {{ request('status')===$k?'selected':'' }}>{{ $v['label'] }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            <a href="{{ route('admin.laundry.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 small" id="laundryTable">
            <thead class="table-light">
                <tr><th>No. Laundry</th><th>Fasilitas</th><th class="text-end">Qty</th><th>Tgl Masuk</th><th>Est. Selesai</th><th>Tgl Selesai</th><th>Vendor</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($laundries as $l)
                <tr>
                    <td class="fw-semibold">{{ $l->laundry_number }}</td>
                    <td>{{ $l->facility->name }}</td>
                    <td class="text-end">{{ $l->quantity }} {{ $l->facility->unit }}</td>
                    <td>{{ $l->date_in->format('d M Y') }}</td>
                    <td>{{ $l->estimated_done?->format('d M Y') ?? '-' }}</td>
                    <td>{{ $l->date_done?->format('d M Y') ?? '-' }}</td>
                    <td>{{ $l->laundry_vendor ?? '-' }}</td>
                    <td><span class="badge bg-{{ $l->status_badge['color'] }}">{{ $l->status_badge['label'] }}</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            @if($l->status !== 'returned')
                                <button class="btn btn-sm btn-success btn-complete-laundry" data-id="{{ $l->id }}" data-number="{{ $l->laundry_number }}" title="Selesai"><i class="fas fa-check"></i> Selesai</button>
                            @endif
                            <button class="btn btn-sm btn-outline-danger btn-delete-laundry" data-id="{{ $l->id }}"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center py-4 text-muted">Tidak ada data laundry</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-transparent">{{ $laundries->links('pagination::bootstrap-5') }}</div>
</div>

{{-- Create Modal --}}
<div class="modal fade" id="createLaundryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-soap me-2"></i>Catat Laundry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="laundryForm">@csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Fasilitas <span class="text-danger">*</span></label>
                        <select name="facility_id" class="form-select" required>
                            <option value="">Pilih fasilitas...</option>
                            @foreach($facilities as $f)
                                <option value="{{ $f->id }}">{{ $f->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jumlah <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal Masuk</label>
                            <input type="date" name="date_in" class="form-control" value="{{ now()->toDateString() }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Est. Selesai</label>
                            <input type="date" name="estimated_done" class="form-control" value="{{ now()->addDays(3)->toDateString() }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Vendor</label>
                            <input type="text" name="laundry_vendor" class="form-control" placeholder="Nama vendor laundry">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Catatan</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="saveLaundry"><i class="fas fa-save me-1"></i>Simpan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#saveLaundry').on('click', function() {
    $.post('/admin/laundry', $('#laundryForm').serialize())
    .done(res => {
        if (res.success) {
            bootstrap.Modal.getInstance(document.getElementById('createLaundryModal')).hide();
            Swal.fire({icon:'success',text:res.message,timer:1500,showConfirmButton:false}).then(()=>location.reload());
        }
    })
    .fail(xhr => Swal.fire({icon:'error',title:'Error',html:xhr.responseJSON?.message||'Terjadi kesalahan'}));
});

$('.btn-complete-laundry').on('click', function() {
    const id = $(this).data('id'), number = $(this).data('number');
    Swal.fire({ title:`Selesaikan laundry ${number}?`, text:'Stok akan dikembalikan ke available.', icon:'question',
        showCancelButton:true, confirmButtonText:'Selesai', confirmButtonColor:'#198754' })
    .then(r => {
        if (r.isConfirmed) {
            $.post(`/admin/laundry/${id}/complete`).done(res => {
                if (res.success) { Swal.fire({icon:'success',text:res.message,timer:2000,showConfirmButton:false}).then(()=>location.reload()); }
            });
        }
    });
});

$('.btn-delete-laundry').on('click', function() {
    const id = $(this).data('id');
    Swal.fire({ title:'Hapus data laundry?', icon:'warning', showCancelButton:true, confirmButtonColor:'#dc3545' })
    .then(r => { if (r.isConfirmed) { $.ajax({url:`/admin/laundry/${id}`,type:'DELETE'}).done(()=>location.reload()); } });
});
</script>
@endpush

