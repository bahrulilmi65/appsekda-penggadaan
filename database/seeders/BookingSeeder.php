<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\BookingStatusHistory;
use App\Models\Customer;
use App\Models\Facility;
use App\Models\Payment;
use App\Models\User;
use App\Services\InventoryService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Data contoh transaksi (booking + pembayaran) 12 bulan terakhir
 * agar grafik dashboard, laporan, dan daftar transaksi terlihat hidup.
 * Idempoten: aman dijalankan ulang (acuan booking_number).
 */
class BookingSeeder extends Seeder
{
    private array $purposes = [
        'Rapat Koordinasi OPD', 'Seminar Peningkatan Kapasitas', 'Pelatihan Pegawai',
        'Resepsi Pernikahan', 'Acara Khitanan', 'Musrenbang Kecamatan',
        'Workshop Digitalisasi', 'Pengajian Rutin', 'Pameran Produk UMKM',
        'Gathering Keluarga Besar', 'Rapat Pimpinan', 'Sosialisasi Program',
    ];

    private array $methods = ['transfer', 'transfer', 'cash', 'qris', 'virtual_account'];

    public function run(): void
    {
        mt_srand(20261007);

        $facilities = Facility::active()->get();
        $customers = Customer::where('is_active', true)->get();
        if ($facilities->isEmpty() || $customers->isEmpty()) {
            $this->command->warn('Fasilitas/customer kosong, seeder dilewati.');
            return;
        }

        $actor = User::where('email', 'user@example.com')->first() ?? User::first();
        $admin = User::where('email', 'admin@sekda.go.id')->first() ?? User::first();
        $inventory = app(InventoryService::class);

        $made = 0;
        for ($back = 11; $back >= 0; $back--) {
            $month = now()->startOfMonth()->subMonths($back);
            $isCurrent = $back === 0;

            $plans = $isCurrent
                ? ['completed', 'completed', 'waiting_approval', 'waiting_approval', 'waiting_payment', 'preparing', 'in_use', 'cancelled']
                : $this->pastStatuses((int) $month->format('n'));

            $seq = 1;
            foreach ($plans as $status) {
                $this->makeBooking(
                    $month, $seq++, $status, $isCurrent,
                    $facilities, $customers, $actor, $admin, $inventory
                );
                $made++;
            }
        }

        $this->command->info("Data contoh transaksi dibuat: {$made} booking.");
    }

    /** Status acak untuk bulan lampau: dominan selesai. */
    private function pastStatuses(int $monthNum): array
    {
        $n = 4 + ($monthNum % 3); // 4-6 booking per bulan
        $out = [];
        for ($i = 0; $i < $n; $i++) {
            $r = mt_rand(1, 100);
            $out[] = $r <= 72 ? 'completed' : ($r <= 86 ? 'cancelled' : 'rejected');
        }
        return $out;
    }

