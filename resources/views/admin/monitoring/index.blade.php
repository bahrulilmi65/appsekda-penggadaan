@extends('layouts.app')
@section('title', 'Monitoring Ketersediaan')
@section('breadcrumb')
    <li class="breadcrumb-item active">Monitoring</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Monitoring Ketersediaan Fasilitas</h4>
        <p class="text-muted small mb-0">Real-time status ketersediaan berdasarkan tanggal</p>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Pilih Tanggal</label>
                <input type="date" id="monitorDate" class="form-control" value="{{ $today }}">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100" id="btnMonitor">
                    <i class="fas fa-search me-1"></i>Tampilkan
                </button>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-secondary w-100" id="btnToday">Hari Ini</button>
            </div>
        </div>
    </div>
</div>

<div id="monitoringResult" class="d-none">

    {{-- Buildings --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">
            <i class="fas fa-building me-2 text-primary"></i>Gedung & Ruangan
            <span id="monitorDate2" class="text-muted ms-2 small"></span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 small" id="buildingTable">
                <thead class="table-light">
                    <tr><th>Gedung</th><th>Lokasi</th><th class="text-end">Kapasitas</th><th>Status</th></tr>
                </thead>
                <tbody id="buildingRows"></tbody>
            </table>
        </div>
    </div>

    {{-- Assets --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">
            <i class="fas fa-boxes me-2 text-info"></i>Peralatan & Perlengkapan
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 small" id="assetTable">
                <thead class="table-light">
                    <tr>
                        <th>Fasilitas</th>
                        <th>Kategori</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Dipesan</th>
                        <th class="text-end">Digunakan</th>
                        <th class="text-end">Laundry</th>
                        <th class="text-end">Rusak</th>
                        <th class="text-end">Hilang</th>
                        <th class="text-end fw-bold text-success">Tersedia</th>
                    </tr>
                </thead>
                <tbody id="assetRows"></tbody>
            </table>
        </div>
    </div>
</div>

<div id="monitoringEmpty" class="text-center py-5">
    <i class="fas fa-calendar-check text-muted mb-3 d-block" style="font-size:3rem; opacity:.3;"></i>
    <h5 class="text-muted">Pilih tanggal dan klik Tampilkan</h5>
</div>
@endsection

@push('scripts')
<script>
const dtOpts = {
    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json' },
    pageLength: 10,
    lengthChange: false,
};

function initMonitorTables() {
    ['#buildingTable', '#assetTable'].forEach(sel => {
        if ($.fn.DataTable.isDataTable(sel)) {
            $(sel).DataTable().destroy();
        }
        $(sel).DataTable(dtOpts);
    });
}

function loadMonitoring(date) {
    $.get('/admin/monitoring/data', { date }, function(data) {
        // Buildings
        let buildingHtml = '';
        data.buildings.forEach(b => {
            const statusClass = b.is_available ? 'success' : 'danger';
            const statusText = b.is_available ? 'TERSEDIA' : 'TERPESAN';
            buildingHtml += `<tr>
                <td class="fw-semibold">${b.name}</td>
                <td class="text-muted">${b.location || '-'}</td>
                <td class="text-end">${(b.capacity||0).toLocaleString('id')} orang</td>
                <td><span class="badge bg-${statusClass}">${statusText}</span></td>
            </tr>`;
        });
        document.getElementById('buildingRows').innerHTML = buildingHtml || '<tr><td colspan="4" class="text-center text-muted">Tidak ada data</td></tr>';

        // Assets
        let assetHtml = '';
        data.assets.forEach(a => {
            const availClass = a.qty_available <= 0 ? 'text-danger fw-bold' : (a.qty_available < a.qty_total * 0.2 ? 'text-warning fw-bold' : 'text-success fw-bold');
            assetHtml += `<tr>
                <td class="fw-semibold">${a.name} <span class="text-muted small">(${a.unit})</span></td>
                <td><span class="badge bg-secondary">${a.category}</span></td>
                <td class="text-end">${(a.qty_total||0).toLocaleString('id')}</td>
                <td class="text-end text-warning">${(a.qty_reserved||0).toLocaleString('id')}</td>
                <td class="text-end text-primary">${(a.qty_active||0).toLocaleString('id')}</td>
                <td class="text-end text-info">${(a.qty_laundry||0).toLocaleString('id')}</td>
                <td class="text-end text-danger">${(a.qty_damaged||0).toLocaleString('id')}</td>
                <td class="text-end text-dark">${(a.qty_lost||0).toLocaleString('id')}</td>
                <td class="text-end ${availClass}">${(a.qty_available||0).toLocaleString('id')}</td>
            </tr>`;
        });
        document.getElementById('assetRows').innerHTML = assetHtml || '<tr><td colspan="9" class="text-center text-muted">Tidak ada data</td></tr>';

        document.getElementById('monitorDate2').textContent = `— ${date}`;
        document.getElementById('monitoringResult').classList.remove('d-none');
        document.getElementById('monitoringEmpty').classList.add('d-none');
        initMonitorTables();
    });
}

document.getElementById('btnMonitor').addEventListener('click', function() {
    const date = document.getElementById('monitorDate').value;
    if (!date) { Swal.fire({icon:'warning',title:'Pilih tanggal terlebih dahulu'}); return; }
    loadMonitoring(date);
});

document.getElementById('btnToday').addEventListener('click', function() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('monitorDate').value = today;
    loadMonitoring(today);
});

// Load today on page load
loadMonitoring('{{ $today }}');
</script>
@endpush
