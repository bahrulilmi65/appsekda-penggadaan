<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DamageController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\FacilityController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\LaundryController;
use App\Http\Controllers\Admin\MonitoringController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReturnController as AdminReturnController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\User\BookingController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\PaymentController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/fasilitas', [PublicController::class, 'catalog'])->name('catalog');
Route::get('/fasilitas/{slug}', [PublicController::class, 'facilityDetail'])->name('facility.detail');
Route::get('/cek-ketersediaan', [PublicController::class, 'checkAvailability'])->name('check.availability');
Route::get('/verifikasi-invoice/{token}', [PublicController::class, 'verifyInvoice'])->name('invoice.verify');

Route::get('/api/availability', [PublicController::class, 'apiAvailability'])->name('api.availability');
Route::get('/api/facility-calendar/{id}', [PublicController::class, 'facilityCalendar'])->name('api.facility.calendar');

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| User/Penyewa Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'check.role:penyewa,admin,superadmin,bendahara,petugas,pimpinan'])
    ->prefix('user')
    ->name('user.')
    ->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profil', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profil/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Cart
    Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
    Route::post('/keranjang', [CartController::class, 'store'])->name('cart.store');
    Route::put('/keranjang/{cart}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/keranjang/{cart}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::delete('/keranjang', [CartController::class, 'clear'])->name('cart.clear');
    Route::get('/keranjang/count', [CartController::class, 'count'])->name('cart.count');

    // Booking
    Route::get('/booking', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/booking/create', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/booking', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/booking/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::post('/booking/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

    // Payment
    Route::get('/pembayaran', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/pembayaran/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::post('/pembayaran/{payment}/upload', [PaymentController::class, 'uploadProof'])->name('payments.upload');

    // Invoice
    Route::get('/invoice/{invoice}/download', [PaymentController::class, 'downloadInvoice'])->name('invoice.download');

    // Notifikasi
    Route::get('/notifikasi', [DashboardController::class, 'notifications'])->name('notifications');
    Route::post('/notifikasi/{id}/read', [DashboardController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifikasi/read-all', [DashboardController::class, 'markAllRead'])->name('notifications.read-all');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'check.role:superadmin,admin,bendahara,petugas,pimpinan'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [AdminDashboardController::class, 'stats'])->name('dashboard.stats');

    // Master Data - Kategori
    Route::resource('kategori', CategoryController::class)->except(['show', 'create']);

    // Master Data - Fasilitas
    Route::resource('fasilitas', FacilityController::class)->except(['show']);
    Route::post('fasilitas/{id}/images', [FacilityController::class, 'uploadImage'])->name('fasilitas.image.upload');
    Route::delete('fasilitas/images/{id}', [FacilityController::class, 'deleteImage'])->name('fasilitas.image.delete');
    Route::post('fasilitas/images/{id}/primary', [FacilityController::class, 'setPrimaryImage'])->name('fasilitas.image.primary');
    Route::get('fasilitas/{id}/inventory', [FacilityController::class, 'inventoryDetail'])->name('fasilitas.inventoryDetail');

    // Inventory
    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('inventory/{facility}/add-stock', [InventoryController::class, 'addStock'])->name('inventory.add-stock');
    Route::post('inventory/{facility}/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
    Route::get('inventory/{facility}/movements', [InventoryController::class, 'movements'])->name('inventory.movements');

    // Monitoring
    Route::get('monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
    Route::get('monitoring/data', [MonitoringController::class, 'data'])->name('monitoring.data');

    // Penyewa / Customer
    Route::resource('penyewa', CustomerController::class)->except(['show', 'create']);
    Route::post('penyewa/{customer}/verify', [CustomerController::class, 'verify'])->name('penyewa.verify');
    Route::get('penyewa/{customer}/detail', [CustomerController::class, 'detail'])->name('penyewa.detail');

    // Booking
    Route::get('booking', [AdminBookingController::class, 'index'])->name('booking.index');
    Route::get('booking/{booking}', [AdminBookingController::class, 'show'])->name('booking.show');
    Route::post('booking/{booking}/approve', [AdminBookingController::class, 'approve'])->name('booking.approve');
    Route::post('booking/{booking}/reject', [AdminBookingController::class, 'reject'])->name('booking.reject');
    Route::post('booking/{booking}/status', [AdminBookingController::class, 'changeStatus'])->name('booking.status');
    Route::post('booking/manual', [AdminBookingController::class, 'storeManual'])->name('booking.manual');

    // Pembayaran
    Route::get('pembayaran', [AdminPaymentController::class, 'index'])->name('payment.index');
    Route::get('pembayaran/{payment}', [AdminPaymentController::class, 'show'])->name('payment.show');
    Route::post('pembayaran/{payment}/verify', [AdminPaymentController::class, 'verify'])->name('payment.verify');
    Route::post('pembayaran/{payment}/reject', [AdminPaymentController::class, 'reject'])->name('payment.reject');
    Route::get('invoice/{invoice}/pdf', [AdminPaymentController::class, 'downloadInvoice'])->name('invoice.pdf');

    // Pengiriman
    Route::get('pengiriman', [DeliveryController::class, 'index'])->name('delivery.index');
    Route::get('pengiriman/{delivery}', [DeliveryController::class, 'show'])->name('delivery.show');
    Route::post('pengiriman/{booking}/create', [DeliveryController::class, 'store'])->name('delivery.store');
    Route::post('pengiriman/{delivery}/status', [DeliveryController::class, 'updateStatus'])->name('delivery.status');

    // Pengembalian
    Route::get('pengembalian', [AdminReturnController::class, 'index'])->name('return.index');
    Route::get('pengembalian/{return}', [AdminReturnController::class, 'show'])->name('return.show');
    Route::post('pengembalian/{booking}/create', [AdminReturnController::class, 'store'])->name('return.store');
    Route::post('pengembalian/{return}/verify', [AdminReturnController::class, 'verify'])->name('return.verify');

    // Laundry
    Route::get('laundry', [LaundryController::class, 'index'])->name('laundry.index');
    Route::post('laundry', [LaundryController::class, 'store'])->name('laundry.store');
    Route::put('laundry/{laundry}', [LaundryController::class, 'update'])->name('laundry.update');
    Route::post('laundry/{laundry}/complete', [LaundryController::class, 'complete'])->name('laundry.complete');
    Route::delete('laundry/{laundry}', [LaundryController::class, 'destroy'])->name('laundry.destroy');

    // Kerusakan
    Route::get('kerusakan', [DamageController::class, 'index'])->name('damage.index');
    Route::post('kerusakan', [DamageController::class, 'store'])->name('damage.store');
    Route::put('kerusakan/{damage}', [DamageController::class, 'update'])->name('damage.update');
    Route::delete('kerusakan/{damage}', [DamageController::class, 'destroy'])->name('damage.destroy');

    // Laporan
    Route::prefix('laporan')->name('report.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/booking', [ReportController::class, 'booking'])->name('booking');
        Route::get('/pembayaran', [ReportController::class, 'payment'])->name('payment');
        Route::get('/inventory', [ReportController::class, 'inventory'])->name('inventory');
        Route::get('/fasilitas', [ReportController::class, 'facility'])->name('facility');
        Route::get('/kerusakan', [ReportController::class, 'damage'])->name('damage');
        Route::get('/laundry', [ReportController::class, 'laundry'])->name('laundry');
        Route::get('/penyewa', [ReportController::class, 'customer'])->name('customer');
        Route::get('/booking/pdf', [ReportController::class, 'bookingPdf'])->name('booking.pdf');
        Route::get('/pembayaran/pdf', [ReportController::class, 'paymentPdf'])->name('payment.pdf');
        Route::get('/booking/excel', [ReportController::class, 'bookingExcel'])->name('booking.excel');
        Route::get('/pembayaran/excel', [ReportController::class, 'paymentExcel'])->name('payment.excel');
    });

    // Superadmin only
    Route::middleware('check.role:superadmin')->group(function () {
        Route::resource('users', UserController::class)->except(['show', 'create']);
        Route::get('activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
        Route::get('pengaturan', [SettingController::class, 'index'])->name('settings.index');
        Route::put('pengaturan', [SettingController::class, 'update'])->name('settings.update');
    });

    Route::get('/notifikasi/count', [AdminDashboardController::class, 'notificationCount'])->name('notifications.count');
    Route::post('/notifikasi/read-all', [AdminDashboardController::class, 'markAllRead'])->name('notifications.read-all');
});

// Redirect berdasarkan role setelah login
Route::get('/redirect', function () {
    if (!auth()->check()) return redirect()->route('login');
    $user = auth()->user();
    if ($user->hasRole(['superadmin', 'admin', 'bendahara', 'petugas', 'pimpinan'])) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('user.dashboard');
})->middleware('auth')->name('redirect');
