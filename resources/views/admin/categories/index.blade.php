@extends('layouts.app')
@section('title', 'Kategori Fasilitas')
@section('breadcrumb')
    <li class="breadcrumb-item">Master Data</li>
    <li class="breadcrumb-item active">Kategori</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Kategori Fasilitas</h4>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
        <i class="fas fa-plus me-1"></i>Tambah Kategori
    </button>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 dataTable" id="categoryTable">
            <thead class="table-light">
                <tr>
                    <th>Kode</th><th>Nama</th><th>Ikon</th><th>Fasilitas</th><th>Urutan</th><th>Status</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $cat)
                <tr>
                    <td><code>{{ $cat->code }}</code></td>
                    <td>
                        <span class="me-2" style="color:{{ $cat->color }};"><i class="fas {{ $cat->icon }}"></i></span>
                        <strong>{{ $cat->name }}</strong>
                        @if($cat->description)<div class="text-muted small">{{ Str::limit($cat->description, 50) }}</div>@endif
                    </td>
                    <td><code>{{ $cat->icon }}</code></td>
                    <td><span class="badge bg-secondary">{{ $cat->facilities_count }}</span></td>
                    <td>{{ $cat->sort_order }}</td>
                    <td>
                        <span class="badge bg-{{ $cat->is_active ? 'success' : 'secondary' }}">
                            {{ $cat->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-warning btn-edit" data-id="{{ $cat->id }}" title="Edit"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-outline-danger btn-delete" data-id="{{ $cat->id }}" data-name="{{ $cat->name }}" title="Hapus"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Create Modal --}}
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-plus me-2"></i>Tambah Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createForm">
                    @csrf
                    <div class="mb-3"><label class="form-label fw-semibold">Kode <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" placeholder="GDG, FRN, dll." maxlength="20" required></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Nama <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="2"></textarea></div>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label fw-semibold">Ikon Font Awesome</label>
                            <input type="text" name="icon" class="form-control" placeholder="fa-building" value="fa-box"></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Warna</label>
                            <input type="color" name="color" class="form-control form-control-color" value="#3498db"></div>
                    </div>
                    <div class="mt-3"><label class="form-label fw-semibold">Urutan Tampil</label>
                        <input type="number" name="sort_order" class="form-control" value="0" min="0"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnCreate"><i class="fas fa-save me-1"></i>Simpan</button>
            </div>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Edit Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    @csrf @method('PUT')
                    <input type="hidden" id="editId">
                    <div class="mb-3"><label class="form-label fw-semibold">Kode</label>
                        <input type="text" name="code" id="editCode" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Nama</label>
                        <input type="text" name="name" id="editName" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Deskripsi</label>
                        <textarea name="description" id="editDescription" class="form-control" rows="2"></textarea></div>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label fw-semibold">Ikon</label>
                            <input type="text" name="icon" id="editIcon" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Warna</label>
                            <input type="color" name="color" id="editColor" class="form-control form-control-color"></div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-md-6"><label class="form-label fw-semibold">Urutan</label>
                            <input type="number" name="sort_order" id="editSortOrder" class="form-control"></div>
                        <div class="col-md-6 d-flex align-items-end pb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="editIsActive" value="1">
                                <label class="form-check-label" for="editIsActive">Aktif</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning" id="btnUpdate"><i class="fas fa-save me-1"></i>Perbarui</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#categoryTable').DataTable({ language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json' }, pageLength: 25 });

// Create
$('#btnCreate').on('click', function() {
    $.post('/admin/kategori', $('#createForm').serialize())
    .done(res => {
        if (res.success) {
            bootstrap.Modal.getInstance(document.getElementById('createModal')).hide();
            Swal.fire({icon:'success',title:'Berhasil',text:res.message,timer:1500,showConfirmButton:false}).then(()=>location.reload());
        }
    })
    .fail(xhr => Swal.fire({icon:'error',title:'Error',html: Object.values(xhr.responseJSON?.errors||{}).flat().join('<br>')}));
});

// Edit - Load data
$('.btn-edit').on('click', function() {
    const id = $(this).data('id');
    $.get(`/admin/kategori/${id}/edit`).done(cat => {
        $('#editId').val(cat.id);
        $('#editCode').val(cat.code);
        $('#editName').val(cat.name);
        $('#editDescription').val(cat.description);
        $('#editIcon').val(cat.icon);
        $('#editColor').val(cat.color || '#3498db');
        $('#editSortOrder').val(cat.sort_order);
        $('#editIsActive').prop('checked', cat.is_active);
        new bootstrap.Modal(document.getElementById('editModal')).show();
    });
});

// Update
$('#btnUpdate').on('click', function() {
    const id = $('#editId').val();
    $.ajax({ url: `/admin/kategori/${id}`, type: 'PUT', data: $('#editForm').serialize() })
    .done(res => {
        if (res.success) {
            bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
            Swal.fire({icon:'success',title:'Berhasil',text:res.message,timer:1500,showConfirmButton:false}).then(()=>location.reload());
        }
    })
    .fail(xhr => Swal.fire({icon:'error',title:'Error',html: Object.values(xhr.responseJSON?.errors||{}).flat().join('<br>')}));
});

// Delete
$('.btn-delete').on('click', function() {
    const id = $(this).data('id'), name = $(this).data('name');
    Swal.fire({ title:`Hapus "${name}"?`, text:'Data yang dihapus tidak dapat dikembalikan.', icon:'warning',
        showCancelButton:true, confirmButtonColor:'#dc3545', confirmButtonText:'Hapus', cancelButtonText:'Batal' })
    .then(r => {
        if (r.isConfirmed) {
            $.ajax({ url: `/admin/kategori/${id}`, type: 'DELETE' })
            .done(res => {
                if (res.success) { Swal.fire({icon:'success',timer:1500,showConfirmButton:false}).then(()=>location.reload()); }
                else { Swal.fire({icon:'error',title:'Gagal',text:res.message}); }
            });
        }
    });
});
</script>
@endpush
