@extends('layouts.app')
@section('title', 'Tambah Fasilitas')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.fasilitas.index') }}">Fasilitas</a></li>
    <li class="breadcrumb-item active">Tambah</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Tambah Fasilitas Baru</h4>
    <a href="{{ route('admin.fasilitas.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
</div>

@if($errors->any())
<div class="alert alert-danger border-0 mb-4">
    <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form action="{{ route('admin.fasilitas.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold"><i class="fas fa-info-circle me-2 text-primary"></i>Informasi Dasar</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Kode Fasilitas <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                value="{{ old('code') }}" placeholder="GDG-001" required>
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Nama Fasilitas <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                <option value="">Pilih kategori...</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}" {{ old('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jenis <span class="text-danger">*</span></label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror" id="typeSelect" required>
                                @foreach(\App\Models\Facility::TYPES as $k=>$v)
                                    <option value="{{ $k }}" {{ old('type')===$k?'selected':'' }}>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
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
                            <input type="text" name="unit" class="form-control" value="{{ old('unit','pcs') }}" placeholder="pcs, unit, set..." required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Total Stok <span class="text-danger">*</span></label>
                            <input type="number" name="total_stock" class="form-control" value="{{ old('total_stock',1) }}" min="1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Stok Minimum</label>
                            <input type="number" name="minimum_stock" class="form-control" value="{{ old('minimum_stock',0) }}" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Harga <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="price" class="form-control" value="{{ old('price',0) }}" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tipe Harga <span class="text-danger">*</span></label>
                            <select name="price_type" class="form-select" required>
                                @foreach(\App\Models\Facility::PRICE_TYPES as $k=>$v)
                                    <option value="{{ $k }}" {{ old('price_type')===$k?'selected':'' }}>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 building-field" id="capacityField">
                            <label class="form-label fw-semibold">Kapasitas (orang)</label>
                            <input type="number" name="capacity" class="form-control" value="{{ old('capacity') }}" min="1">
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
                            <input type="text" name="location" class="form-control" value="{{ old('location') }}" placeholder="Gedung Utama Sekretariat Daerah">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Lantai</label>
                            <input type="text" name="floor" class="form-control" value="{{ old('floor') }}" placeholder="Lantai 1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Gedung</label>
                            <input type="text" name="building" class="form-control" value="{{ old('building') }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold"><i class="fas fa-list me-2 text-primary"></i>Detail Tambahan</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Daftar Fasilitas (satu per baris)</label>
                        <textarea name="facilities_list" class="form-control" rows="4"
                            placeholder="AC Central&#10;Sound System&#10;Kursi&#10;Meja">{{ old('facilities_list') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Syarat & Ketentuan</label>
                        <textarea name="terms_conditions" class="form-control" rows="3">{{ old('terms_conditions') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold"><i class="fas fa-cog me-2 text-primary"></i>Pengaturan</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Min. Jam Booking</label>
                            <input type="number" name="min_booking_hours" class="form-control" value="{{ old('min_booking_hours',1) }}" min="1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Maks. Hari Booking</label>
                            <input type="number" name="max_booking_days" class="form-control" value="{{ old('max_booking_days',30) }}" min="1">
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="is_bookable" id="isBookable" value="1" {{ old('is_bookable',1) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isBookable">Dapat Dipesan</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="requires_approval" id="requiresApproval" value="1" {{ old('requires_approval',1) ? 'checked' : '' }}>
                            <label class="form-check-label" for="requiresApproval">Perlu Persetujuan</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" {{ old('is_active',1) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isActive">Aktif</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary py-2">
                    <i class="fas fa-save me-2"></i>Simpan Fasilitas
                </button>
                <a href="{{ route('admin.fasilitas.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.getElementById('typeSelect').addEventListener('change', function() {
    const isBuilding = this.value === 'building';
    document.getElementById('capacityField').style.display = isBuilding ? 'block' : 'none';
});
document.getElementById('typeSelect').dispatchEvent(new Event('change'));
</script>
@endpush
