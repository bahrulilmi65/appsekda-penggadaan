@extends('layouts.public')
@section('title', 'Cek Ketersediaan Fasilitas')

@section('content')
<div class="py-4 bg-light border-bottom">
    <div class="container">
        <h4 class="fw-bold mb-1">Cek Ketersediaan Fasilitas</h4>
        <p class="text-muted small mb-0">Cek fasilitas yang tersedia berdasarkan tanggal pemakaian</p>
    </div>
</div>

<div class="container py-4">
    {{-- Search form --}}
    <div class="card shadow-sm border-0 mb-4" style="border-radius:12px;">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('check.availability') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Tanggal Mulai</label>
                        <input type="date" name="start_date" class="form-control"
                            value="{{ $startDate }}" min="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Tanggal Selesai</label>
                        <input type="date" name="end_date" class="form-control"
                            value="{{ $endDate }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Kategori</label>
                        <select name="category" class="form-select">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="fas fa-search me-2"></i>Cek Ketersediaan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($facilities !== null)
    @if($startDate && $endDate)
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">
            Hasil untuk: <span class="text-primary">{{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM Y') }}</span>
            s/d <span class="text-primary">{{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM Y') }}</span>
        </h5>
        <span class="badge bg-primary">{{ $facilities->total() }} fasilitas</span>
    </div>

    {{-- Buildings --}}
    @php $buildings = $facilities->where('type','building'); $assets = $facilities->where('type','!=','building'); @endphp

    @if($buildings->count())
    <h6 class="fw-bold mb-3 mt-4"><i class="fas fa-building me-2 text-primary"></i>Gedung & Ruangan</h6>
    <div class="row g-3 mb-4">
        @foreach($buildings as $f)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm" style="border-radius:12px;">
                <div class="card-body p-3">
                    <div class="d-flex gap-3">
                        <img src="{{ $f->primary_image_url }}" class="rounded-3" style="width:70px;height:70px;object-fit:cover;"
                            onerror="this.src='https://placehold.co/70x70/1a3a6c/fff?text=G'">
                        <div class="flex-grow-1">
                            <div class="fw-bold mb-1">{{ $f->name }}</div>
                            <div class="text-muted small mb-2">
                                <i class="fas fa-users me-1"></i>{{ number_format($f->capacity) }} orang
                            </div>
                            @if($f->availability_status === 'available')
                                <span class="badge bg-success"><i class="fas fa-check me-1"></i>Tersedia</span>
                            @else
                                <span class="badge bg-danger"><i class="fas fa-times me-1"></i>Tidak Tersedia</span>
                            @endif
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                        <div class="text-primary fw-bold small">Rp {{ number_format($f->price,0,',','.') }} / hari</div>
                        @if($f->availability_status === 'available')
                            <a href="{{ route('facility.detail', $f->slug) }}" class="btn btn-sm btn-primary">Pesan</a>
                        @else
                            <span class="btn btn-sm btn-secondary disabled">Penuh</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Assets/Equipment --}}
    @if($assets->count())
    <h6 class="fw-bold mb-3 mt-2"><i class="fas fa-boxes me-2 text-info"></i>Peralatan & Perlengkapan</h6>
    <div class="card border-0 shadow-sm" style="border-radius:12px;">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fasilitas</th>
                        <th>Kategori</th>
                        <th class="text-end">Total Stok</th>
                        <th class="text-end">Tersedia</th>
                        <th class="text-end">Harga</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assets as $f)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $f->name }}</div>
                            <div class="text-muted small">{{ $f->unit }}</div>
                        </td>
                        <td><span class="badge bg-secondary">{{ $f->category->name }}</span></td>
                        <td class="text-end">{{ number_format($f->inventory?->qty_total ?? $f->total_stock) }}</td>
                        <td class="text-end">
                            @if($f->available_qty > 0)
                                <span class="text-success fw-bold">{{ number_format($f->available_qty) }}</span>
                            @else
                                <span class="text-danger fw-bold">0</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="fw-semibold">Rp {{ number_format($f->price,0,',','.') }}</div>
                            <div class="text-muted" style="font-size:.72rem;">{{ $f->price_type_label }}</div>
                        </td>
                        <td>
                            @if($f->available_qty > 0)
                                <a href="{{ route('facility.detail', $f->slug) }}" class="btn btn-sm btn-primary">Pesan</a>
                            @else
                                <span class="btn btn-sm btn-outline-secondary disabled">Habis</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="mt-4">{{ $facilities->links() }}</div>

    @if($facilities->count() === 0)
    <div class="text-center py-5">
        <i class="fas fa-calendar-times fs-1 text-muted mb-3 d-block"></i>
        <h5 class="text-muted">Tidak ada fasilitas tersedia</h5>
        <p class="text-muted small">Coba pilih tanggal berbeda</p>
    </div>
    @endif
    @endif

    @else
    <div class="text-center py-5">
        <i class="fas fa-calendar-search fs-1 text-muted mb-3 d-block" style="opacity:.3;"></i>
        <h5 class="text-muted">Masukkan tanggal untuk mengecek ketersediaan</h5>
        <p class="text-muted small">Pilih tanggal mulai dan selesai, kemudian klik "Cek Ketersediaan"</p>
    </div>
    @endif
</div>
@endsection
