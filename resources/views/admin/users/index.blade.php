@extends('layouts.app')
@section('title', 'Manajemen Pengguna')
@section('breadcrumb')
    <li class="breadcrumb-item">Sistem</li>
    <li class="breadcrumb-item active">Pengguna</li>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Manajemen Pengguna</h4>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createUserModal">
        <i class="fas fa-plus me-1"></i>Tambah Pengguna
    </button>
</div>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 small" id="userTable">
            <thead class="table-light"><tr><th>Nama</th><th>Email</th><th>Role</th><th>Status</th><th>Terdaftar</th><th>Aksi</th></tr></thead>
            <tbody>
                @foreach($users as $u)
                <tr>
                    <td><div class="d-flex align-items-center gap-2">
                        <img src="{{ $u->avatar_url }}" class="rounded-circle" width="30" height="30">
                        <span class="fw-semibold">{{ $u->name }}</span>
                    </div></td>
                    <td>{{ $u->email }}</td>
                    <td><span class="badge bg-primary">{{ ucfirst($u->getRoleNames()->first() ?? '-') }}</span></td>
                    <td><span class="badge bg-{{ $u->is_active?'success':'secondary' }}">{{ $u->is_active?'Aktif':'Nonaktif' }}</span></td>
                    <td>{{ $u->created_at->format('d M Y') }}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-warning btn-edit-user" data-id="{{ $u->id }}"><i class="fas fa-edit"></i></button>
                        @if($u->id !== auth()->id())
                        <button class="btn btn-sm btn-outline-danger btn-delete-user" data-id="{{ $u->id }}" data-name="{{ $u->name }}"><i class="fas fa-trash"></i></button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-transparent">{{ $users->links() }}</div>
</div>

{{-- Create Modal --}}
<div class="modal fade" id="createUserModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title fw-bold">Tambah Pengguna</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><form id="createUserForm">@csrf
        <div class="mb-3"><label class="form-label small fw-semibold">Nama</label><input type="text" name="name" class="form-control" required></div>
        <div class="mb-3"><label class="form-label small fw-semibold">Email</label><input type="email" name="email" class="form-control" required></div>
        <div class="mb-3"><label class="form-label small fw-semibold">Telepon</label><input type="text" name="phone" class="form-control"></div>
        <div class="mb-3"><label class="form-label small fw-semibold">Role</label>
            <select name="role" class="form-select" required>
                @foreach($roles as $r)<option value="{{ $r->name }}">{{ ucfirst($r->name) }}</option>@endforeach
            </select></div>
        <div class="mb-3"><label class="form-label small fw-semibold">Password</label><input type="password" name="password" class="form-control" required></div>
        <div class="mb-3"><label class="form-label small fw-semibold">Konfirmasi Password</label><input type="password" name="password_confirmation" class="form-control" required></div>
    </form></div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="button" class="btn btn-primary" id="saveUser">Simpan</button></div>
</div></div></div>

{{-- Edit Modal --}}
<div class="modal fade" id="editUserModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title fw-bold">Edit Pengguna</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><form id="editUserForm">@csrf @method('PUT')
        <input type="hidden" id="editUserId">
        <div class="mb-3"><label class="form-label small fw-semibold">Nama</label><input type="text" id="euName" name="name" class="form-control" required></div>
        <div class="mb-3"><label class="form-label small fw-semibold">Email</label><input type="email" id="euEmail" name="email" class="form-control" required></div>
        <div class="mb-3"><label class="form-label small fw-semibold">Telepon</label><input type="text" id="euPhone" name="phone" class="form-control"></div>
        <div class="mb-3"><label class="form-label small fw-semibold">Role</label>
            <select id="euRole" name="role" class="form-select" required>
                @foreach($roles as $r)<option value="{{ $r->name }}">{{ ucfirst($r->name) }}</option>@endforeach
            </select></div>
        <div class="mb-3"><label class="form-label small fw-semibold">Password Baru (kosongkan jika tidak diubah)</label><input type="password" name="password" class="form-control"></div>
        <div class="mb-3"><label class="form-label small fw-semibold">Konfirmasi Password</label><input type="password" name="password_confirmation" class="form-control"></div>
        <div class="form-check"><input class="form-check-input" type="checkbox" id="euActive" name="is_active" value="1"><label class="form-check-label small" for="euActive">Aktif</label></div>
    </form></div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="button" class="btn btn-warning" id="updateUser">Perbarui</button></div>
</div></div></div>
@endsection

@push('scripts')
<script>
$('#userTable').DataTable({ language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json' } });

$('#saveUser').on('click', () => {
    $.post('/admin/users', $('#createUserForm').serialize())
    .done(res => { if (res.success) { bootstrap.Modal.getInstance(document.getElementById('createUserModal')).hide(); Swal.fire({icon:'success',timer:1500,showConfirmButton:false}).then(()=>location.reload()); } })
    .fail(xhr => Swal.fire({icon:'error',html:Object.values(xhr.responseJSON?.errors||{}).flat().join('<br>')}));
});

$('.btn-edit-user').on('click', function() {
    const id = $(this).data('id');
    $.get(`/admin/users/${id}/edit`).done(r => {
        $('#editUserId').val(r.user.id); $('#euName').val(r.user.name); $('#euEmail').val(r.user.email);
        $('#euPhone').val(r.user.phone); $('#euRole').val(r.role); $('#euActive').prop('checked', r.user.is_active);
        new bootstrap.Modal(document.getElementById('editUserModal')).show();
    });
});

$('#updateUser').on('click', () => {
    const id = $('#editUserId').val();
    $.ajax({ url: `/admin/users/${id}`, type: 'PUT', data: $('#editUserForm').serialize() })
    .done(res => { if (res.success) { bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide(); Swal.fire({icon:'success',timer:1500,showConfirmButton:false}).then(()=>location.reload()); } });
});

$('.btn-delete-user').on('click', function() {
    const id = $(this).data('id'), name = $(this).data('name');
    Swal.fire({ title:`Hapus user "${name}"?`, icon:'warning', showCancelButton:true, confirmButtonColor:'#dc3545' })
    .then(r => { if (r.isConfirmed) { $.ajax({url:`/admin/users/${id}`,type:'DELETE'}).done(res=>{ if(res.success) { Swal.fire({icon:'success',timer:1500,showConfirmButton:false}).then(()=>location.reload()); } else { Swal.fire({icon:'error',text:res.message}); } }); } });
});
</script>
@endpush
