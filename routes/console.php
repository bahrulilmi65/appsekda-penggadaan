<?php

use Illuminate\Support\Facades\Schedule;

// Expire bookings yang melewati waktu hold setiap 5 menit
Schedule::command('bookings:expire')->everyFiveMinutes();
