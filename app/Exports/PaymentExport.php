<?php

namespace App\Exports;

use App\Models\Payment;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PaymentExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(protected array $filters = []) {}

    public function collection(): Collection
    {
        $query = Payment::with(['booking.customer'])->latest();

        if (!empty($this->filters['date_from'])) {
            $query->where('payment_date', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->where('payment_date', '<=', $this->filters['date_to']);
        }
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No. Pembayaran', 'No. Booking', 'Penyewa',
            'Total Tagihan', 'Total Bayar', 'Metode',
            'Tgl Bayar', 'Status', 'Diverifikasi',
        ];
    }

    public function map($payment): array
    {
        return [
            $payment->payment_number,
            $payment->booking->booking_number,
            $payment->booking->customer->name,
            'Rp ' . number_format($payment->amount_due, 0, ',', '.'),
            'Rp ' . number_format($payment->amount_paid, 0, ',', '.'),
            $payment->payment_method,
            $payment->payment_date?->format('d/m/Y'),
            $payment->status_badge['label'],
            $payment->verifiedBy?->name ?? '-',
        ];
    }
}
