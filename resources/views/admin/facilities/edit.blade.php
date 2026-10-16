@extends('layouts.app')
@section('title', 'Edit Fasilitas')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.fasilitas.index') }}">Fasilitas</a></li>
    <li class="breadcrumb-item active">Edit: {{ $facility->name }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Edit Fasilitas: {{ $facility->name }}</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.fasilitas.inventoryDetail', $facility->id) }}" class="btn btn-outline-info btn-sm">
            <i class="fas fa-boxes me-1"></i>Inventory
        </a>
        <a href="{{ route('admin.fasilitas.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
    </div>
</div>

@if($errors->any())
<div class="alert alert-danger border-0 mb-4">
    <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form action="{{ route('admin.fasilitas.update', $facility->id) }}" method="POST">
    @csrf @method('PUT')
    <div class="row g-4">
        <div class="col-lg-8">
            {{-- Informasi Dasar --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold"><i class="fas fa-info-circle me-2 text-primary"></i>Informasi Dasar</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Kode Fasilitas <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                value="{{ old('code', $facility->code) }}" required>
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Nama Fasilitas <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $facility->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}" {{ old('category_id', $facility->category_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jenis <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" id="typeSelect" required>
                                @foreach(\App\Models\Facility::TYPES as $k=>$v)
                                    <option value="{{ $k }}" {{ old('type', $facility->type)===$k?'selected':'' }}>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $facility->description) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold"><i class="fas fa-boxes me-2 text-primary"></i>Stok & Harga</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Satuan <span class="text-danger">*</span></label>
                            <input type="text" name="unit" class="form-control" value="{{ old('unit', $facility->unit) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Total Stok <span class="text-danger">*</span></label>
                            <input type="number" name="total_stock" class="form-control" value="{{ old('total_stock', $facility->total_stock) }}" min="1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Stok Minimum</label>
                            <input type="number" name="minimum_stock" class="form-control" value="{{ old('minimum_stock', $facility->minimum_stock) }}" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Harga <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="price" class="form-control" value="{{ old('price', $facility->price) }}" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tipe Harga <span class="text-danger">*</span></label>
                            <select name="price_type" class="form-select" required>
                                @foreach(\App\Models\Facility::PRICE_TYPES as $k=>$v)
                                    <option value="{{ $k }}" {{ old('price_type', $facility->price_type)===$k?'selected':'' }}>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6" id="capacityField">
                            <label class="form-label fw-semibold">Kapasitas (orang)</label>
                            <input type="number" name="capacity" class="form-control" value="{{ old('capacity', $facility->capacity) }}" min="1">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold"><i class="fas fa-map-marker-alt me-2 text-primary"></i>Lokasi</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Lokasi</label>
                            <input type="text" name="location" class="form-control" value="{{ old('location', $facility->location) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Lantai</label>
                            <input type="text" name="floor" class="form-control" value="{{ old('floor', $facility->floor) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Gedung</label>
                            <input type="text" name="building" class="form-control" value="{{ old('building', $facility->building) }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold"><i class="fas fa-list me-2 text-primary"></i>Detail Tambahan</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Daftar Fasilitas (satu per baris)</label>
                        <textarea name="facilities_list" class="form-control" rows="4">{{ old('facilities_list', $facility->facilities_list) }}</textarea>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Syarat & Ketentuan</label>
                        <textarea name="terms_conditions" class="form-control" rows="3">{{ old('terms_conditions', $facility->terms_conditions) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Image management --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold"><i class="fas fa-images me-2 text-primary"></i>Foto Fasilitas</div>
                <div class="card-body">
                    @if($facility->images->count())
                    <div class="row g-2 mb-3" id="imageGallery">
                        @foreach($facility->images as $img)
                        <div class="col-md-3" id="img-{{ $img->id }}">
                            <div class="position-relative">
                                <img src="{{ $img->url }}" class="img-fluid rounded" style="height:100px; width:100%; object-fit:cover;">
                                <div class="position-absolute top-0 end-0 m-1 d-flex gap-1">
                                    @if(!$img->is_primary)
                                    <button type="button" class="btn btn-xs btn-warning btn-set-primary" data-id="{{ $img->id }}"
                                        style="font-size:.65rem; padding:2px 6px;" title="Set Utama">
                                        <i class="fas fa-star"></i>
                                    </button>
                                    @else
                                    <span class="badge bg-warning" style="font-size:.65rem;"><i class="fas fa-star"></i></span>
                                    @endif
                                    <button type="button" class="btn btn-xs btn-danger btn-del-img" data-id="{{ $img->id }}"
                                        style="font-size:.65rem; padding:2px 6px;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    <div>
                        <label class="form-label small fw-semibold">Upload Foto Baru</label>
                        <input type="file" id="imageUpload" class="form-control form-control-sm" accept="image/*" multiple>
                        <div class="form-text">Format: JPG, PNG, WebP. Maks 2MB per file.</div>
                        <button type="button" class="btn btn-sm btn-primary mt-2" id="btnUploadImages">
                            <i class="fas fa-upload me-1"></i>Upload
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold"><i class="fas fa-cog me-2 text-primary"></i>Pengaturan</div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Min. Jam Booking</label>
                            <input type="number" name="min_booking_hours" class="form-control" value="{{ old('min_booking_hours', $facility->min_booking_hours) }}" min="1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Maks. Hari</label>
                            <input type="number" name="max_booking_days" class="form-control" value="{{ old('max_booking_days', $facility->max_booking_days) }}" min="1">
                        </div>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_bookable" id="isBookable" value="1"
                            {{ old('is_bookable', $facility->is_bookable) ? 'checked' : '' }}>
                        <label class="form-check-label" for="isBookable">Dapat Dipesan</label>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="requires_approval" id="requiresApproval" value="1"
                            {{ old('requires_approval', $facility->requires_approval) ? 'checked' : '' }}>
                        <label class="form-check-label" for="requiresApproval">Perlu Persetujuan</label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1"
                            {{ old('is_active', $facility->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="isActive">Aktif</label>
                    </div>
                </div>
            </div>

            {{-- Inventory summary --}}
            @if($facility->inventory)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold"><i class="fas fa-boxes me-2 text-info"></i>Status Inventory</div>
                <div class="card-body small">
                    @php $inv = $facility->inventory; @endphp
                    @foreach([
                        ['Total','qty_total','secondary'],
                        ['Tersedia','qty_available','success'],
                        ['Dipesan','qty_reserved','warning'],
                        ['Laundry','qty_laundry','info'],
                        ['Rusak','qty_damaged','danger'],
                    ] as $r)
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">{{ $r[0] }}</span>
                        <span class="badge bg-{{ $r[2] }}">{{ $inv->{$r[1]} }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary py-2">
                    <i class="fas fa-save me-2"></i>Simpan Perubahan
                </button>
                <a href="{{ route('admin.fasilitas.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
const facilityId = {{ $facility->id }};

document.getElementById('typeSelect').addEventListener('change', function() {
    const isBuilding = this.value === 'building';
    document.getElementById('capacityField').style.display = isBuilding ? 'block' : 'none';
});
document.getElementById('typeSelect').dispatchEvent(new Event('change'));

// Upload images
document.getElementById('btnUploadImages').addEventListener('click', function() {
    const files = document.getElementById('imageUpload').files;
    if (!files.length) { Swal.fire({icon:'warning', text:'Pilih file gambar terlebih dahulu'}); return; }
    const fd = new FormData();
    for (const f of files) fd.append('images[]', f);
    fd.append('_token', '{{ csrf_token() }}');

    $.ajax({ url: `/admin/fasilitas/${facilityId}/images`, type: 'POST', data: fd, processData: false, contentType: false })
    .done(res => {
        if (res.success) { Swal.fire({icon:'success',text:'Foto berhasil diupload',timer:1200,showConfirmButton:false}).then(()=>location.reload()); }
    });
});

// Delete image
$(document).on('click', '.btn-del-img', function() {
    const id = $(this).data('id');
    Swal.fire({ title:'Hapus foto ini?', icon:'warning', showCancelButton:true, confirmButtonColor:'#dc3545' })
    .then(r => {
        if (r.isConfirmed) {
            $.ajax({ url: `/admin/fasilitas/images/${id}`, type: 'DELETE' })
            .done(res => { if (res.success) { $(`#img-${id}`).remove(); Swal.fire({icon:'success',timer:1000,showConfirmButton:false}); } });
        }
    });
});

// Set primary
$(document).on('click', '.btn-set-primary', function() {
    const id = $(this).data('id');
    $.post(`/admin/fasilitas/images/${id}/primary`)
    .done(res => { if (res.success) { Swal.fire({icon:'success',timer:1000,showConfirmButton:false}).then(()=>location.reload()); } });
});
</script>
@endpush
