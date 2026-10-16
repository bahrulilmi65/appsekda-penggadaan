@extends('layouts.app')
@section('title', 'Laporan')
@section('breadcrumb')
    <li class="breadcrumb-item active">Laporan</li>
@endsection

@section('content')
<h4 class="fw-bold mb-4">Modul Laporan</h4>
<div class="row g-3">
    @foreach([
        ['Laporan Booking','fa-calendar-check','Rekap semua booking','admin.report.booking','primary'],
        ['Laporan Pembayaran','fa-money-bill','Rekap pembayaran & pendapatan','admin.report.payment','success'],
        ['Laporan Inventory','fa-boxes','Status stok semua fasilitas','admin.report.inventory','info'],
        ['Laporan Fasilitas','fa-building','Penggunaan per fasilitas','admin.report.facility','warning'],
        ['Laporan Kerusakan','fa-tools','Kerusakan & kehilangan','admin.report.damage','danger'],
        ['Laporan Laundry','fa-soap','Riwayat laundry','admin.report.laundry','secondary'],
        ['Laporan Penyewa','fa-users','Data penyewa','admin.report.customer','primary'],
    ] as $r)
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex gap-3 align-items-center">
                <div class="stat-icon bg-{{ $r[4] }} bg-opacity-10 text-{{ $r[4] }}" style="width:52px;height:52px;border-radius:12px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas {{ $r[1] }} fs-4"></i>
                </div>
                <div>
                    <div class="fw-bold">{{ $r[0] }}</div>
                    <div class="text-muted small">{{ $r[2] }}</div>
                    <a href="{{ route($r[3]) }}" class="btn btn-sm btn-{{ $r[4] }} mt-2">Buka Laporan</a>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
