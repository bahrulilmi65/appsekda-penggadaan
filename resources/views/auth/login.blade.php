<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIPF Sekretariat Daerah</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #1a3a6c 0%, #2d5fa5 100%); min-height: 100vh; display: flex; align-items: center; }
        .login-card { border-radius: 16px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,.3); }
        .login-brand { font-size: 2rem; color: #1a3a6c; }
        .btn-login { background: #1a3a6c; border-color: #1a3a6c; font-weight: 600; letter-spacing: .5px; }
        .btn-login:hover { background: #2d5fa5; border-color: #2d5fa5; }
        .divider { position: relative; text-align: center; }
        .divider::before { content: ''; position: absolute; top: 50%; left: 0; right: 0; height: 1px; background: #dee2e6; }
        .divider span { background: #fff; padding: 0 12px; position: relative; color: #6c757d; font-size: .85rem; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="text-center mb-4">
                    <i class="fas fa-building-columns login-brand"></i>
                    <h5 class="text-white mt-2 fw-bold">SIPF Sekretariat Daerah</h5>
                    <p class="text-white-50 small">Sistem Informasi Penyewaan Fasilitas</p>
                </div>

                <div class="card login-card">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-1">Selamat Datang</h5>
                        <p class="text-muted small mb-4">Masuk ke akun Anda</p>

                        @if(session('error'))
                            <div class="alert alert-danger alert-sm py-2 small">
                                <i class="fas fa-exclamation-circle me-1"></i>{{ session('error') }}
                            </div>
                        @endif

                        <form action="{{ route('login.post') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope text-muted"></i></span>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email') }}" placeholder="contoh@email.com" required autofocus>
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                                    <input type="password" name="password" id="pwInput" class="form-control @error('password') is-invalid @enderror"
                                        placeholder="Password" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePw()">
                                        <i class="fas fa-eye" id="pwIcon"></i>
                                    </button>
                                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="mb-4 d-flex justify-content-between align-items-center">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                    <label class="form-check-label small" for="remember">Ingat saya</label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-login btn-primary w-100 py-2">
                                <i class="fas fa-sign-in-alt me-2"></i>Masuk
                            </button>
                        </form>

                        <div class="divider my-4"><span>atau</span></div>

                        <div class="text-center small">
                            Belum punya akun?
                            <a href="{{ route('register') }}" class="text-primary fw-semibold">Daftar Sekarang</a>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <a href="{{ route('home') }}" class="text-white-50 small">
                        <i class="fas fa-arrow-left me-1"></i>Kembali ke Beranda
                    </a>
                </div>

                {{-- Demo accounts --}}
                <div class="card mt-3 border-0 bg-white bg-opacity-10">
                    <div class="card-body p-3">
                        <p class="text-white small fw-semibold mb-2"><i class="fas fa-info-circle me-1"></i>Akun Demo:</p>
                        <div class="row g-1" style="font-size:.72rem;">
                            @foreach([
                                ['superadmin@sekda.go.id','Super Admin'],
                                ['admin@sekda.go.id','Admin'],
                                ['bendahara@sekda.go.id','Bendahara'],
                                ['petugas@sekda.go.id','Petugas'],
                                ['pimpinan@sekda.go.id','Pimpinan'],
                                ['user@example.com','Penyewa'],
                            ] as $acc)
                            <div class="col-6">
                                <a href="#" class="text-white-50 demo-login" data-email="{{ $acc[0] }}">
                                    {{ $acc[1] }}: <span class="text-white">klik</span>
                                </a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePw() {
            const input = document.getElementById('pwInput');
            const icon = document.getElementById('pwIcon');
            if (input.type === 'password') { input.type = 'text'; icon.classList.replace('fa-eye','fa-eye-slash'); }
            else { input.type = 'password'; icon.classList.replace('fa-eye-slash','fa-eye'); }
        }
        document.querySelectorAll('.demo-login').forEach(el => {
            el.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector('[name=email]').value = this.dataset.email;
                document.querySelector('[name=password]').value = 'password123';
            });
        });
    </script>
</body>
</html>
