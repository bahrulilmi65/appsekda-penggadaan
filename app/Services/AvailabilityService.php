<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Facility;
use App\Models\Inventory;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AvailabilityService
{
    /**
     * Cek ketersediaan fasilitas gedung berdasarkan tanggal & jam.
     * Return true jika tersedia, false jika bentrok.
     */
    public function checkBuildingAvailability(
        int $facilityId,
        string $startDate,
        string $endDate,
        ?string $startTime = null,
        ?string $endTime = null,
        ?int $excludeBookingId = null
    ): bool {
        $query = BookingItem::where('facility_id', $facilityId)
            ->whereHas('booking', function ($q) use ($startDate, $endDate, $excludeBookingId) {
                $q->where('start_date', '<=', $endDate)
                  ->where('end_date', '>=', $startDate)
                  ->whereNotIn('status', ['cancelled', 'expired', 'rejected', 'draft']);
                if ($excludeBookingId) {
                    $q->where('id', '!=', $excludeBookingId);
                }
            })
            ->whereNotIn('status', ['cancelled']);

        // Jika ada waktu, cek overlap waktu juga
        if ($startTime && $endTime && $startDate === $endDate) {
            $query->whereHas('booking', function ($q) use ($startDate, $startTime, $endTime) {
                $q->where('start_date', $startDate)
                  ->where(function ($q2) use ($startTime, $endTime) {
                      $q2->where(function ($q3) use ($startTime, $endTime) {
                          $q3->where('start_time', '<', $endTime)
                             ->where('end_time', '>', $startTime);
                      });
                  });
            });
        }

        return $query->count() === 0;
    }

    /**
     * Hitung stok tersedia untuk item/asset berdasarkan tanggal.
     * Ini adalah method UTAMA untuk availability check.
     *
     * Rumus:
     * Available = Total - (Reserved + Preparing + InDelivery + InUse + Laundry + Damaged + Lost + Maintenance)
     * + dikurangi yg sudah dipesan untuk periode overlap
     */
    public function getAvailableQuantity(
        int $facilityId,
        string $startDate,
        string $endDate,
        ?int $excludeBookingId = null
    ): int {
        $facility = Facility::find($facilityId);
        if (!$facility) return 0;

        $inventory = $facility->inventory;
        if (!$inventory) return 0;

        $totalStock = $inventory->qty_total;

        // Qty yang sedang "tidak bisa dipakai" secara permanen/semi-permanen
        $permanentlyUnavailable = $inventory->qty_damaged
            + $inventory->qty_lost
            + $inventory->qty_maintenance;

        // Qty dalam laundry (akan kembali tapi sementara tidak tersedia)
        $temporarilyUnavailable = $inventory->qty_laundry;

        // Qty yang sudah dipesan untuk tanggal overlap (KECUALI yang dibatalkan/expired)
        $bookedQty = $this->getBookedQuantityForPeriod(
            $facilityId, $startDate, $endDate, $excludeBookingId
        );

        // Qty dalam pengantaran dan penggunaan aktif untuk periode ini
        $activeQty = $this->getActiveQuantityForPeriod(
            $facilityId, $startDate, $endDate, $excludeBookingId
        );

        $available = $totalStock - $permanentlyUnavailable - $temporarilyUnavailable - $bookedQty - $activeQty;

        return max(0, $available);
    }

    /**
     * Hitung qty yang sudah dipesan (reserved) untuk periode tertentu.
     * Overlap check: existing_start <= requested_end AND existing_end >= requested_start
     */
    public function getBookedQuantityForPeriod(
        int $facilityId,
        string $startDate,
        string $endDate,
        ?int $excludeBookingId = null
    ): int {
        $query = BookingItem::where('facility_id', $facilityId)
            ->whereIn('status', ['reserved'])
            ->whereHas('booking', function ($q) use ($startDate, $endDate, $excludeBookingId) {
                $q->where('start_date', '<=', $endDate)
                  ->where('end_date', '>=', $startDate)
                  ->whereNotIn('status', ['cancelled', 'expired', 'rejected', 'draft']);
                if ($excludeBookingId) {
                    $q->where('id', '!=', $excludeBookingId);
                }
            });

        return (int) $query->sum('quantity');
    }

    /**
     * Hitung qty yang sedang aktif (preparing, in_delivery, in_use) untuk periode tertentu.
     */
    public function getActiveQuantityForPeriod(
        int $facilityId,
        string $startDate,
        string $endDate,
        ?int $excludeBookingId = null
    ): int {
        $query = BookingItem::where('facility_id', $facilityId)
            ->whereIn('status', ['preparing', 'in_delivery', 'in_use'])
            ->whereHas('booking', function ($q) use ($startDate, $endDate, $excludeBookingId) {
                $q->where('start_date', '<=', $endDate)
                  ->where('end_date', '>=', $startDate)
                  ->whereNotIn('status', ['cancelled', 'expired', 'rejected']);
                if ($excludeBookingId) {
                    $q->where('id', '!=', $excludeBookingId);
                }
            });

        return (int) $query->sum('quantity');
    }

    /**
     * Dapatkan detail ketersediaan fasilitas untuk tanggal tertentu.
     * Digunakan untuk dashboard monitoring.
     */
    public function getAvailabilityDetail(int $facilityId, string $date): array
    {
        $facility = Facility::with('inventory')->find($facilityId);
        if (!$facility) return [];

        $inventory = $facility->inventory;
        if (!$inventory) return [];

        // Untuk gedung
        if ($facility->isBuilding()) {
            $bookings = BookingItem::where('facility_id', $facilityId)
                ->whereHas('booking', function ($q) use ($date) {
                    $q->where('start_date', '<=', $date)
                      ->where('end_date', '>=', $date)
                      ->whereNotIn('status', ['cancelled', 'expired', 'rejected', 'draft']);
                })
                ->whereNotIn('status', ['cancelled'])
                ->with(['booking.customer'])
                ->get();

            return [
                'type' => 'building',
                'facility_id' => $facilityId,
                'facility_name' => $facility->name,
                'capacity' => $facility->capacity,
                'is_available' => $bookings->isEmpty(),
                'bookings' => $bookings->map(fn($item) => [
                    'booking_number' => $item->booking->booking_number,
                    'customer_name' => $item->booking->customer->name,
                    'start_date' => $item->booking->start_date->format('d M Y'),
                    'end_date' => $item->booking->end_date->format('d M Y'),
                    'start_time' => $item->booking->start_time,
                    'end_time' => $item->booking->end_time,
                    'status' => $item->booking->status,
                ])->toArray(),
            ];
        }

        // Untuk aset/equipment
        $bookedQty = $this->getBookedQuantityForPeriod($facilityId, $date, $date);
        $activeQty = $this->getActiveQuantityForPeriod($facilityId, $date, $date);

        return [
            'type' => 'asset',
            'facility_id' => $facilityId,
            'facility_name' => $facility->name,
            'unit' => $facility->unit,
            'qty_total' => $inventory->qty_total,
            'qty_reserved' => $bookedQty,
            'qty_active' => $activeQty,
            'qty_laundry' => $inventory->qty_laundry,
            'qty_damaged' => $inventory->qty_damaged,
            'qty_lost' => $inventory->qty_lost,
            'qty_maintenance' => $inventory->qty_maintenance,
            'qty_available' => $this->getAvailableQuantity($facilityId, $date, $date),
        ];
    }

    /**
     * Cek apakah jumlah yg diminta tersedia untuk suatu periode.
     */
    public function isQuantityAvailable(
        int $facilityId,
        int $requestedQty,
        string $startDate,
        string $endDate,
        ?int $excludeBookingId = null
    ): bool {
        $facility = Facility::find($facilityId);
        if (!$facility) return false;

        if ($facility->isBuilding()) {
            return $this->checkBuildingAvailability(
                $facilityId, $startDate, $endDate, null, null, $excludeBookingId
            );
        }

        $available = $this->getAvailableQuantity(
            $facilityId, $startDate, $endDate, $excludeBookingId
        );

        return $available >= $requestedQty;
    }

    /**
     * Dapatkan semua fasilitas yang tersedia untuk periode tertentu.
     * Digunakan di halaman pencarian/katalog.
     */
    public function getAvailableFacilities(string $startDate, string $endDate): Collection
    {
        return Facility::with(['category', 'inventory', 'primaryImage'])
            ->where('is_active', true)
            ->where('is_bookable', true)
            ->get()
            ->map(function ($facility) use ($startDate, $endDate) {
                if ($facility->isBuilding()) {
                    $available = $this->checkBuildingAvailability(
                        $facility->id, $startDate, $endDate
                    );
                    $facility->availability_status = $available ? 'available' : 'booked';
                    $facility->available_qty = $available ? 1 : 0;
                } else {
                    $availableQty = $this->getAvailableQuantity(
                        $facility->id, $startDate, $endDate
                    );
                    $facility->available_qty = $availableQty;
                    if ($availableQty <= 0) {
                        $facility->availability_status = 'unavailable';
                    } elseif ($availableQty < $facility->total_stock * 0.3) {
                        $facility->availability_status = 'limited';
                    } else {
                        $facility->availability_status = 'available';
                    }
                }
                return $facility;
            });
    }

    /**
     * Dapatkan kalender availability untuk gedung (per bulan).
     * Digunakan di halaman detail fasilitas.
     */
    public function getBuildingCalendar(int $facilityId, int $year, int $month): array
    {
        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth();

        // Ambil semua booking dalam bulan ini untuk fasilitas ini
        $bookings = BookingItem::where('facility_id', $facilityId)
            ->whereHas('booking', function ($q) use ($startOfMonth, $endOfMonth) {
                $q->where('start_date', '<=', $endOfMonth->toDateString())
                  ->where('end_date', '>=', $startOfMonth->toDateString())
                  ->whereNotIn('status', ['cancelled', 'expired', 'rejected', 'draft']);
            })
            ->whereNotIn('status', ['cancelled'])
            ->with('booking:id,start_date,end_date,status')
            ->get();

        $calendar = [];
        $current = $startOfMonth->copy();

        while ($current <= $endOfMonth) {
            $dateStr = $current->toDateString();
            $dayBookings = $bookings->filter(function ($item) use ($dateStr) {
                return $item->booking->start_date->lte($dateStr)
                    && $item->booking->end_date->gte($dateStr);
            });

            $calendar[$dateStr] = [
                'date' => $dateStr,
                'day' => $current->day,
                'is_today' => $current->isToday(),
                'is_past' => $current->isPast() && !$current->isToday(),
                'status' => $dayBookings->isEmpty() ? 'available' : 'booked',
                'booking_count' => $dayBookings->count(),
            ];

            $current->addDay();
        }

        return $calendar;
    }

    /**
     * Dapatkan monitoring ketersediaan semua fasilitas untuk tanggal tertentu.
     * Digunakan di halaman monitoring admin.
     */
    public function getMonitoringData(string $date): array
    {
        $facilities = Facility::with(['category', 'inventory'])
            ->where('is_active', true)
            ->get();

        $buildings = [];
        $assets = [];

        foreach ($facilities as $facility) {
            if ($facility->isBuilding()) {
                $isAvailable = $this->checkBuildingAvailability($facility->id, $date, $date);
                $buildings[] = [
                    'id' => $facility->id,
                    'name' => $facility->name,
                    'location' => $facility->location,
                    'capacity' => $facility->capacity,
                    'is_available' => $isAvailable,
                    'status' => $isAvailable ? 'AVAILABLE' : 'BOOKED',
                ];
            } else {
                $inv = $facility->inventory;
                if (!$inv) continue;

                $bookedQty = $this->getBookedQuantityForPeriod($facility->id, $date, $date);
                $activeQty = $this->getActiveQuantityForPeriod($facility->id, $date, $date);
                $available = $this->getAvailableQuantity($facility->id, $date, $date);

                $assets[] = [
                    'id' => $facility->id,
                    'name' => $facility->name,
                    'unit' => $facility->unit,
                    'category' => $facility->category->name ?? '-',
                    'qty_total' => $inv->qty_total,
                    'qty_reserved' => $bookedQty,
                    'qty_active' => $activeQty,
                    'qty_laundry' => $inv->qty_laundry,
                    'qty_damaged' => $inv->qty_damaged,
                    'qty_lost' => $inv->qty_lost,
                    'qty_maintenance' => $inv->qty_maintenance,
                    'qty_available' => $available,
                ];
            }
        }

        return compact('buildings', 'assets', 'date');
    }
}