    private function makeBooking(
        Carbon $month, int $seq, string $status, bool $isCurrent,
        $facilities, $customers, $actor, $admin, InventoryService $inventory
    ): void {
        $customer = $customers->random();
        $daysInMonth = $month->daysInMonth;

        // Tanggal pakai dalam bulan tsb (bulan berjalan: campur lalu & akan datang)
        if ($isCurrent) {
            $day = match ($status) {
                'in_use' => max(1, now()->day - 1),
                'completed' => max(1, now()->day - 10),
                'cancelled', 'rejected' => max(1, now()->day - 5),
                default => min($daysInMonth - 1, now()->day + mt_rand(2, 8)),
            };
        } else {
            $day = mt_rand(1, $daysInMonth - 2);
        }
        $duration = mt_rand(1, 2);
        $start = $month->copy()->day(min($day, $daysInMonth - $duration + 1));
        $end = $start->copy()->addDays($duration - 1);
        $bookedAt = $start->copy()->subDays(mt_rand(3, 10));

        $prefix = 'BK/'.$start->format('Y/m').'/'.str_pad($seq, 4, '0', STR_PAD_LEFT);

        $booking = Booking::updateOrCreate(['booking_number' => $prefix], [
            'customer_id' => $customer->id,
            'user_id' => $customer->user_id ?? $actor->id,
            'booking_date' => $bookedAt->toDateString(),
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'purpose' => $this->purposes[array_rand($this->purposes)],
            'participants' => mt_rand(20, 300),
            'status' => $status,
            'payment_status' => in_array($status, ['completed', 'paid', 'preparing', 'in_use']) ? 'paid'
                : ($status === 'waiting_payment' ? 'waiting_verification' : 'unpaid'),
            'approved_by' => in_array($status, ['cancelled', 'rejected']) ? null : $admin->id,
            'approved_at' => in_array($status, ['cancelled', 'rejected']) ? null : $bookedAt->copy()->addDay()->toDateTimeString(),
        ]);
        $fresh = $booking->wasRecentlyCreated;

        // Item: 1 gedung + 1-2 aset (atau 2-3 aset)
        $buildings = $facilities->where('type', 'building');
        $assets = $facilities->where('type', '!=', 'building');
        $picked = collect();
        if (! $buildings->isEmpty() && mt_rand(1, 100) <= 45) {
            $picked->push($buildings->random());
        }
        $need = max(1, mt_rand(1, 3) - $picked->count());
        foreach ($assets->shuffle()->take($need + 1) as $a) {
            if ($picked->count() >= 3) {
                break;
            }
            $picked->push($a);
        }

        $subtotal = 0;
        foreach ($picked as $i => $fac) {
            $qty = $fac->isBuilding() ? 1 : mt_rand(2, 8);
            $itemSub = $fac->price_type === 'per_event'
                ? $fac->price * $duration
                : $fac->price * $qty * $duration;
            $subtotal += $itemSub;

            $itemStatus = match ($status) {
                'completed' => 'completed',
                'cancelled', 'rejected' => 'cancelled',
                'in_use' => 'in_use',
                'preparing' => 'preparing',
                default => 'reserved',
            };

            BookingItem::updateOrCreate(
                ['booking_id' => $booking->id, 'facility_id' => $fac->id],
                [
                    'quantity' => $qty,
                    'unit_price' => $fac->price,
                    'price_type' => $fac->price_type,
                    'duration' => $duration,
                    'duration_unit' => 'day',
                    'subtotal' => $itemSub,
                    'status' => $itemStatus,
                ]
            );

            // Reservasi stok hanya untuk booking aktif & hanya saat pertama dibuat
            if ($fresh && ! $fac->isBuilding()
                && in_array($status, ['waiting_approval', 'approved', 'waiting_payment', 'paid', 'preparing', 'in_use'])) {
                $available = $fac->inventory?->qty_available ?? 0;
                $take = min($qty, max(0, $available - 5)); // sisakan buffer
                if ($take > 0) {
                    $inventory->reserveStock($fac->id, $take, $booking->id);
                }
            }

            if ($fac->isBuilding() && $i === 0) {
                $booking->start_time = '08:00';
                $booking->end_time = '12:00';
            }
        }

        $booking->subtotal = $subtotal;
        $booking->total_amount = $subtotal;
        $booking->save();

        // Pembayaran untuk booking lunas / menunggu verifikasi
        if (in_array($status, ['completed', 'paid', 'preparing', 'in_use'])) {
            $payAt = $start->copy()->subDays(mt_rand(1, 3));
            Payment::updateOrCreate(
                ['booking_id' => $booking->id],
                [
                    'payment_number' => str_replace('BK/', 'PAY/', $prefix),
                    'customer_id' => $customer->id,
                    'amount_due' => $subtotal,
                    'amount_paid' => $subtotal,
                    'payment_method' => $this->methods[array_rand($this->methods)],
                    'payment_date' => $payAt->toDateString(),
                    'status' => 'paid',
                    'verified_by' => $admin->id,
                    'verified_at' => $payAt->copy()->addDay()->toDateTimeString(),
                ]
            );
        } elseif ($status === 'waiting_payment') {
            Payment::updateOrCreate(
                ['booking_id' => $booking->id],
                [
                    'payment_number' => str_replace('BK/', 'PAY/', $prefix),
                    'customer_id' => $customer->id,
                    'amount_due' => $subtotal,
                    'amount_paid' => $subtotal,
                    'payment_method' => 'transfer',
                    'payment_date' => now()->toDateString(),
                    'status' => 'waiting_verification',
                ]
            );
        }

        // History + backdate timestamp agar grafik per bulan benar
        BookingStatusHistory::updateOrCreate(
            ['booking_id' => $booking->id, 'to_status' => $status],
            [
                'from_status' => null,
                'changed_by' => $admin->id,
                'notes' => 'Data contoh (seeder)',
                'created_at' => $bookedAt,
                'updated_at' => $bookedAt,
            ]
        );
        $booking->created_at = $bookedAt;
        $booking->updated_at = $bookedAt;
        $booking->save();

        $this->command->getOutput()->writeln("  {$prefix} {$status} Rp ".number_format($subtotal, 0, ',', '.'));
    }
}
