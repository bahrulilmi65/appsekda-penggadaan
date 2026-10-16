<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIPF Sekretariat Daerah') - {{ config('app.name') }}</title>

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <!-- Select2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">

    @stack('styles')
    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-bg: #1a2b4a;
            --sidebar-hover: #243a60;
            --sidebar-active: #0d6efd;
            --topbar-height: 60px;
            --primary: #0d6efd;
        }
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }

        /* Sidebar */
        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0; left: 0;
            background: var(--sidebar-bg);
            overflow-y: auto;
            transition: transform .3s ease;
            z-index: 1040;
        }
        #sidebar .sidebar-brand {
            padding: 16px 20px;
            border-bottom: 1px solid rgba(255,255,255,.1);
        }
        #sidebar .sidebar-brand h6 { color: #fff; font-size: .8rem; margin: 0; opacity: .7; }
        #sidebar .sidebar-brand .brand-title { color: #fff; font-weight: 700; font-size: 1rem; margin: 0; }

        #sidebar .nav-section { padding: 8px 16px 4px; color: rgba(255,255,255,.4); font-size: .7rem; text-transform: uppercase; letter-spacing: 1px; }

        #sidebar .nav-link {
            color: rgba(255,255,255,.75);
            padding: 9px 20px;
            font-size: .875rem;
            border-radius: 0;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all .2s;
        }
        #sidebar .nav-link:hover { color: #fff; background: var(--sidebar-hover); }
        #sidebar .nav-link.active { color: #fff; background: var(--sidebar-active); font-weight: 600; position: relative; }
        #sidebar .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 4px;
            background: #ffc107;
            border-radius: 0 4px 4px 0;
        }
        #sidebar .nav-link i { width: 18px; text-align: center; font-size: .9rem; }

        #sidebar .collapse .nav-link { padding-left: 48px; font-size: .83rem; }

        /* Main content */
        #main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin .3s ease;
        }

        /* Topbar */
        #topbar {
            height: var(--topbar-height);
            background: #fff;
            border-bottom: 1px solid #e5e9f0;
            padding: 0 24px;
            position: sticky;
            top: 0;
            z-index: 1030;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
        }

        /* Page content */
        .page-content { padding: 24px; }

        /* Cards */
        .card { border: none; box-shadow: 0 1px 4px rgba(0,0,0,.08); border-radius: 10px; }
        .card-header { background: #fff; border-bottom: 1px solid #f0f0f0; font-weight: 600; }

        /* Stats cards */
        .stat-card { border-radius: 12px; overflow: hidden; }
        .stat-card .stat-icon { width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }

        /* Badges */
        .badge { font-size: .75rem; }

        /* Tables */
        table.dataTable thead th { background: #f8f9fa; font-weight: 600; font-size: .825rem; }

        /* Mobile */
        @media (max-width: 991.98px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.show { transform: translateX(0); }
            #main-content { margin-left: 0; }
        }

        /* SweetAlert custom */
        .swal2-popup { border-radius: 12px !important; }

        /* Scrollbar */
        #sidebar::-webkit-scrollbar { width: 4px; }
        #sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.2); border-radius: 2px; }

        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 1039; }
        .sidebar-overlay.show { display: block; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="sidebar-brand">
            <p class="brand-title"><i class="fas fa-building me-2 text-primary"></i>SIPF Sekda</p>
            <h6>Sistem Informasi Penyewaan Fasilitas</h6>
        </div>

        @include('layouts.partials.sidebar-menu')
    </nav>

    <!-- Sidebar overlay (mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Main Content -->
    <div id="main-content">
        <!-- Topbar -->
        <div id="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-light" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <nav aria-label="breadcrumb" class="d-none d-md-block">
                    <ol class="breadcrumb mb-0 small">
                        @yield('breadcrumb')
                    </ol>
                </nav>
            </div>

            <div class="d-flex align-items-center gap-3">
                <!-- Notifications -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-light position-relative" data-bs-toggle="dropdown">
                        <i class="fas fa-bell"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notifBadge" style="font-size:.65rem;">
                            {{ auth()->user()->unreadNotificationsCount() ?: '' }}
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow" style="min-width:320px; max-height:400px; overflow-y:auto;">
                        <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                            <strong class="small">Notifikasi</strong>
                            <a href="#" class="small text-primary" id="markAllRead">Tandai semua dibaca</a>
                        </div>
                        @forelse(auth()->user()->notifications()->where('is_read', false)->latest()->take(5)->get() as $notif)
                            <a href="{{ $notif->action_url ?? '#' }}" class="dropdown-item py-2 px-3 border-bottom notif-item {{ !$notif->is_read ? 'bg-light' : '' }}" data-id="{{ $notif->id }}">
                                <div class="d-flex gap-2">
                                    <div class="mt-1"><i class="fas fa-circle text-{{ $notif->type }} small"></i></div>
                                    <div>
                                        <div class="fw-semibold small">{{ $notif->title }}</div>
                                        <div class="text-muted" style="font-size:.78rem;">{{ Str::limit($notif->message, 80) }}</div>
                                        <div class="text-muted" style="font-size:.72rem;">{{ $notif->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="text-center text-muted py-3 small">Tidak ada notifikasi baru</div>
                        @endforelse
                        @if(auth()->user()->hasRole(['admin','superadmin','bendahara','petugas','pimpinan']))
                        <div class="px-3 py-2 text-center">
                            <a href="{{ route('admin.dashboard') }}" class="small text-primary">Lihat semua</a>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- User menu -->
                <div class="dropdown">
                    <button class="btn btn-sm d-flex align-items-center gap-2 text-dark" data-bs-toggle="dropdown">
                        <img src="{{ auth()->user()->avatar_url }}" class="rounded-circle" width="32" height="32" alt="avatar">
                        <span class="d-none d-md-block small fw-semibold">{{ Str::limit(auth()->user()->name, 20) }}</span>
                        <i class="fas fa-chevron-down small text-muted"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li class="px-3 py-2">
                            <div class="fw-semibold">{{ auth()->user()->name }}</div>
                            <small class="text-muted">{{ ucfirst(auth()->user()->getRoleNames()->first()) }}</small>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        @if(auth()->user()->hasRole(['admin','superadmin','bendahara','petugas','pimpinan']))
                            <li><a class="dropdown-item small" href="{{ route('admin.dashboard') }}"><i class="fas fa-tachometer-alt me-2 text-muted"></i>Dashboard Admin</a></li>
                        @else
                            <li><a class="dropdown-item small" href="{{ route('user.dashboard') }}"><i class="fas fa-tachometer-alt me-2 text-muted"></i>Dashboard</a></li>
                            <li><a class="dropdown-item small" href="{{ route('user.profile') }}"><i class="fas fa-user me-2 text-muted"></i>Profil</a></li>
                        @endif
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST" class="d-inline w-100">
                                @csrf
                                <button type="submit" class="dropdown-item small text-danger"><i class="fas fa-sign-out-alt me-2"></i>Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Page Content -->
        <div class="page-content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <script>
    // CSRF token for AJAX
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // Sidebar toggle
    $('#sidebarToggle').on('click', function() {
        $('#sidebar').toggleClass('show');
        $('#sidebarOverlay').toggleClass('show');
    });
    $('#sidebarOverlay').on('click', function() {
        $('#sidebar').removeClass('show');
        $(this).removeClass('show');
    });

    // Mark notification as read
    $('.notif-item').on('click', function() {
        const id = $(this).data('id');
        if (id) {
            $.post(`/user/notifikasi/${id}/read`);
        }
    });

    // Mark all read
    $('#markAllRead').on('click', function(e) {
        e.preventDefault();
        @if(auth()->user()->hasRole(['admin','superadmin','bendahara','petugas','pimpinan']))
        $.post('{{ route("user.notifications.read-all") }}').done(() => { $('#notifBadge').text(''); });
        @else
        $.post('{{ route("user.notifications.read-all") }}').done(() => { $('#notifBadge').text(''); });
        @endif
    });

    // Global SweetAlert success
    @if(session('swal_success'))
    Swal.fire({ icon: 'success', title: 'Berhasil', text: '{{ session("swal_success") }}', timer: 2500, showConfirmButton: false });
    @endif
    </script>
    @stack('scripts')
</body>
</html>
