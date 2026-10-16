@extends('layouts.public')
@section('title', 'Verifikasi Invoice')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card border-0 shadow" style="border-radius:16px;">
                <div class="card-body p-4 text-center">
                    <div class="mb-3">
                        @if($invoice->status === 'paid')
                        <div class="bg-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:64px;height:64px;">
                            <i class="fas fa-check text-white fs-3"></i>
                        </div>
                        <h4 class="fw-bold text-success">Invoice Valid & Lunas</h4>
                        @else
                        <div class="bg-warning rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:64px;height:64px;">
                            <i class="fas fa-exclamation text-white fs-3"></i>
                        </div>
                        <h4 class="fw-bold text-warning">Invoice Valid – Belum Lunas</h4>
                        @endif
                    </div>

                    <div class="card bg-light border-0 mb-4">
                        <div class="card-body">
                            <div class="row g-3 text-start small">
                                <div class="col-md-6">
                                    <div class="text-muted">No. Invoice</div>
                                    <div class="fw-bold">{{ $invoice->invoice_number }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted">No. Booking</div>
                                    <div class="fw-bold">{{ $invoice->booking->booking_number }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted">Penyewa</div>
                                    <div class="fw-bold">{{ $invoice->customer->name }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted">Tanggal Invoice</div>
                                    <div class="fw-bold">{{ $invoice->invoice_date->format('d M Y') }}</div>
                                </div>
                                <div class="col-12">
                                    <div class="text-muted">Fasilitas</div>
                                    <div class="fw-bold">{{ $invoice->booking->items->map(fn($i)=>$i->facility->name)->join(', ') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted">Tanggal Penggunaan</div>
                                    <div class="fw-bold">{{ $invoice->booking->start_date->format('d M Y') }} – {{ $invoice->booking->end_date->format('d M Y') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted">Total</div>
                                    <div class="fw-bold fs-5 text-primary">Rp {{ number_format($invoice->total,0,',','.') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="text-muted small">Dokumen ini diverifikasi secara digital oleh Sekretariat Daerah.<br>Kode: <code>{{ $invoice->verification_token }}</code></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
