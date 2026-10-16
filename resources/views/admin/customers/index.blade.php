@extends('layouts.app')
@section('title', 'Manajemen Penyewa')
@section('breadcrumb')
    <li class="breadcrumb-item">Master Data</li>
    <li class="breadcrumb-item active">Penyewa</li>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Manajemen Penyewa</h4>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createCustomerModal">
        <i class="fas fa-plus me-1"></i>Tambah Penyewa
    </button>
</div>
<div class="card border-0 shadow-sm mb-4"><div class="card-body py-2">
    <form method="GET" class="d-flex gap-2 flex-wrap">
        <input type="text" name="search" class="form-control form-control-sm" style="width:200px;" placeholder="Cari nama/kode..." value="{{ request('search') }}">
        <select name="type" class="form-select form-select-sm" style="width:160px;"><option value="">Semua Jenis</option>
            @foreach(\App\Models\Customer::TYPES as $k=>$v)<option value="{{ $k }}" {{ request('type')===$k?'selected':'' }}>{{ $v }}</option>@endforeach
        </select>
        <select name="verification_status" class="form-select form-select-sm" style="width:160px;"><option value="">Semua Status</option>
            @foreach(\App\Models\Customer::VERIFICATION_STATUSES as $k=>$v)<option value="{{ $k }}" {{ request('verification_status')===$k?'selected':'' }}>{{ $v['label'] }}</option>@endforeach
        </select>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="{{ route('admin.penyewa.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
    </form>
</div></div>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 small">
            <thead class="table-light"><tr><th>Kode</th><th>Nama</th><th>Jenis</th><th>Instansi</th><th>Telepon</th><th>Verifikasi</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($customers as $c)
                <tr>
                    <td><code>{{ $c->code }}</code></td>
                    <td class="fw-semibold">{{ $c->name }}</td>
                    <td><span class="badge bg-secondary">{{ $c->type_label }}</span></td>
                    <td>{{ $c->institution_name ?? 'â€“' }}</td>
                    <td>{{ $c->phone ?? 'â€“' }}</td>
                    <td><span class="badge bg-{{ $c->verification_badge['color'] }}">{{ $c->verification_badge['label'] }}</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            @if($c->verification_status === 'pending')
                                <button class="btn btn-sm btn-success btn-verify-customer" data-id="{{ $c->id }}" title="Verifikasi"><i class="fas fa-check"></i></button>
                            @endif
                            <button class="btn btn-sm btn-outline-warning btn-edit-customer" data-id="{{ $c->id }}" title="Edit"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-outline-danger btn-delete-customer" data-id="{{ $c->id }}" data-name="{{ $c->name }}"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada data penyewa</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-transparent d-flex flex-column flex-sm-row gap-2 justify-content-sm-between align-items-sm-center">
        <small class="text-muted">Menampilkan {{ $customers->firstItem() ?? 0 }}–{{ $customers->lastItem() ?? 0 }} dari {{ $customers->total() }} data</small>
        <div>{{ $customers->links('pagination::bootstrap-5') }}</div>
    </div>
</div>

