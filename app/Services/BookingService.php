<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\BookingStatusHistory;
use App\Models\Cart;
use App\Models\Facility;
use App\Models\Notification;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(
        protected AvailabilityService $availabilityService,
        protected InventoryService $inventoryService
    ) {}

    /**
     * Buat booking baru dari cart.
     * Menggunakan DB transaction + lockForUpdate untuk mencegah double booking.
     */
    public function createFromCart(
        int $userId,
        int $customerId,
        array $bookingData,
        array $cartIds
    ): Booking {
        return DB::transaction(function () use ($userId, $customerId, $bookingData, $cartIds) {
            $carts = Cart::with('facility')->whereIn('id', $cartIds)
                ->where('user_id', $userId)
                ->get();

            if ($carts->isEmpty()) {
                throw new \Exception('Keranjang belanja kosong.');
            }

            $startDate = $bookingData['start_date'];
            $endDate = $bookingData['end_date'];

            // Validasi ketersediaan setiap item DENGAN LOCK
            foreach ($carts as $cart) {
                $facility = $cart->facility;

                if ($facility->isBuilding()) {
                    // Lock dan cek gedung
                    $available = $this->availabilityService->checkBuildingAvailability(
                        $facility->id,
                        $startDate,
                        $endDate,
                        $bookingData['start_time'] ?? null,
                        $bookingData['end_time'] ?? null
                    );

                    if (!$available) {
                        throw ValidationException::withMessages([
                            'facility' => "{$facility->name} tidak tersedia pada tanggal yang dipilih. Sudah ada booking lain.",
                        ]);
                    }
                } else {
                    // Lock dan cek stok
                    $availableQty = $this->availabilityService->getAvailableQuantity(
                        $facility->id, $startDate, $endDate
                    );

                    if ($availableQty < $cart->quantity) {
                        throw ValidationException::withMessages([
                            'facility' => "Stok {$facility->name} tidak mencukupi. Tersedia: {$availableQty} {$facility->unit}, diminta: {$cart->quantity} {$facility->unit}.",
                        ]);
                    }
                }
            }

            // Hitung total
            $subtotal = 0;
            $items = [];

            foreach ($carts as $cart) {
                $facility = $cart->facility;
                $durationDays = (new \DateTime($startDate))->diff(new \DateTime($endDate))->days + 1;

                if ($facility->price_type === 'per_item') {
                    $itemSubtotal = $facility->price * $cart->quantity * $durationDays;
                } elseif ($facility->price_type === 'per_hour') {
                    $startTime = $bookingData['start_time'] ?? '08:00';
                    $endTime = $bookingData['end_time'] ?? '17:00';
                    $hours = (strtotime($endTime) - strtotime($startTime)) / 3600;
                    $itemSubtotal = $facility->price * max(1, $hours) * $durationDays;
                } else {
                    // per_day, per_event
                    $qty = $facility->price_type === 'per_event' ? 1 : $cart->quantity;
                    $itemSubtotal = $facility->price * $qty * $durationDays;
                }

                $subtotal += $itemSubtotal;
                $items[] = [
                    'facility' => $facility,
                    'quantity' => $cart->quantity,
                    'unit_price' => $facility->price,
                    'price_type' => $facility->price_type,
                    'duration' => $durationDays,
                    'duration_unit' => 'day',
                    'subtotal' => $itemSubtotal,
                    'notes' => $cart->notes,
                ];
            }

            $total = $subtotal + ($bookingData['additional_fee'] ?? 0) - ($bookingData['discount_amount'] ?? 0);

            // Hold time dari setting
            $holdMinutes = (int) Setting::get('booking_hold_minutes', 30);

            // Buat booking
            $booking = Booking::create([
                'customer_id' => $customerId,
                'user_id' => $userId,
                'booking_date' => now()->toDateString(),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'start_time' => $bookingData['start_time'] ?? null,
                'end_time' => $bookingData['end_time'] ?? null,
                'purpose' => $bookingData['purpose'],
                'participants' => $bookingData['participants'] ?? null,
                'notes' => $bookingData['notes'] ?? null,
                'special_request' => $bookingData['special_request'] ?? null,
                'subtotal' => $subtotal,
                'discount_amount' => $bookingData['discount_amount'] ?? 0,
                'additional_fee' => $bookingData['additional_fee'] ?? 0,
                'additional_fee_notes' => $bookingData['additional_fee_notes'] ?? null,
                'total_amount' => $total,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'hold_until' => now()->addMinutes($holdMinutes),
            ]);

            // Buat booking items dan reserve inventory
            foreach ($items as $item) {
                $bookingItem = BookingItem::create([
                    'booking_id' => $booking->id,
                    'facility_id' => $item['facility']->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'price_type' => $item['price_type'],
                    'duration' => $item['duration'],
                    'duration_unit' => $item['duration_unit'],
                    'subtotal' => $item['subtotal'],
                    'notes' => $item['notes'],
                    'status' => 'reserved',
                ]);

                // Reserve inventory (hanya untuk non-building)
                if (!$item['facility']->isBuilding()) {
                    $this->inventoryService->reserveStock(
                        $item['facility']->id,
                        $item['quantity'],
                        $booking->id
                    );
                }
            }

            // Catat status history
            $this->recordStatusHistory($booking, null, 'pending', 'Booking dibuat oleh ' . auth()->user()->name);

            // Hapus cart
            Cart::whereIn('id', $cartIds)->where('user_id', $userId)->delete();

            // Notifikasi ke admin
            $this->notifyAdmins($booking);

            return $booking;
        });
    }

    /**
     * Ubah status booking
     */
    public function changeStatus(Booking $booking, string $newStatus, ?string $notes = null): Booking
    {
        return DB::transaction(function () use ($booking, $newStatus, $notes) {
            $oldStatus = $booking->status;

            $booking->status = $newStatus;

            // Timestamp khusus per status
            match ($newStatus) {
                'approved' => $booking->fill([
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]),
                'cancelled' => $booking->fill([
                    'cancelled_at' => now(),
                    'cancellation_reason' => $notes,
                ]),
                'confirmed' => $booking->confirmed_at = now(),
                default => null,
            };

            $booking->save();

            // Handle inventory berdasarkan status
            if ($newStatus === 'cancelled' || $newStatus === 'rejected') {
                $this->releaseBookingInventory($booking);
            }

            if ($newStatus === 'expired') {
                $this->releaseBookingInventory($booking);
            }

            // Catat history
            $this->recordStatusHistory($booking, $oldStatus, $newStatus, $notes);

            // Notifikasi ke penyewa
            $this->notifyCustomer($booking, $newStatus);

            return $booking;
        });
    }

    /**
     * Lepaskan semua reservasi inventory untuk booking
     */
    protected function releaseBookingInventory(Booking $booking): void
    {
        foreach ($booking->items as $item) {
            if (!$item->facility->isBuilding() && $item->status === 'reserved') {
                $this->inventoryService->releaseReservation(
                    $item->facility_id,
                    $item->quantity,
                    $booking->id
                );
            }
            $item->update(['status' => 'cancelled']);
        }
    }

    /**
     * Tandai booking sebagai expired (dijalankan oleh scheduler)
     */
    public function expireHoldBookings(): int
    {
        $expiredBookings = Booking::whereIn('status', ['pending', 'waiting_payment'])
            ->where('hold_until', '<', now())
            ->get();

        $count = 0;
        foreach ($expiredBookings as $booking) {
            $this->changeStatus($booking, 'expired', 'Waktu hold habis');
            $count++;
        }

        return $count;
    }

    protected function recordStatusHistory(
        Booking $booking,
        ?string $fromStatus,
        string $toStatus,
        ?string $notes
    ): void {
        BookingStatusHistory::create([
            'booking_id' => $booking->id,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'changed_by' => auth()->id(),
            'notes' => $notes,
        ]);
    }

    protected function notifyAdmins(Booking $booking): void
    {
        // Notifikasi akan dibuat ke semua admin
        // Implementasi sederhana, bisa diperluas
    }

    protected function notifyCustomer(Booking $booking, string $status): void
    {
        $messages = [
            'approved' => ['Booking Disetujui', "Booking Anda {$booking->booking_number} telah disetujui. Silakan lakukan pembayaran.", 'success'],
            'rejected' => ['Booking Ditolak', "Booking Anda {$booking->booking_number} ditolak.", 'danger'],
            'waiting_payment' => ['Menunggu Pembayaran', "Silakan selesaikan pembayaran untuk booking {$booking->booking_number}.", 'warning'],
            'paid' => ['Pembayaran Dikonfirmasi', "Pembayaran untuk booking {$booking->booking_number} telah dikonfirmasi.", 'success'],
            'preparing' => ['Persiapan', "Barang untuk booking {$booking->booking_number} sedang disiapkan.", 'info'],
            'in_delivery' => ['Dalam Pengantaran', "Barang untuk booking {$booking->booking_number} sedang dalam perjalanan.", 'info'],
            'completed' => ['Booking Selesai', "Booking {$booking->booking_number} telah selesai. Terima kasih!", 'success'],
            'cancelled' => ['Booking Dibatalkan', "Booking {$booking->booking_number} telah dibatalkan.", 'warning'],
        ];

        if (isset($messages[$status])) {
            [$title, $message, $type] = $messages[$status];
            Notification::send(
                $booking->user_id,
                $title,
                $message,
                $type,
                route('user.bookings.show', $booking->id),
                'booking',
                $booking->id
            );
        }
    }

    /**
     * Hitung estimasi harga untuk cart items
     */
    public function calculateCartTotal(int $userId, string $startDate, string $endDate, ?string $startTime = null, ?string $endTime = null): array
    {
        $carts = Cart::with('facility')->where('user_id', $userId)->get();

        $items = [];
        $subtotal = 0;

        foreach ($carts as $cart) {
            $facility = $cart->facility;
            if (!$facility) continue;

            $durationDays = (new \DateTime($startDate))->diff(new \DateTime($endDate))->days + 1;

            if ($facility->price_type === 'per_item') {
                $itemSubtotal = $facility->price * $cart->quantity * $durationDays;
            } elseif ($facility->price_type === 'per_hour') {
                $hours = $startTime && $endTime
                    ? (strtotime($endTime) - strtotime($startTime)) / 3600
                    : 8;
                $itemSubtotal = $facility->price * max(1, $hours) * $durationDays;
            } else {
                $qty = $facility->price_type === 'per_event' ? 1 : 1;
                $itemSubtotal = $facility->price * $qty * $durationDays;
            }

            $subtotal += $itemSubtotal;
            $items[] = [
                'facility_id' => $facility->id,
                'facility_name' => $facility->name,
                'quantity' => $cart->quantity,
                'unit' => $facility->unit,
                'unit_price' => $facility->price,
                'price_type' => $facility->price_type,
                'duration' => $durationDays,
                'subtotal' => $itemSubtotal,
            ];
        }

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'duration_days' => isset($startDate) && isset($endDate)
                ? (new \DateTime($startDate))->diff(new \DateTime($endDate))->days + 1
                : 1,
        ];
    }
}
