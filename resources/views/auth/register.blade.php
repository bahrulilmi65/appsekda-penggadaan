<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - SIPF Sekretariat Daerah</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #1a3a6c 0%, #2d5fa5 100%); min-height: 100vh; padding: 40px 0; }
        .register-card { border-radius: 16px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,.3); }
    </style>
</head>
<body>
    <div class="container py-3">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="text-center mb-4">
                    <i class="fas fa-building-columns text-white" style="font-size:2rem;"></i>
                    <h5 class="text-white mt-2 fw-bold">SIPF Sekretariat Daerah</h5>
                </div>

                <div class="card register-card">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-1">Buat Akun Baru</h5>
                        <p class="text-muted small mb-4">Daftarkan diri Anda untuk dapat menyewa fasilitas</p>

                        @if($errors->any())
                            <div class="alert alert-danger py-2 small">
                                <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                            </div>
                        @endif

                        <form action="{{ route('register.post') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name') }}" placeholder="Nama lengkap sesuai KTP">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email') }}" placeholder="email@contoh.com">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">No. HP/Telepon <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                        value="{{ old('phone') }}" placeholder="08xxxxxxxxxx">
                                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Jenis Penyewa <span class="text-danger">*</span></label>
                                    <select name="type" class="form-select @error('type') is-invalid @enderror" id="typeSelect">
                                        <option value="">-- Pilih Jenis --</option>
                                        @foreach(\App\Models\Customer::TYPES as $key => $label)
                                            <option value="{{ $key }}" {{ old('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12" id="institutionField" style="display:none;">
                                    <label class="form-label small fw-semibold">Nama Instansi/Organisasi</label>
                                    <input type="text" name="institution_name" class="form-control"
                                        value="{{ old('institution_name') }}" placeholder="Nama instansi">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">NIK</label>
                                    <input type="text" name="nik" class="form-control" value="{{ old('nik') }}" placeholder="16 digit NIK" maxlength="16">
                                </div>

                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Alamat <span class="text-danger">*</span></label>
                                    <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2"
                                        placeholder="Alamat lengkap">{{ old('address') }}</textarea>
                                    @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Min. 8 karakter">
                                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Konfirmasi Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password">
                                </div>

                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input @error('agree') is-invalid @enderror" type="checkbox" name="agree" id="agree">
                                        <label class="form-check-label small" for="agree">
                                            Saya menyetujui <a href="#" class="text-primary">syarat dan ketentuan</a> penyewaan fasilitas
                                        </label>
                                        @error('agree') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                                        <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="text-center mt-3 small">
                            Sudah punya akun? <a href="{{ route('login') }}" class="text-primary fw-semibold">Login di sini</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('typeSelect').addEventListener('change', function() {
            const show = ['opd','instansi_pemerintah','perusahaan','organisasi'].includes(this.value);
            document.getElementById('institutionField').style.display = show ? 'block' : 'none';
        });
    </script>
</body>
</html>
