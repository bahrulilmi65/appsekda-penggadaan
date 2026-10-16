<?php

namespace App\Exports;

use App\Models\Booking;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BookingExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(protected array $filters = []) {}

    public function collection(): Collection
    {
        $query = Booking::with(['customer', 'items.facility'])->latest();

        if (!empty($this->filters['date_from'])) {
            $query->where('start_date', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->where('end_date', '<=', $this->filters['date_to']);
        }
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No. Booking', 'Penyewa', 'Jenis Penyewa', 'Tgl Booking',
            'Tgl Mulai', 'Tgl Selesai', 'Tujuan', 'Fasilitas',
            'Total', 'Status', 'Status Pembayaran',
        ];
    }

    public function map($booking): array
    {
        $facilities = $booking->items->map(fn($i) => $i->facility->name . ' (' . $i->quantity . ' ' . $i->facility->unit . ')')->join(', ');

        return [
            $booking->booking_number,
            $booking->customer->name,
            $booking->customer->type_label,
            $booking->booking_date?->format('d/m/Y'),
            $booking->start_date?->format('d/m/Y'),
            $booking->end_date?->format('d/m/Y'),
            $booking->purpose,
            $facilities,
            'Rp ' . number_format($booking->total_amount, 0, ',', '.'),
            $booking->status_badge['label'],
            $booking->payment_status_badge['label'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1a472a']], 'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true]],
        ];
    }
}