{{-- Create Modal --}}
<div class="modal fade" id="createCustomerModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title fw-bold">Tambah Penyewa</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><form id="createCustomerForm">@csrf
        <div class="mb-3"><label class="form-label small fw-semibold">Nama</label><input type="text" name="name" class="form-control" required></div>
        <div class="mb-3"><label class="form-label small fw-semibold">Jenis</label>
            <select name="type" class="form-select" required><option value="">Pilih jenis...</option>
                @foreach(\App\Models\Customer::TYPES as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
            </select></div>
        <div class="mb-3"><label class="form-label small fw-semibold">Instansi</label><input type="text" name="institution_name" class="form-control"></div>
        <div class="row g-2"><div class="col-md-6"><label class="form-label small fw-semibold">Telepon</label><input type="text" name="phone" class="form-control"></div>
        <div class="col-md-6"><label class="form-label small fw-semibold">Email</label><input type="email" name="email" class="form-control"></div></div>
        <div class="mt-2"><label class="form-label small fw-semibold">Alamat</label><textarea name="address" class="form-control" rows="2"></textarea></div>
    </form></div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="saveCustomer">Simpan</button></div>
</div></div></div>

{{-- Edit Modal --}}
<div class="modal fade" id="editCustomerModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title fw-bold">Edit Penyewa</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><form id="editCustomerForm">@csrf
        <input type="hidden" name="id" id="editId">
        <div class="mb-3"><label class="form-label small fw-semibold">Nama</label><input type="text" name="name" id="editName" class="form-control" required></div>
        <div class="mb-3"><label class="form-label small fw-semibold">Jenis</label>
            <select name="type" id="editType" class="form-select" required><option value="">Pilih jenis...</option>
                @foreach(\App\Models\Customer::TYPES as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
            </select></div>
        <div class="mb-3"><label class="form-label small fw-semibold">Instansi</label><input type="text" name="institution_name" id="editInstitution" class="form-control"></div>
        <div class="row g-2"><div class="col-md-6"><label class="form-label small fw-semibold">Telepon</label><input type="text" name="phone" id="editPhone" class="form-control"></div>
        <div class="col-md-6"><label class="form-label small fw-semibold">Email</label><input type="email" name="email" id="editEmail" class="form-control"></div></div>
        <div class="row g-2 mt-2"><div class="col-md-6"><label class="form-label small fw-semibold">NIK</label><input type="text" name="nik" id="editNik" class="form-control"></div>
        <div class="col-md-6"><label class="form-label small fw-semibold">NIP</label><input type="text" name="nip" id="editNip" class="form-control"></div></div>
        <div class="row g-2 mt-2"><div class="col-md-6"><label class="form-label small fw-semibold">Kota</label><input type="text" name="city" id="editCity" class="form-control"></div>
        <div class="col-md-6"><label class="form-label small fw-semibold">Provinsi</label><input type="text" name="province" id="editProvince" class="form-control"></div></div>
        <div class="mt-2"><label class="form-label small fw-semibold">Alamat</label><textarea name="address" id="editAddress" class="form-control" rows="2"></textarea></div>
        <div class="form-check mt-2"><input type="checkbox" name="is_active" id="editActive" value="1" class="form-check-input"><label class="form-check-label small" for="editActive">Aktif</label></div>
    </form></div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-warning" id="updateCustomer">Simpan Perubahan</button></div>
</div></div></div>
@endsection

@push('scripts')
<script>
$('#saveCustomer').on('click', () => {
    $.post('/admin/penyewa', $('#createCustomerForm').serialize())
    .done(res => { if (res.success) { bootstrap.Modal.getInstance(document.getElementById('createCustomerModal')).hide(); Swal.fire({icon:'success',timer:1500,showConfirmButton:false}).then(()=>location.reload()); } })
    .fail(xhr => Swal.fire({icon:'error',html:Object.values(xhr.responseJSON?.errors||{}).flat().join('<br>')}));
});

$('.btn-edit-customer').on('click', function() {
    const id = $(this).data('id');
    $.get(`/admin/penyewa/${id}/edit`)
    .done(res => {
        $('#editId').val(res.id);
        $('#editName').val(res.name);
        $('#editType').val(res.type);
        $('#editInstitution').val(res.institution_name);
        $('#editPhone').val(res.phone);
        $('#editEmail').val(res.email);
        $('#editNik').val(res.nik);
        $('#editNip').val(res.nip);
        $('#editCity').val(res.city);
        $('#editProvince').val(res.province);
        $('#editAddress').val(res.address);
        $('#editActive').prop('checked', !!res.is_active);
        new bootstrap.Modal(document.getElementById('editCustomerModal')).show();
    })
    .fail(() => Swal.fire({icon:'error', text:'Gagal memuat data penyewa.'}));
});

$('#updateCustomer').on('click', () => {
    const id = $('#editId').val();
    // is_active ditambah eksplisit agar uncheck terbaca (serialize melewatkan checkbox mati)
    const data = $('#editCustomerForm').serialize() + '&_method=PUT&is_active=' + ($('#editActive').is(':checked') ? 1 : 0);
    $.post(`/admin/penyewa/${id}`, data)
    .done(res => { if (res.success) { bootstrap.Modal.getInstance(document.getElementById('editCustomerModal')).hide(); Swal.fire({icon:'success',timer:1500,showConfirmButton:false}).then(()=>location.reload()); } })
    .fail(xhr => Swal.fire({icon:'error',html:Object.values(xhr.responseJSON?.errors||{}).flat().join('<br>')}));
});
$('.btn-verify-customer').on('click', function() {
    const id = $(this).data('id');
    Swal.fire({ title:'Verifikasi penyewa ini?', icon:'question', showCancelButton:true, confirmButtonText:'Verifikasi', confirmButtonColor:'#198754' })
    .then(r => { if (r.isConfirmed) { $.post(`/admin/penyewa/${id}/verify`, { action:'verify' }).done(res => { if (res.success) { Swal.fire({icon:'success',timer:1500,showConfirmButton:false}).then(()=>location.reload()); } }); } });
});

$('.btn-delete-customer').on('click', function() {
    const id = $(this).data('id'), name = $(this).data('name');
    Swal.fire({ title:`Hapus penyewa "${name}"?`, icon:'warning', showCancelButton:true, confirmButtonColor:'#dc3545' })
    .then(r => { if (r.isConfirmed) { $.ajax({url:`/admin/penyewa/${id}`,type:'DELETE'}).done(()=>location.reload()); } });
});
</script>
@endpush

