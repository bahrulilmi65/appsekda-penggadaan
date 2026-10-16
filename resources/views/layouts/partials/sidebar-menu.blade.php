@php
    $user = auth()->user();
    $isAdmin = $user->hasRole(['superadmin','admin','bendahara','petugas','pimpinan']);
    try {
        $pendingBookingsCount = $isAdmin ? \App\Models\Booking::where('status','waiting_approval')->count() : 0;
        $pendingPaymentsCount = $isAdmin ? \App\Models\Payment::where('status','waiting_verification')->count() : 0;
        $cartCount = !$isAdmin ? \App\Models\Cart::where('user_id', $user->id)->count() : 0;
    } catch (\Exception $e) {
        $pendingBookingsCount = 0;
        $pendingPaymentsCount = 0;
        $cartCount = 0;
    }
@endphp

<ul class="nav flex-column pt-2 pb-4">

    {{-- Dashboard --}}
    <li class="nav-item">
        @if($isAdmin)
        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        @else
        <a href="{{ route('user.dashboard') }}" class="nav-link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        @endif
    </li>

    @if($isAdmin)

    {{-- Master Data --}}
    <li class="nav-section mt-2">Master Data</li>

    @if($user->can('view categories'))
    <li class="nav-item">
        <a href="{{ route('admin.kategori.index') }}" class="nav-link {{ request()->routeIs('admin.kategori.*') ? 'active' : '' }}">
            <i class="fas fa-tags"></i> Kategori
        </a>
    </li>
    @endif

    @if($user->can('view facilities'))
    <li class="nav-item">
        <a href="{{ route('admin.fasilitas.index') }}" class="nav-link {{ request()->routeIs('admin.fasilitas.*') ? 'active' : '' }}">
            <i class="fas fa-building"></i> Fasilitas
        </a>
    </li>
    @endif

    @if($user->can('view inventory'))
    <li class="nav-item">
        <a href="{{ route('admin.inventory.index') }}" class="nav-link {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}">
            <i class="fas fa-boxes"></i> Inventory
        </a>
    </li>
    @endif

    @if($user->can('view customers'))
    <li class="nav-item">
        <a href="{{ route('admin.penyewa.index') }}" class="nav-link {{ request()->routeIs('admin.penyewa.*') ? 'active' : '' }}">
            <i class="fas fa-users"></i> Penyewa
        </a>
    </li>
    @endif

    {{-- Transaksi --}}
    <li class="nav-section mt-2">Transaksi</li>

    @if($user->can('view bookings'))
    <li class="nav-item">
        <a href="{{ route('admin.booking.index') }}" class="nav-link {{ request()->routeIs('admin.booking.*') ? 'active' : '' }}">
            <i class="fas fa-calendar-check"></i> Booking
            @if($pendingBookingsCount > 0)
                <span class="badge bg-warning ms-auto">{{ $pendingBookingsCount }}</span>
            @endif
        </a>
    </li>
    @endif

    @if($user->can('view payments'))
    <li class="nav-item">
        <a href="{{ route('admin.payment.index') }}" class="nav-link {{ request()->routeIs('admin.payment.*') ? 'active' : '' }}">
            <i class="fas fa-money-bill-wave"></i> Pembayaran
            @if($pendingPaymentsCount > 0)
                <span class="badge bg-danger ms-auto">{{ $pendingPaymentsCount }}</span>
            @endif
        </a>
    </li>
    @endif

    @if($user->can('view deliveries'))
    <li class="nav-item">
        <a href="{{ route('admin.delivery.index') }}" class="nav-link {{ request()->routeIs('admin.delivery.*') ? 'active' : '' }}">
            <i class="fas fa-truck"></i> Pengiriman
        </a>
    </li>
    @endif

    @if($user->can('view returns'))
    <li class="nav-item">
        <a href="{{ route('admin.return.index') }}" class="nav-link {{ request()->routeIs('admin.return.*') ? 'active' : '' }}">
            <i class="fas fa-undo"></i> Pengembalian
        </a>
    </li>
    @endif

    {{-- Operasional --}}
    <li class="nav-section mt-2">Operasional</li>

    @if($user->can('view laundry'))
    <li class="nav-item">
        <a href="{{ route('admin.laundry.index') }}" class="nav-link {{ request()->routeIs('admin.laundry.*') ? 'active' : '' }}">
            <i class="fas fa-soap"></i> Laundry
        </a>
    </li>
    @endif

    @if($user->can('view damages'))
    <li class="nav-item">
        <a href="{{ route('admin.damage.index') }}" class="nav-link {{ request()->routeIs('admin.damage.*') ? 'active' : '' }}">
            <i class="fas fa-tools"></i> Kerusakan
        </a>
    </li>
    @endif

    <li class="nav-item">
        <a href="{{ route('admin.monitoring.index') }}" class="nav-link {{ request()->routeIs('admin.monitoring.*') ? 'active' : '' }}">
            <i class="fas fa-tv"></i> Monitoring
        </a>
    </li>

    {{-- Laporan --}}
    @if($user->can('view reports'))
    <li class="nav-section mt-2">Laporan</li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.report.*') ? 'active collapsed' : 'collapsed' }}"
           data-bs-toggle="collapse" href="#menuLaporan">
            <i class="fas fa-chart-bar"></i> Laporan
            <i class="fas fa-chevron-down ms-auto small"></i>
        </a>
        <div class="collapse {{ request()->routeIs('admin.report.*') ? 'show' : '' }}" id="menuLaporan">
            <a href="{{ route('admin.report.booking') }}" class="nav-link {{ request()->routeIs('admin.report.booking') ? 'active' : '' }}">Booking</a>
            <a href="{{ route('admin.report.payment') }}" class="nav-link {{ request()->routeIs('admin.report.payment') ? 'active' : '' }}">Pembayaran</a>
            <a href="{{ route('admin.report.inventory') }}" class="nav-link {{ request()->routeIs('admin.report.inventory') ? 'active' : '' }}">Inventory</a>
            <a href="{{ route('admin.report.facility') }}" class="nav-link {{ request()->routeIs('admin.report.facility') ? 'active' : '' }}">Fasilitas</a>
            <a href="{{ route('admin.report.damage') }}" class="nav-link {{ request()->routeIs('admin.report.damage') ? 'active' : '' }}">Kerusakan</a>
            <a href="{{ route('admin.report.laundry') }}" class="nav-link {{ request()->routeIs('admin.report.laundry') ? 'active' : '' }}">Laundry</a>
        </div>
    </li>
    @endif

    {{-- Sistem --}}
    @if($user->hasRole('superadmin'))
    <li class="nav-section mt-2">Sistem</li>
    <li class="nav-item">
        <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <i class="fas fa-user-cog"></i> Pengguna
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('admin.activity-log.index') }}" class="nav-link {{ request()->routeIs('admin.activity-log.*') ? 'active' : '' }}">
            <i class="fas fa-history"></i> Audit Log
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
            <i class="fas fa-cog"></i> Pengaturan
        </a>
    </li>
    @endif

    @else
    {{-- PENYEWA MENU --}}
    <li class="nav-section mt-2">Menu</li>
    <li class="nav-item">
        <a href="{{ route('catalog') }}" class="nav-link {{ request()->routeIs('catalog', 'facility.detail', 'check.availability') ? 'active' : '' }}">
            <i class="fas fa-store"></i> Katalog Fasilitas
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('user.cart.index') }}" class="nav-link {{ request()->routeIs('user.cart.*') ? 'active' : '' }}">
            <i class="fas fa-shopping-cart"></i> Keranjang
            @if($cartCount > 0)
                <span class="badge bg-primary ms-auto">{{ $cartCount }}</span>
            @endif
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('user.bookings.index') }}" class="nav-link {{ request()->routeIs('user.bookings.*') ? 'active' : '' }}">
            <i class="fas fa-calendar-check"></i> Booking Saya
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('user.payments.index') }}" class="nav-link {{ request()->routeIs('user.payments.*') ? 'active' : '' }}">
            <i class="fas fa-receipt"></i> Pembayaran
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('user.notifications') }}" class="nav-link {{ request()->routeIs('user.notifications') ? 'active' : '' }}">
            <i class="fas fa-bell"></i> Notifikasi
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('user.profile') }}" class="nav-link {{ request()->routeIs('user.profile*') ? 'active' : '' }}">
            <i class="fas fa-user"></i> Profil
        </a>
    </li>
    @endif

</ul>
