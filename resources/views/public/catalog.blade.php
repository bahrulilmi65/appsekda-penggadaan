@extends('layouts.public')
@section('title', 'Katalog Fasilitas')

@section('content')
<div class="py-4 bg-light border-bottom">
    <div class="container">
        <h4 class="fw-bold mb-1">Katalog Fasilitas</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
            <li class="breadcrumb-item active">Katalog</li>
        </ol></nav>
    </div>
</div>

<div class="container py-4">
    <div class="row g-4">
        {{-- Filter sidebar --}}
        <div class="col-lg-3">
            <div class="card sticky-top" style="top:80px;">
                <div class="card-header fw-semibold"><i class="fas fa-filter me-2"></i>Filter</div>
                <div class="card-body">
                    <form method="GET" action="{{ route('catalog') }}">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Cari Fasilitas</label>
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Nama fasilitas..." value="{{ request('search') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Kategori</label>
                            <select name="category" class="form-select form-select-sm">
                                <option value="">Semua Kategori</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Jenis</label>
                            <select name="type" class="form-select form-select-sm">
                                <option value="">Semua Jenis</option>
                                @foreach(\App\Models\Facility::TYPES as $key => $label)
                                    <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100">Terapkan Filter</button>
                        <a href="{{ route('catalog') }}" class="btn btn-outline-secondary btn-sm w-100 mt-2">Reset</a>
                    </form>
                </div>
            </div>
        </div>

        {{-- Facility grid --}}
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted small">Menampilkan {{ $facilities->total() }} fasilitas</span>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary active" onclick="toggleView('grid')"><i class="fas fa-th"></i></button>
                    <button class="btn btn-outline-secondary" onclick="toggleView('list')"><i class="fas fa-list"></i></button>
                </div>
            </div>

            <div id="facilityGrid" class="row g-3">
                @forelse($facilities as $facility)
                <div class="col-md-6 col-xl-4">
                    <div class="facility-card card h-100">
                        <a href="{{ route('facility.detail', $facility->slug) }}" class="position-relative">
                            <img src="{{ $facility->primary_image_url }}" class="card-img-top" alt="{{ $facility->name }}"
                                style="height:180px; object-fit:cover;"
                                onerror="this.src='https://placehold.co/400x180/1a3a6c/fff?text={{ urlencode($facility->name) }}'">
                            <span class="position-absolute top-0 end-0 m-2 badge bg-{{ $facility->type === 'building' ? 'primary' : 'info' }}">
                                {{ $facility->type_label }}
                            </span>
                        </a>
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-1">
                                <a href="{{ route('facility.detail', $facility->slug) }}" class="text-dark text-decoration-none">{{ $facility->name }}</a>
                            </h6>
                            <p class="text-muted mb-2 small">{{ $facility->category->name }}</p>

                            @if($facility->location)
                            <p class="text-muted mb-2" style="font-size:.78rem;"><i class="fas fa-map-marker-alt me-1 text-danger"></i>{{ $facility->location }}</p>
                            @endif

                            <div class="d-flex justify-content-between align-items-end">
                                <div>
                                    <div class="fw-bold text-primary">Rp {{ number_format($facility->price,0,',','.') }}</div>
                                    <div class="text-muted" style="font-size:.72rem;">{{ $facility->price_type_label }}</div>
                                </div>
                                @if($facility->isBuilding())
                                    <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:.7rem;">
                                        <i class="fas fa-users me-1"></i>{{ number_format($facility->capacity) }} org
                                    </span>
                                @elseif($facility->inventory)
                                    <div class="text-end">
                                        <div class="fw-semibold text-success small">{{ number_format($facility->inventory->qty_available) }} {{ $facility->unit }}</div>
                                        <div class="text-muted" style="font-size:.7rem;">tersedia</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-3">
                            <div class="d-flex gap-2">
                                <a href="{{ route('facility.detail', $facility->slug) }}" class="btn btn-outline-primary btn-sm flex-grow-1">Detail</a>
                                @auth
                                <button class="btn btn-primary btn-sm flex-grow-1 btn-add-cart"
                                    data-id="{{ $facility->id }}"
                                    data-name="{{ $facility->name }}"
                                    data-type="{{ $facility->type }}">
                                    <i class="fas fa-cart-plus"></i> Tambah
                                </button>
                                @else
                                <a href="{{ route('login') }}" class="btn btn-primary btn-sm flex-grow-1">Login untuk Pesan</a>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center py-5">
                    <i class="fas fa-search text-muted fs-1 mb-3 d-block"></i>
                    <h5 class="text-muted">Tidak ada fasilitas ditemukan</h5>
                    <p class="text-muted small">Coba ubah filter atau kata kunci pencarian</p>
                    <a href="{{ route('catalog') }}" class="btn btn-primary btn-sm">Reset Filter</a>
                </div>
                @endforelse
            </div>

            <div class="mt-4">{{ $facilities->links() }}</div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleView(type) {
    const grid = document.getElementById('facilityGrid');
    if (type === 'list') {
        grid.querySelectorAll('.col-md-6').forEach(c => { c.className = 'col-12'; });
    } else {
        grid.querySelectorAll('.col-12').forEach(c => { c.className = 'col-md-6 col-xl-4'; });
    }
}

// Quick add to cart
$('.btn-add-cart').on('click', function() {
    const id = $(this).data('id');
    const name = $(this).data('name');
    const type = $(this).data('type');

    if (type === 'building') {
        Swal.fire({ icon: 'info', title: 'Pilih Tanggal', text: 'Silakan buka halaman detail untuk melihat kalender ketersediaan dan melakukan pemesanan.', confirmButtonText: 'Buka Detail' })
            .then(r => { if (r.isConfirmed) window.location.href = `/fasilitas/${id}`; });
        return;
    }

    Swal.fire({
        title: `Tambah ke Keranjang`,
        html: `
            <p class="mb-3"><strong>${name}</strong></p>
            <div class="mb-2 text-start"><label class="form-label small">Jumlah</label>
            <input type="number" id="swalQty" class="form-control" value="1" min="1"></div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-cart-plus me-1"></i>Tambah',
        cancelButtonText: 'Batal',
        preConfirm: () => ({ quantity: parseInt(document.getElementById('swalQty').value) || 1 })
    }).then(r => {
        if (r.isConfirmed) {
            $.post('/user/keranjang', { facility_id: id, quantity: r.value.quantity })
            .done(res => {
                if (res.success) {
                    Swal.fire({ icon:'success', title:'Ditambahkan!', text: res.message, timer:1800, showConfirmButton:false });
                    // Update cart badge
                    const badge = document.querySelector('.cart-badge .badge');
                    if (badge) badge.textContent = res.cart_count;
                } else {
                    Swal.fire({ icon:'error', title:'Gagal', text: res.message });
                }
            })
            .fail(xhr => { Swal.fire({ icon:'error', title:'Error', text: xhr.responseJSON?.message || 'Terjadi kesalahan.' }); });
        }
    });
});
</script>
@endpush
