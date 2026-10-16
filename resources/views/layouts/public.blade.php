<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIPF Sekretariat Daerah')</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @stack('styles')
    <style>
        :root { --primary: #1a3a6c; --secondary: #e8a020; }
        body { font-family: 'Segoe UI', sans-serif; }

        .navbar-brand img { height: 40px; }
        .navbar { background: var(--primary) !important; }
        .navbar .nav-link { color: rgba(255,255,255,.85) !important; position: relative; padding-bottom: 8px; }
        .navbar .nav-link:hover { color: #fff !important; }
        .navbar .nav-link.active { color: #fff !important; font-weight: 600; }
        .navbar .nav-link.active::after {
            content: '';
            position: absolute;
            left: 12px; right: 12px; bottom: 2px;
            height: 3px; border-radius: 2px;
            background: var(--secondary);
        }

        .hero-section {
            background: linear-gradient(135deg, var(--primary) 0%, #2d5fa5 100%);
            color: white;
            padding: 80px 0;
        }
        .hero-section h1 { font-size: 2.5rem; font-weight: 700; }

        .facility-card { transition: transform .2s, box-shadow .2s; border: none; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .facility-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,.12); }
        .facility-card img { height: 200px; object-fit: cover; }

        .availability-badge { font-size: .75rem; padding: 4px 10px; border-radius: 20px; }

        .category-card { border-radius: 12px; padding: 20px; text-align: center; cursor: pointer; transition: all .2s; border: 2px solid transparent; }
        .category-card:hover { border-color: var(--primary); transform: translateY(-2px); }

        footer { background: var(--primary); color: rgba(255,255,255,.8); }
        footer a { color: rgba(255,255,255,.7); text-decoration: none; }
        footer a:hover { color: #fff; }

        .cart-badge { position: relative; }
        .cart-badge .badge { position: absolute; top: -8px; right: -8px; font-size: .6rem; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 text-white fw-bold" href="{{ route('home') }}">
                <i class="fas fa-building-columns fs-5"></i>
                <div>
                    <div style="font-size:.85rem; line-height:1;">SIPF</div>
                    <div style="font-size:.65rem; opacity:.7; font-weight:400;">Sekretariat Daerah</div>
                </div>
            </a>
            <button class="navbar-toggler border-0 text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <i class="fas fa-bars"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav ms-auto gap-1 align-items-lg-center">
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('catalog', 'facility.detail') ? 'active' : '' }}" href="{{ route('catalog') }}">Fasilitas</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('check.availability') ? 'active' : '' }}" href="{{ route('check.availability') }}">Cek Ketersediaan</a></li>

                    @auth
                        @if(auth()->user()->hasRole(['admin','superadmin','bendahara','petugas','pimpinan']))
                            <li class="nav-item">
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-warning btn-sm ms-2">
                                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard Admin
                                </a>
                            </li>
                        @else
                            <li class="nav-item cart-badge">
                                <a href="{{ route('user.cart.index') }}" class="nav-link">
                                    <i class="fas fa-shopping-cart"></i>
                                    @php $cnt = \App\Models\Cart::where('user_id',auth()->id())->count() @endphp
                                    @if($cnt) <span class="badge bg-danger">{{ $cnt }}</span> @endif
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                    <img src="{{ auth()->user()->avatar_url }}" class="rounded-circle" width="26" height="26">
                                    {{ Str::limit(auth()->user()->name, 15) }}
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ route('user.dashboard') }}">Dashboard</a></li>
                                    <li><a class="dropdown-item" href="{{ route('user.bookings.index') }}">Booking Saya</a></li>
                                    <li><a class="dropdown-item" href="{{ route('user.profile') }}">Profil</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('logout') }}" method="POST">
                                            @csrf
                                            <button class="dropdown-item text-danger">Logout</button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @endif
                    @else
                        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
                        <li class="nav-item">
                            <a href="{{ route('register') }}" class="btn btn-warning btn-sm ms-2">Daftar Sekarang</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    @yield('content')

    <!-- Footer -->
    <footer class="pt-5 pb-4 mt-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h6 class="text-white fw-bold mb-3"><i class="fas fa-building-columns me-2"></i>SIPF Sekretariat Daerah</h6>
                    <p class="small">Sistem Informasi Penyewaan Fasilitas untuk memudahkan pengelolaan dan peminjaman fasilitas Sekretariat Daerah.</p>
                </div>
                <div class="col-lg-2">
                    <h6 class="text-white fw-bold mb-3">Menu</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-1"><a href="{{ route('home') }}">Beranda</a></li>
                        <li class="mb-1"><a href="{{ route('catalog') }}">Katalog</a></li>
                        <li class="mb-1"><a href="{{ route('check.availability') }}">Ketersediaan</a></li>
                    </ul>
                </div>
                <div class="col-lg-3">
                    <h6 class="text-white fw-bold mb-3">Akun</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-1"><a href="{{ route('login') }}">Login</a></li>
                        <li class="mb-1"><a href="{{ route('register') }}">Daftar</a></li>
                    </ul>
                </div>
                <div class="col-lg-3">
                    <h6 class="text-white fw-bold mb-3">Kontak</h6>
                    <p class="small mb-1"><i class="fas fa-map-marker-alt me-2"></i>{{ \App\Models\Setting::get('organization_address','Jl. Ahmad Yani No.1') }}</p>
                    <p class="small mb-1"><i class="fas fa-phone me-2"></i>{{ \App\Models\Setting::get('organization_phone','-') }}</p>
                    <p class="small mb-0"><i class="fas fa-envelope me-2"></i>{{ \App\Models\Setting::get('organization_email','-') }}</p>
                </div>
            </div>
            <hr style="border-color:rgba(255,255,255,.2);">
            <div class="text-center small">
                &copy; {{ date('Y') }} Sekretariat Daerah. Hak Cipta Dilindungi.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    </script>
    @stack('scripts')
</body>
</html>
