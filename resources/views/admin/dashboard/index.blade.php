@extends('layouts.app')
@section('title', 'Dashboard Admin')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Dashboard</h4>
        <p class="text-muted small mb-0">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
    </div>
    <a href="{{ route('admin.monitoring.index') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-tv me-1"></i>Monitoring Ketersediaan
    </a>
</div>

{{-- Stats Cards --}}
<div class="row g-3 mb-4">
    @php
    $cards = [
        ['label'=>'Total Fasilitas','value'=>$stats['total_facilities'],'icon'=>'fa-building','bg'=>'primary','sub'=>'fasilitas aktif'],
        ['label'=>'Total Booking','value'=>$stats['total_bookings'],'icon'=>'fa-calendar-check','bg'=>'info','sub'=>'semua booking'],
        ['label'=>'Booking Aktif','value'=>$stats['active_bookings'],'icon'=>'fa-spinner','bg'=>'warning','sub'=>'sedang berjalan'],
        ['label'=>'Menunggu Verifikasi','value'=>$stats['waiting_payment'],'icon'=>'fa-money-bill-wave','bg'=>'danger','sub'=>'pembayaran'],
        ['label'=>'Pendapatan Bulan Ini','value'=>'Rp '.number_format($stats['revenue_month'],0,',','.'),'icon'=>'fa-chart-line','bg'=>'success','sub'=>date('F Y')],
        ['label'=>'Sedang Digunakan','value'=>$stats['in_use'],'icon'=>'fa-users','bg'=>'primary','sub'=>'booking aktif'],
        ['label'=>'Dalam Pengantaran','value'=>$stats['in_delivery'],'icon'=>'fa-truck','bg'=>'info','sub'=>'dalam perjalanan'],
        ['label'=>'Sedang Laundry','value'=>$stats['in_laundry'].' pcs','icon'=>'fa-soap','bg'=>'secondary','sub'=>'perlu proses'],
    ];
    @endphp
    @foreach($cards as $card)
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-{{ $card['bg'] }} bg-opacity-10 text-{{ $card['bg'] }}">
                    <i class="fas {{ $card['icon'] }}"></i>
                </div>
                <div>
                    <div class="fw-bold fs-5">{{ $card['value'] }}</div>
                    <div class="small text-muted">{{ $card['label'] }}</div>
                    <div style="font-size:.7rem;" class="text-muted">{{ $card['sub'] }}</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    {{-- Booking Chart --}}
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-chart-bar me-2 text-primary"></i>Booking & Pendapatan (12 Bulan)</span>
            </div>
            <div class="card-body">
                <canvas id="bookingChart" height="80"></canvas>
            </div>
        </div>
    </div>

    {{-- Status Pie --}}
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header"><i class="fas fa-chart-pie me-2 text-primary"></i>Status Booking</div>
            <div class="card-body d-flex flex-column justify-content-center">
                <canvas id="statusChart" height="220"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- Pending Approvals --}}
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-clock me-2 text-warning"></i>Menunggu Persetujuan</span>
                <a href="{{ route('admin.booking.index', ['status'=>'waiting_approval']) }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                @forelse($pendingApprovals as $booking)
                <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
                    <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center" style="width:38px;height:38px;min-width:38px;">
                        <i class="fas fa-calendar-check text-warning small"></i>
                    </div>
                    <div class="flex-grow-1 min-width-0">
                        <div class="fw-semibold small">{{ $booking->booking_number }}</div>
                        <div class="text-muted" style="font-size:.78rem;">{{ $booking->customer->name }} &bull; {{ $booking->start_date->format('d M Y') }}</div>
                    </div>
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-success btn-approve" data-id="{{ $booking->id }}" title="Setujui"><i class="fas fa-check"></i></button>
                        <button class="btn btn-sm btn-danger btn-reject" data-id="{{ $booking->id }}" title="Tolak"><i class="fas fa-times"></i></button>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4 small"><i class="fas fa-check-circle fs-4 text-success d-block mb-2"></i>Tidak ada booking menunggu persetujuan</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Waiting Payment Verification --}}
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-money-bill me-2 text-danger"></i>Menunggu Verifikasi Pembayaran</span>
                <a href="{{ route('admin.payment.index', ['status'=>'waiting_verification']) }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                @forelse($waitingVerification as $payment)
                <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
                    <div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center" style="width:38px;height:38px;min-width:38px;">
                        <i class="fas fa-receipt text-danger small"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold small">{{ $payment->payment_number }}</div>
                        <div class="text-muted" style="font-size:.78rem;">{{ $payment->booking->customer->name }} &bull; <span class="text-success fw-semibold">{{ 'Rp '.number_format($payment->amount_paid,0,',','.') }}</span></div>
                    </div>
                    <a href="{{ route('admin.payment.show', $payment->id) }}" class="btn btn-sm btn-primary">Verifikasi</a>
                </div>
                @empty
                <div class="text-center text-muted py-4 small"><i class="fas fa-check-circle fs-4 text-success d-block mb-2"></i>Tidak ada pembayaran menunggu verifikasi</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Recent Bookings + Top Facilities --}}
