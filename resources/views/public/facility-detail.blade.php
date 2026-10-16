@extends('layouts.public')
@section('title', $facility->name)

@section('content')
<div class="py-3 bg-light border-bottom">
    <div class="container">
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
            <li class="breadcrumb-item"><a href="{{ route('catalog') }}">Katalog</a></li>
            <li class="breadcrumb-item active">{{ $facility->name }}</li>
        </ol></nav>
    </div>
</div>

<div class="container py-4">
    <div class="row g-4">
        {{-- Left: Images + Details --}}
        <div class="col-lg-8">
            {{-- Image gallery --}}
            <div class="card mb-4 border-0 shadow-sm overflow-hidden" style="border-radius:12px;">
                @if($facility->images->count() > 0)
                    <div id="facilityCarousel" class="carousel slide">
                        <div class="carousel-inner">
                            @foreach($facility->images as $i => $img)
                            <div class="carousel-item {{ $i===0 ? 'active' : '' }}">
                                <img src="{{ $img->url }}" class="d-block w-100" style="height:400px; object-fit:cover;" alt="{{ $img->caption ?? $facility->name }}">
                            </div>
                            @endforeach
                        </div>
                        @if($facility->images->count() > 1)
                        <button class="carousel-control-prev" type="button" data-bs-target="#facilityCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#facilityCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                        @endif
                    </div>
                @else
                    <img src="https://placehold.co/800x400/1a3a6c/fff?text={{ urlencode($facility->name) }}"
                        class="w-100" style="height:400px; object-fit:cover;" alt="{{ $facility->name }}">
                @endif
            </div>

            {{-- Info --}}
            <div class="card shadow-sm border-0 mb-4" style="border-radius:12px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="flex-grow-1">
                            <div class="d-flex gap-2 mb-2">
                                <span class="badge bg-primary">{{ $facility->category->name }}</span>
                                <span class="badge bg-secondary">{{ $facility->type_label }}</span>
                            </div>
                            <h2 class="fw-bold mb-1">{{ $facility->name }}</h2>
                            @if($facility->location)
                            <p class="text-muted mb-0"><i class="fas fa-map-marker-alt me-1 text-danger"></i>{{ $facility->location }}
                                @if($facility->floor) &bull; {{ $facility->floor }} @endif
                            </p>
                            @endif
                        </div>
                        <div class="text-end">
                            <div class="fw-bold fs-4 text-primary">Rp {{ number_format($facility->price,0,',','.') }}</div>
                            <div class="text-muted small">{{ $facility->price_type_label }}</div>
                        </div>
                    </div>

                    <hr>

                    @if($facility->description)
                    <div class="mb-4">
                        <h6 class="fw-bold mb-2">Deskripsi</h6>
                        <p class="text-muted">{{ $facility->description }}</p>
                    </div>
                    @endif

                    <div class="row g-3 mb-4">
                        @if($facility->capacity)
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3">
                                <div class="text-muted small">Kapasitas</div>
                                <div class="fw-bold"><i class="fas fa-users me-1 text-primary"></i>{{ number_format($facility->capacity) }} Orang</div>
                            </div>
                        </div>
                        @endif
                        @if(!$facility->isBuilding())
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3">
                                <div class="text-muted small">Total Stok</div>
                                <div class="fw-bold"><i class="fas fa-boxes me-1 text-primary"></i>{{ number_format($facility->total_stock) }} {{ $facility->unit }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-success bg-opacity-10 rounded-3 border border-success border-opacity-25">
                                <div class="text-muted small">Tersedia Sekarang</div>
                                <div class="fw-bold text-success"><i class="fas fa-check-circle me-1"></i>
                                    {{ number_format($facility->inventory?->qty_available ?? 0) }} {{ $facility->unit }}
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3">
                                <div class="text-muted small">Satuan</div>
                                <div class="fw-bold">{{ $facility->unit }}</div>
                            </div>
                        </div>
                    </div>

                    @if($facility->facilities_list)
                    <div class="mb-4">
                        <h6 class="fw-bold mb-2">Fasilitas Tersedia</h6>
                        <div class="row g-2">
                            @foreach(explode("\n", $facility->facilities_list) as $item)
                            @if(trim($item))
                            <div class="col-md-6">
                                <div class="d-flex align-items-center gap-2 small">
                                    <i class="fas fa-check text-success"></i> {{ trim($item) }}
                                </div>
                            </div>
                            @endif
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($facility->terms_conditions)
                    <div class="alert alert-info border-0" style="border-radius:10px;">
                        <h6 class="fw-bold mb-2"><i class="fas fa-info-circle me-1"></i>Syarat & Ketentuan</h6>
                        <div class="small">{{ $facility->terms_conditions }}</div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Building Calendar --}}
            @if($facility->isBuilding())
            <div class="card shadow-sm border-0 mb-4" style="border-radius:12px;">
                <div class="card-header border-0 bg-white fw-bold">
                    <i class="fas fa-calendar me-2 text-primary"></i>Kalender Ketersediaan
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <button class="btn btn-sm btn-outline-secondary" id="prevMonth">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <h6 class="fw-bold mb-0" id="calendarTitle">Loading...</h6>
                        <button class="btn btn-sm btn-outline-secondary" id="nextMonth">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>

                    <div class="row g-1 mb-2 text-center">
                        @foreach(['Min','Sen','Sel','Rab','Kam','Jum','Sab'] as $d)
                        <div class="col" style="font-size:.75rem; font-weight:600; color:#6c757d;">{{ $d }}</div>
                        @endforeach
                    </div>
                    <div id="calendarGrid" class="row g-1"></div>

                    <div class="d-flex gap-4 mt-3 small">
                        <span><span class="d-inline-block rounded me-1" style="width:14px;height:14px;background:#198754;"></span>Tersedia</span>
                        <span><span class="d-inline-block rounded me-1" style="width:14px;height:14px;background:#dc3545;"></span>Terpesan</span>
                        <span><span class="d-inline-block rounded me-1" style="width:14px;height:14px;background:#adb5bd;"></span>Lewat</span>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Right: Booking Form --}}
        <div class="col-lg-4">
            <div class="card shadow border-0 sticky-top" style="top:80px; border-radius:12px;">
                <div class="card-header bg-primary text-white border-0 fw-bold" style="border-radius:12px 12px 0 0;">
                    <i class="fas fa-calendar-plus me-2"></i>Pesan Fasilitas
                </div>
                <div class="card-body p-3">
                    {{-- Availability checker --}}
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tanggal Mulai</label>
                        <input type="date" id="bookStartDate" class="form-control" min="{{ now()->addDay()->toDateString() }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tanggal Selesai</label>
                        <input type="date" id="bookEndDate" class="form-control">
                    </div>

                    @if($facility->isBuilding())
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Jam Mulai</label>
                            <input type="time" id="bookStartTime" class="form-control" value="08:00">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Jam Selesai</label>
                            <input type="time" id="bookEndTime" class="form-control" value="17:00">
                        </div>
                    </div>
                    @else
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Jumlah ({{ $facility->unit }})</label>
                        <input type="number" id="bookQty" class="form-control" value="1" min="1" max="{{ $facility->inventory?->qty_available ?? 1 }}">
                    </div>
                    @endif

                    <button class="btn btn-outline-primary btn-sm w-100 mb-3" id="checkAvailBtn">
                        <i class="fas fa-search me-1"></i>Cek Ketersediaan
                    </button>

                    <div id="availabilityResult" class="mb-3 d-none"></div>
                    <div id="priceEstimate" class="mb-3 d-none">
                        <div class="p-3 bg-light rounded-3">
                            <div class="small text-muted mb-1">Estimasi Biaya</div>
                            <div class="fw-bold fs-5 text-primary" id="priceTotal">-</div>
                            <div class="text-muted" style="font-size:.75rem;" id="priceDetail">-</div>
                        </div>
                    </div>

                    @auth
                    <button class="btn btn-primary w-100 py-2 fw-semibold" id="addToCartBtn" disabled>
                        <i class="fas fa-cart-plus me-2"></i>Tambah ke Keranjang
                    </button>
                    @else
                    <a href="{{ route('login') }}" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="fas fa-sign-in-alt me-2"></i>Login untuk Memesan
                    </a>
                    @endauth

                    <div class="mt-3 small text-muted">
                        <i class="fas fa-shield-alt text-success me-1"></i>Pemesanan aman &amp; terverifikasi
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Related --}}
    @if($relatedFacilities->count())
    <div class="mt-4">
        <h5 class="fw-bold mb-3">Fasilitas Terkait</h5>
        <div class="row g-3">
            @foreach($relatedFacilities as $rel)
            <div class="col-md-3">
                <div class="facility-card card">
                    <img src="{{ $rel->primary_image_url }}" class="card-img-top" style="height:120px;object-fit:cover;"
                        onerror="this.src='https://placehold.co/300x120/1a3a6c/fff?text={{ urlencode($rel->name) }}'">
                    <div class="card-body p-2">
                        <div class="fw-semibold small">{{ $rel->name }}</div>
                        <div class="text-primary small">Rp {{ number_format($rel->price,0,',','.') }}</div>
                        <a href="{{ route('facility.detail', $rel->slug) }}" class="btn btn-sm btn-outline-primary w-100 mt-2">Detail</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
