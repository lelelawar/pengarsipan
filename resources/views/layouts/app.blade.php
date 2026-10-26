<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') - Pengarsipan</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-w: 264px;
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --primary-soft: #eef2ff;
            --bg: #f4f6fb;
            --border: #e8ebf3;
            --muted: #6b7280;
        }

        body { font-family: 'Inter', sans-serif; background: var(--bg); color: #1f2937; }
        h3, h4, h5 { letter-spacing: -.01em; }

        /* ---------- Sidebar ---------- */
        .sidebar {
            position: fixed; top: 0; left: 0; bottom: 0;
            width: var(--sidebar-w);
            background: linear-gradient(180deg, #1e1b4b 0%, #312e81 100%);
            padding: 1.25rem 1rem;
            display: flex; flex-direction: column;
            overflow-y: auto;
            z-index: 1050;
            transition: transform .25s ease;
        }
        .brand { display: flex; align-items: center; gap: .7rem; color: #fff; font-weight: 700; font-size: 1.15rem; padding: .25rem .5rem 1.25rem; }
        .brand-logo {
            width: 40px; height: 40px; border-radius: .8rem;
            background: rgba(255, 255, 255, .14);
            display: flex; align-items: center; justify-content: center; font-size: 1.2rem;
        }
        .brand small { display: block; font-weight: 400; font-size: .72rem; color: #a5b4fc; }

        .menu-label { font-size: .68rem; letter-spacing: .1em; color: rgba(199, 210, 254, .5); padding: 1rem .75rem .4rem; text-transform: uppercase; }
        .sidebar .nav-link {
            color: #c7d2fe; border-radius: .7rem;
            padding: .62rem .8rem; margin-bottom: .15rem;
            display: flex; align-items: center; gap: .75rem; font-weight: 500; font-size: .93rem;
            transition: background .15s, color .15s;
        }
        .sidebar .nav-link i { font-size: 1.05rem; }
        .sidebar .nav-link:hover { background: rgba(255, 255, 255, .08); color: #fff; }
        .sidebar .nav-link.active { background: rgba(255, 255, 255, .15); color: #fff; box-shadow: inset 3px 0 0 #a5b4fc; }

        .user-box {
            margin-top: auto; padding: .9rem;
            background: rgba(255, 255, 255, .08);
            border-radius: .9rem;
        }
        .user-box .user-name { color: #fff; font-weight: 600; }
        .user-box .user-role { color: #a5b4fc; font-size: .78rem; text-transform: capitalize; }
        .avatar {
            width: 40px; height: 40px; border-radius: 50%;
            background: linear-gradient(135deg, #818cf8, #c084fc); color: #fff;
            display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0;
        }

        .sidebar-backdrop { display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, .5); z-index: 1045; }
        .sidebar-backdrop.show { display: block; }

        /* ---------- Konten ---------- */
        .content { margin-left: var(--sidebar-w); padding: 1.5rem 2rem 2rem; min-height: 100vh; }
        .topbar { display: none; }

        .page-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .page-top .date { color: var(--muted); font-size: .88rem; }
        .user-chip {
            display: flex; align-items: center; gap: .6rem;
            background: #fff; border: 1px solid var(--border);
            padding: .3rem .85rem .3rem .3rem; border-radius: 2rem; font-size: .88rem; font-weight: 500;
        }
        .user-chip .avatar { width: 32px; height: 32px; font-size: .85rem; }

        /* ---------- Komponen global ---------- */
        .card-soft {
            border: 1px solid var(--border); border-radius: 1rem; background: #fff;
            box-shadow: 0 1px 2px rgba(16, 24, 40, .04), 0 8px 24px rgba(30, 41, 80, .05);
        }

        .btn { border-radius: .65rem; font-weight: 500; }
        .btn-primary {
            --bs-btn-bg: #4f46e5; --bs-btn-border-color: #4f46e5;
            --bs-btn-hover-bg: #4338ca; --bs-btn-hover-border-color: #4338ca;
            --bs-btn-active-bg: #3730a3; --bs-btn-active-border-color: #3730a3;
            box-shadow: 0 4px 12px rgba(79, 70, 229, .25);
        }
        .btn-outline-primary {
            --bs-btn-color: #4f46e5; --bs-btn-border-color: #c7d2fe;
            --bs-btn-hover-bg: #4f46e5; --bs-btn-hover-border-color: #4f46e5;
            --bs-btn-active-bg: #4338ca; --bs-btn-active-border-color: #4338ca;
        }

        .form-control, .form-select { border-radius: .65rem; border-color: #dfe3ee; padding: .6rem .85rem; }
        .form-control:focus, .form-select:focus { border-color: #818cf8; box-shadow: 0 0 0 .22rem rgba(99, 102, 241, .15); }
        .form-label { font-size: .88rem; }

        .table { --bs-table-bg: transparent; }
        .table-light { --bs-table-bg: #f8f9fc; --bs-table-color: #6b7280; --bs-table-border-color: var(--border); }
        .table thead th {
            font-size: .72rem; text-transform: uppercase; letter-spacing: .05em;
            font-weight: 600; white-space: nowrap; padding-top: .8rem; padding-bottom: .8rem;
        }
        .table td { padding-top: .85rem; padding-bottom: .85rem; border-color: var(--border); }
        .table tbody tr:hover td { background: #f8f9ff; }

        .badge { font-weight: 500; }
        .alert { border: 0; border-radius: .8rem; }
        .modal-content { border: 0; border-radius: 1rem; }

        .page-link { color: var(--primary); border-radius: .5rem !important; margin: 0 .1rem; border-color: var(--border); }
        .page-item.active .page-link { background: var(--primary); border-color: var(--primary); }

        .btn-mobile-full { width: 100%; }
        @media (min-width: 576px) { .btn-mobile-full { width: auto; } }

        /* ---------- Responsive: tablet & HP ---------- */
        @media (max-width: 991.98px) {
            .sidebar { width: min(284px, 86vw); transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); box-shadow: 0 0 40px rgba(0, 0, 0, .3); }
            .content { margin-left: 0; padding: 1rem; }
            .page-top { display: none; }
            .topbar {
                display: flex; align-items: center; gap: .75rem;
                position: sticky; top: 0; z-index: 1030;
                background: var(--bg); padding: .5rem 0 .75rem; margin-bottom: .5rem;
            }
        }

        @media (max-width: 575.98px) {
            .btn { min-height: 42px; }
            .btn-sm { min-height: 36px; min-width: 36px; }
            .form-actions { flex-direction: column; }
            .form-actions .btn { width: 100%; }
            h3 { font-size: 1.4rem; }
        }

        /* ---------- Tabel jadi kartu di HP ---------- */
        @media (max-width: 767.98px) {
            .table-cards thead { display: none; }
            .table-cards, .table-cards tbody, .table-cards tr, .table-cards td { display: block; width: 100%; }
            .table-cards tr {
                border: 1px solid var(--border); border-radius: .9rem;
                padding: .5rem .9rem; margin-bottom: .75rem; background: #fff;
            }
            .table-cards td {
                display: flex; justify-content: space-between; align-items: center; gap: 1rem;
                border: 0 !important; padding: .4rem 0; text-align: right;
            }
            .table-cards td::before {
                content: attr(data-label);
                font-weight: 600; color: var(--muted); text-align: left; flex-shrink: 0; font-size: .82rem;
            }
            .table-cards td[colspan] { justify-content: center; }
            .table-cards td[colspan]::before { content: none; }
        }

        @media print {
            .sidebar, .sidebar-backdrop, .topbar, .page-top, .no-print { display: none !important; }
            .content { margin: 0; padding: 0; }
            body { background: #fff; }
            .card-soft { box-shadow: none; border: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>

<div class="sidebar-backdrop" id="backdrop"></div>

<aside class="sidebar" id="sidebar">
    <div class="brand">
        <div class="brand-logo"><i class="bi bi-archive-fill"></i></div>
        <div>Pengarsipan<small>Arsip digital internal</small></div>
    </div>

    <nav class="nav flex-column">
        <div class="menu-label">Menu</div>

        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2"></i> Dashboard
        </a>

        <a href="{{ route('arsip.index') }}" class="nav-link {{ request()->routeIs('arsip.index', 'arsip.show', 'arsip.edit') ? 'active' : '' }}">
            <i class="bi bi-folder2-open"></i> Daftar Arsip
        </a>

        <a href="{{ route('arsip.create') }}" class="nav-link {{ request()->routeIs('arsip.create') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-plus"></i> Tambahkan Arsip
        </a>

        <a href="{{ route('laporan.index') }}" class="nav-link {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line"></i> Laporan
        </a>

        @if(auth()->user()->isAdmin())
            <div class="menu-label">Admin</div>

            <a href="{{ route('akun.index') }}" class="nav-link {{ request()->routeIs('akun.*') ? 'active' : '' }}">
                <i class="bi bi-person-plus"></i> Tambah Akun
            </a>
            <a href="{{ route('kategori.index') }}" class="nav-link {{ request()->routeIs('kategori.*') ? 'active' : '' }}">
                <i class="bi bi-tags"></i> Kategori
            </a>
            <a href="{{ route('log.index') }}" class="nav-link {{ request()->routeIs('log.*') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i> Log Aktivitas
            </a>
        @endif
    </nav>

    <div class="user-box">
        <div class="d-flex align-items-center gap-2 mb-3">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->nama, 0, 1)) }}</div>
            <div class="lh-sm" style="min-width:0">
                <div class="user-name text-truncate">{{ auth()->user()->nama }}</div>
                <div class="user-role">{{ auth()->user()->role }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-light w-100 btn-sm">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </button>
        </form>
    </div>
</aside>

<main class="content">
    <div class="topbar no-print">
        <button class="btn btn-light border" type="button" id="menuToggle" aria-label="Buka menu">
            <i class="bi bi-list fs-5"></i>
        </button>
        <span class="fw-semibold">@yield('title', 'Dashboard')</span>
    </div>

    <div class="page-top no-print">
        <div class="date"><i class="bi bi-calendar3 me-1"></i> {{ now()->locale('id')->translatedFormat('l, d F Y') }}</div>
        <div class="user-chip">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->nama, 0, 1)) }}</div>
            <span>{{ auth()->user()->nama }}</span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show no-print" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show no-print" role="alert">
            <i class="bi bi-exclamation-circle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const sidebar  = document.getElementById('sidebar');
    const backdrop = document.getElementById('backdrop');

    function setSidebar(open) {
        sidebar.classList.toggle('show', open);
        backdrop.classList.toggle('show', open);
    }

    document.getElementById('menuToggle').addEventListener('click', () => setSidebar(true));
    backdrop.addEventListener('click', () => setSidebar(false));
    sidebar.querySelectorAll('a.nav-link').forEach(a => a.addEventListener('click', () => setSidebar(false)));
    window.addEventListener('resize', () => { if (window.innerWidth >= 992) setSidebar(false); });
</script>
@stack('scripts')
</body>
</html>