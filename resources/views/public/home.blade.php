@extends('layouts.public')
@section('title', 'Beranda - SIPF Sekretariat Daerah')

@section('content')
{{-- Hero --}}
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 class="fw-bold mb-3">Kelola & Pesan Fasilitas<br>Sekretariat Daerah dengan Mudah</h1>
                <p class="lead opacity-75 mb-4">Platform digital untuk pemesanan gedung, peralatan, dan perlengkapan Sekretariat Daerah secara transparan dan efisien.</p>

                {{-- Quick availability check --}}
                <div class="card border-0 shadow-lg p-3" style="border-radius:12px; max-width:550px;">
                    <form action="{{ route('check.availability') }}" method="GET">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-dark mb-1">Tanggal Mulai</label>
                                <input type="date" name="start_date" class="form-control form-control-sm" min="{{ now()->addDay()->toDateString() }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-dark mb-1">Tanggal Selesai</label>
                                <input type="date" name="end_date" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="fas fa-search me-1"></i>Cek Ketersediaan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-flex justify-content-end">
                <div class="row g-2">
                    @foreach([['fas fa-building','Gedung','4 unit'],['fas fa-chair','Furniture','1200+ pcs'],['fas fa-volume-up','Elektronik','25+ unit'],['fas fa-soap','Perlengkapan','1500+ pcs']] as $item)
                    <div class="col-6">
                        <div class="card border-0 bg-white bg-opacity-10 text-white p-3 text-center" style="border-radius:12px;">
                            <i class="fas {{ $item[0] }} fs-3 mb-2"></i>
                            <div class="fw-semibold small">{{ $item[1] }}</div>
                            <div style="font-size:.75rem; opacity:.7;">{{ $item[2] }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Categories --}}
<section class="py-5">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="fw-bold">Kategori Fasilitas</h2>
            <p class="text-muted">Pilih fasilitas sesuai kebutuhan Anda</p>
        </div>
        <div class="row g-3 justify-content-center">
            @foreach($categories as $cat)
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('catalog', ['category' => $cat->id]) }}" class="text-decoration-none">
                    <div class="category-card shadow-sm">
                        <div class="mb-2" style="font-size:2rem; color:{{ $cat->color }};">
                            <i class="fas {{ $cat->icon }}"></i>
                        </div>
                        <div class="fw-semibold small text-dark">{{ $cat->name }}</div>
                        <div class="text-muted" style="font-size:.72rem;">{{ $cat->facilities_count }} fasilitas</div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Featured Facilities --}}
<section class="py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h2 class="fw-bold mb-1">Fasilitas Unggulan</h2>
                <p class="text-muted mb-0">Fasilitas tersedia untuk Anda gunakan</p>
            </div>
            <a href="{{ route('catalog') }}" class="btn btn-outline-primary btn-sm">Lihat Semua</a>
        </div>
        <div class="row g-3">
            @foreach($featuredFacilities as $facility)
            <div class="col-md-6 col-lg-3">
                <div class="facility-card card h-100">
                    <a href="{{ route('facility.detail', $facility->slug) }}" class="text-decoration-none">
                        <img src="{{ $facility->primary_image_url }}" class="card-img-top" alt="{{ $facility->name }}"
                            onerror="this.src='https://placehold.co/400x200/1a3a6c/ffffff?text={{ urlencode($facility->name) }}'">
                    </a>
                    <div class="card-body p-3">
                        <span class="badge bg-{{ $facility->type === 'building' ? 'primary' : 'secondary' }} mb-2" style="font-size:.7rem;">
                            {{ $facility->type_label }}
                        </span>
                        <h6 class="fw-bold mb-1">
                            <a href="{{ route('facility.detail', $facility->slug) }}" class="text-dark text-decoration-none">{{ $facility->name }}</a>
                        </h6>
                        @if($facility->location)
                        <p class="text-muted mb-2" style="font-size:.78rem;"><i class="fas fa-map-marker-alt me-1"></i>{{ $facility->location }}</p>
                        @endif

                        @if($facility->isBuilding())
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold text-primary">Rp {{ number_format($facility->price,0,',','.') }}</div>
                                <div class="text-muted" style="font-size:.72rem;">{{ $facility->price_type_label }}</div>
                            </div>
                            <div class="text-muted small"><i class="fas fa-users me-1"></i>{{ number_format($facility->capacity) }} org</div>
                        </div>
                        @else
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold text-primary">Rp {{ number_format($facility->price,0,',','.') }}</div>
                                <div class="text-muted" style="font-size:.72rem;">{{ $facility->price_type_label }}</div>
                            </div>
                            @if($facility->inventory)
                            <div class="text-end" style="font-size:.75rem;">
                                <div class="text-success fw-semibold">{{ number_format($facility->inventory->qty_available) }}</div>
                                <div class="text-muted">tersedia</div>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                    <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-3">
                        <a href="{{ route('facility.detail', $facility->slug) }}" class="btn btn-primary btn-sm w-100">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- How it works --}}
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Cara Pemesanan</h2>
            <p class="text-muted">Mudah, cepat, dan transparan</p>
        </div>
        <div class="row g-4 text-center">
            @foreach([
                ['1','fas fa-search','Cari Fasilitas','Cari dan pilih fasilitas yang Anda butuhkan dari katalog kami','primary'],
                ['2','fas fa-calendar-check','Cek Ketersediaan','Pilih tanggal dan cek ketersediaan fasilitas secara real-time','success'],
                ['3','fas fa-shopping-cart','Buat Pemesanan','Masukkan ke keranjang, isi data pemesanan, dan submit','warning'],
                ['4','fas fa-check-circle','Konfirmasi & Bayar','Tunggu persetujuan admin, lalu lakukan pembayaran','info'],
            ] as $step)
            <div class="col-md-3">
                <div class="position-relative">
                    <div class="bg-{{ $step[4] }} bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:64px;height:64px;">
                        <i class="fas {{ $step[1] }} fs-4 text-{{ $step[4] }}"></i>
                    </div>
                    <span class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-{{ $step[4] }}">{{ $step[0] }}</span>
                </div>
                <h5 class="fw-bold">{{ $step[2] }}</h5>
                <p class="text-muted small">{{ $step[3] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
@guest
<section class="py-5" style="background: linear-gradient(135deg, #1a3a6c, #2d5fa5);">
    <div class="container text-center text-white">
        <h2 class="fw-bold mb-3">Mulai Pesan Fasilitas Sekarang</h2>
        <p class="opacity-75 mb-4">Daftar gratis dan nikmati kemudahan pemesanan fasilitas Sekretariat Daerah</p>
        <a href="{{ route('register') }}" class="btn btn-warning btn-lg px-5">
            <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
        </a>
        <span class="mx-3 opacity-50">atau</span>
        <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg px-4">Login</a>
    </div>
</section>
@endguest
@endsection