const facilityId = {{ $facility->id }};
const facilityType = '{{ $facility->type }}';
const pricePerUnit = {{ $facility->price }};
const priceType = '{{ $facility->price_type }}';
const unit = '{{ $facility->unit }}';
let isAvailable = false;

// Calendar
@if($facility->isBuilding())
let calYear = {{ now()->year }}, calMonth = {{ now()->month }};
const months_id = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

function loadCalendar() {
    document.getElementById('calendarTitle').textContent = `${months_id[calMonth-1]} ${calYear}`;
    $.get(`/api/facility-calendar/${facilityId}`, { year: calYear, month: calMonth }, function(data) {
        const grid = document.getElementById('calendarGrid');
        grid.innerHTML = '';

        const firstDate = new Date(calYear, calMonth-1, 1);
        const firstDay = firstDate.getDay();

        // Empty cells before first day
        for (let i = 0; i < firstDay; i++) {
            grid.innerHTML += `<div class="col"></div>`;
        }

        Object.values(data).forEach(day => {
            let bg = day.is_past ? '#e9ecef' : (day.status === 'booked' ? '#dc3545' : '#198754');
            let color = day.is_past ? '#6c757d' : '#fff';
            grid.innerHTML += `<div class="col text-center">
                <div class="rounded-2 py-1" style="background:${bg}; color:${color}; font-size:.8rem; cursor:default;" title="${day.status === 'booked' ? 'Terpesan' : 'Tersedia'}">${day.day}</div>
            </div>`;
        });
    });
}
loadCalendar();
document.getElementById('prevMonth').addEventListener('click', () => { if (calMonth === 1) { calMonth = 12; calYear--; } else calMonth--; loadCalendar(); });
document.getElementById('nextMonth').addEventListener('click', () => { if (calMonth === 12) { calMonth = 1; calYear++; } else calMonth++; loadCalendar(); });
@endif

