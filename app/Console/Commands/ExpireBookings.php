<?php

namespace App\Console\Commands;

use App\Services\BookingService;
use Illuminate\Console\Command;

class ExpireBookings extends Command
{
    protected $signature = 'bookings:expire';
    protected $description = 'Expire bookings that have passed their hold time without payment';

    public function handle(BookingService $bookingService): void
    {
        $count = $bookingService->expireHoldBookings();
        $this->info("Expired {$count} booking(s).");
    }
}