<div class="row g-3">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-list me-2 text-primary"></i>Booking Terbaru</span>
                <a href="{{ route('admin.booking.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 small">
                    <thead class="table-light">
                        <tr><th>No. Booking</th><th>Penyewa</th><th>Tanggal</th><th>Total</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse($recentBookings as $b)
                        <tr>
                            <td><a href="{{ route('admin.booking.show', $b->id) }}" class="text-decoration-none fw-semibold">{{ $b->booking_number }}</a></td>
                            <td>{{ $b->customer->name }}</td>
                            <td>{{ $b->start_date->format('d M Y') }}</td>
                            <td>{{ 'Rp '.number_format($b->total_amount,0,',','.') }}</td>
                            <td><span class="badge bg-{{ $b->status_badge['color'] }}">{{ $b->status_badge['label'] }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Belum ada booking</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-header"><i class="fas fa-trophy me-2 text-warning"></i>Fasilitas Terpopuler</div>
            <div class="card-body p-0">
                @forelse($topFacilities as $i => $item)
                <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
                    <div class="fw-bold text-muted" style="width:20px;">{{ $i+1 }}</div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold small">{{ $item->facility->name ?? 'N/A' }}</div>
                        <div class="text-muted" style="font-size:.75rem;">{{ $item->booking_count }} booking &bull; Rp {{ number_format($item->total_revenue,0,',','.') }}</div>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4 small">Belum ada data</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Prepare chart labels (last 12 months)
const months = [];
const bookingData = [];
const revenueData = [];

@php
$bookingMap = $bookingChart->keyBy(fn($r) => $r->year.'-'.str_pad($r->month,2,'0',STR_PAD_LEFT));
$revenueMap = $revenueChart->keyBy(fn($r) => $r->year.'-'.str_pad($r->month,2,'0',STR_PAD_LEFT));
$labels = [];
for ($i = 11; $i >= 0; $i--) {
    $d = now()->subMonths($i);
    $key = $d->format('Y-m');
    $labels[$key] = $d->isoFormat('MMM YY');
}
@endphp

@foreach($labels as $key => $label)
months.push('{{ $label }}');
bookingData.push({{ $bookingMap[$key]->total ?? 0 }});
revenueData.push({{ $revenueMap[$key]->total ?? 0 }});
@endforeach

new Chart(document.getElementById('bookingChart'), {
    type: 'bar',
    data: {
        labels: months,
        datasets: [
            { label: 'Booking', data: bookingData, backgroundColor: 'rgba(13,110,253,.7)', borderRadius: 4 },
            { label: 'Pendapatan (Rp)', data: revenueData, backgroundColor: 'rgba(25,135,84,.7)', borderRadius: 4, yAxisID: 'y2' },
        ]
    },
    options: {
        responsive: true, plugins: { legend: { position: 'top' } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } },
            y2: { position: 'right', beginAtZero: true, grid: { drawOnChartArea: false } }
        }
    }
});

// Status pie chart
const statusLabels = @json($bookingStatus->pluck('status'));
const statusData   = @json($bookingStatus->pluck('count'));
const statusColors = ['#6c757d','#ffc107','#0dcaf0','#198754','#dc3545','#ffc107','#0d6efd','#0dcaf0','#0d6efd','#198754','#dc3545','#6c757d','#e83e8c'];
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: { labels: statusLabels, datasets: [{ data: statusData, backgroundColor: statusColors.slice(0, statusData.length), borderWidth: 2 }] },
    options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } }, cutout: '60%' }
});

// Quick approve/reject
$('.btn-approve').on('click', function() {
    const id = $(this).data('id');
    Swal.fire({ title: 'Setujui booking ini?', icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, Setujui', confirmButtonColor: '#198754' })
    .then(r => {
        if (r.isConfirmed) {
            $.post(`/admin/booking/${id}/approve`, {}, function(res) {
                if (res.success) { Swal.fire({ icon:'success', title:'Berhasil', text: res.message, timer:1500, showConfirmButton:false }).then(() => location.reload()); }
            });
        }
    });
});
$('.btn-reject').on('click', function() {
    const id = $(this).data('id');
    Swal.fire({ title: 'Tolak booking ini?', input: 'textarea', inputLabel: 'Alasan penolakan', inputPlaceholder: 'Masukkan alasan...', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Tolak' })
    .then(r => {
        if (r.isConfirmed && r.value) {
            $.post(`/admin/booking/${id}/reject`, { reason: r.value }, function(res) {
                if (res.success) { Swal.fire({ icon:'success', timer:1500, showConfirmButton:false }).then(() => location.reload()); }
            });
        }
    });
});
</script>
@endpush
