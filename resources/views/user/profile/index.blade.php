@extends('layouts.app')
@section('title', 'Profil Saya')
@section('breadcrumb')
    <li class="breadcrumb-item active">Profil</li>
@endsection

@section('content')
<h4 class="fw-bold mb-4">Profil Saya</h4>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show border-0">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">
    {{-- Profile form --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="fas fa-user me-2 text-primary"></i>Data Pribadi & Penyewa
            </div>
            <div class="card-body">
                <form action="{{ route('user.profile.update') }}" method="POST">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $user->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" class="form-control bg-light" value="{{ $user->email }}" readonly>
                            <div class="form-text">Email tidak dapat diubah.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">No. Telepon</label>
                            <input type="text" name="phone" class="form-control"
                                value="{{ old('phone', $user->phone) }}" placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jenis Penyewa <span class="text-danger">*</span></label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                @foreach(\App\Models\Customer::TYPES as $k => $v)
                                    <option value="{{ $k }}" {{ old('type', $customer?->type) === $k ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                            </select>
                            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">NIK</label>
                            <input type="text" name="nik" class="form-control" maxlength="16"
                                value="{{ old('nik', $customer?->nik) }}" placeholder="16 digit NIK">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">NIP (jika ASN)</label>
                            <input type="text" name="nip" class="form-control"
                                value="{{ old('nip', $customer?->nip) }}" placeholder="NIP">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Nama Instansi/Organisasi</label>
                            <input type="text" name="institution_name" class="form-control"
                                value="{{ old('institution_name', $customer?->institution_name) }}"
                                placeholder="Kosongkan jika tidak ada">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Alamat</label>
                            <textarea name="address" class="form-control" rows="2"
                                placeholder="Alamat lengkap">{{ old('address', $customer?->address) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kota</label>
                            <input type="text" name="city" class="form-control"
                                value="{{ old('city', $customer?->city) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Provinsi</label>
                            <input type="text" name="province" class="form-control"
                                value="{{ old('province', $customer?->province) }}">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-4">
                        <i class="fas fa-save me-2"></i>Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>

        {{-- Change password --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="fas fa-lock me-2 text-warning"></i>Ubah Password
            </div>
            <div class="card-body">
                <form action="{{ route('user.profile.password') }}" method="POST">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Password Lama</label>
                            <input type="password" name="current_password"
                                class="form-control @error('current_password') is-invalid @enderror" required>
                            @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Password Baru</label>
                            <input type="password" name="password" class="form-control" required minlength="8">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-warning mt-3">
                        <i class="fas fa-key me-2"></i>Ubah Password
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Right: Status card --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm text-center mb-4">
            <div class="card-body py-4">
                <img src="{{ $user->avatar_url }}" class="rounded-circle mb-3" width="80" height="80">
                <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                <p class="text-muted small mb-2">{{ $user->email }}</p>
                <span class="badge bg-primary">{{ ucfirst($user->getRoleNames()->first() ?? 'User') }}</span>
            </div>
        </div>

        @if($customer)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="fas fa-id-card me-2 text-info"></i>Status Akun
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Kode Penyewa</span>
                    <code>{{ $customer->code }}</code>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Jenis</span>
                    <span class="fw-semibold small">{{ $customer->type_label }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Verifikasi</span>
                    <span class="badge bg-{{ $customer->verification_badge['color'] }}">{{ $customer->verification_badge['label'] }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Terdaftar</span>
                    <span class="small">{{ $customer->created_at?->format('d M Y') }}</span>
                </div>

                @if(!$customer->isVerified())
                <div class="alert alert-warning border-0 py-2 mt-3 small">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Akun Anda sedang menunggu verifikasi. Hubungi admin untuk mempercepat proses verifikasi.
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