// Availability check
document.getElementById('checkAvailBtn').addEventListener('click', function() {
    const startDate = document.getElementById('bookStartDate').value;
    const endDate = document.getElementById('bookEndDate').value;
    const qty = document.getElementById('bookQty')?.value || 1;

    if (!startDate || !endDate) {
        Swal.fire({ icon:'warning', title:'Lengkapi Tanggal', text:'Pilih tanggal mulai dan selesai terlebih dahulu.' });
        return;
    }

    const params = { facility_id: facilityId, start_date: startDate, end_date: endDate, quantity: qty };
    @if($facility->isBuilding())
    params.start_time = document.getElementById('bookStartTime').value;
    params.end_time = document.getElementById('bookEndTime').value;
    @endif

    $.get('/api/availability', params).done(res => {
        const div = document.getElementById('availabilityResult');
        div.classList.remove('d-none');
        isAvailable = res.available;

        if (res.available) {
            div.innerHTML = `<div class="alert alert-success py-2 small mb-0"><i class="fas fa-check-circle me-1"></i>${res.message}</div>`;
            document.getElementById('addToCartBtn')?.removeAttribute('disabled');

            // Show price estimate
            const start = new Date(startDate), end = new Date(endDate);
            const days = Math.ceil((end - start) / 86400000) + 1;
            let total = 0, detail = '';
            if (priceType === 'per_day') { total = pricePerUnit * days; detail = `Rp ${pricePerUnit.toLocaleString('id')} x ${days} hari`; }
            else if (priceType === 'per_item') { total = pricePerUnit * parseInt(qty) * days; detail = `Rp ${pricePerUnit.toLocaleString('id')} x ${qty} ${unit} x ${days} hari`; }
            else if (priceType === 'per_hour') {
                const st = document.getElementById('bookStartTime')?.value || '08:00';
                const et = document.getElementById('bookEndTime')?.value || '17:00';
                const hours = (new Date('2000-01-01 '+et) - new Date('2000-01-01 '+st)) / 3600000;
                total = pricePerUnit * hours * days; detail = `Rp ${pricePerUnit.toLocaleString('id')} x ${hours} jam x ${days} hari`;
            } else { total = pricePerUnit; detail = `Rp ${pricePerUnit.toLocaleString('id')} per event`; }

            document.getElementById('priceEstimate').classList.remove('d-none');
            document.getElementById('priceTotal').textContent = `Rp ${total.toLocaleString('id')}`;
            document.getElementById('priceDetail').textContent = detail;
        } else {
            div.innerHTML = `<div class="alert alert-danger py-2 small mb-0"><i class="fas fa-times-circle me-1"></i>${res.message}</div>`;
            document.getElementById('addToCartBtn')?.setAttribute('disabled', true);
            document.getElementById('priceEstimate').classList.add('d-none');
        }
    });
});

// Add to cart
document.getElementById('addToCartBtn')?.addEventListener('click', function() {
    if (!isAvailable) return;
    const startDate = document.getElementById('bookStartDate').value;
    const endDate = document.getElementById('bookEndDate').value;
    const qty = document.getElementById('bookQty')?.value || 1;

    $.post('/user/keranjang', {
        facility_id: facilityId,
        quantity: qty,
        start_date: startDate,
        end_date: endDate,
        start_time: document.getElementById('bookStartTime')?.value,
        end_time: document.getElementById('bookEndTime')?.value,
    }).done(res => {
        if (res.success) {
            Swal.fire({
                icon: 'success', title: 'Ditambahkan!', text: res.message,
                confirmButtonText: 'Ke Keranjang', showCancelButton: true, cancelButtonText: 'Lanjut Belanja'
            }).then(r => { if (r.isConfirmed) window.location.href = '/user/keranjang'; });
        } else {
            Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
        }
    });
});
</script>
@endpush
