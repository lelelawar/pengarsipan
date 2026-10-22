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
            --sidebar-w: 260px;
            --primary: #4f46e5;
            --primary-soft: #eef2ff;
            --bg: #f5f7fb;
        }
        body { font-family: 'Inter', sans-serif; background: var(--bg); }

        /* ---------- Sidebar ---------- */
        .sidebar {
            position: fixed; top: 0; left: 0; bottom: 0;
            width: var(--sidebar-w);
            background: #fff;
            border-right: 1px solid #e9ecf3;
            padding: 1.25rem 1rem;
            display: flex; flex-direction: column;
            overflow-y: auto;
            z-index: 1050;
            transition: transform .25s ease;
        }
        .brand { font-weight: 700; font-size: 1.2rem; color: var(--primary); padding: .25rem .75rem 1.25rem; }
        .brand i { margin-right: .5rem; }

        .menu-label { font-size: .7rem; letter-spacing: .08em; color: #9aa3b5; padding: .75rem .75rem .35rem; text-transform: uppercase; }
        .sidebar .nav-link {
            color: #4b5468; border-radius: .6rem;
            padding: .6rem .75rem; margin-bottom: .15rem;
            display: flex; align-items: center; gap: .7rem; font-weight: 500;
        }
        .sidebar .nav-link:hover { background: var(--bg); }
        .sidebar .nav-link.active { background: var(--primary-soft); color: var(--primary); }

        .user-box { margin-top: auto; border-top: 1px solid #e9ecf3; padding-top: 1rem; }
        .avatar {
            width: 38px; height: 38px; border-radius: 50%;
            background: var(--primary); color: #fff;
            display: flex; align-items: center; justify-content: center; font-weight: 600;
        }

        .sidebar-backdrop {
            display: none; position: fixed; inset: 0;
            background: rgba(15, 23, 42, .45); z-index: 1045;
        }
        .sidebar-backdrop.show { display: block; }

        /* ---------- Konten ---------- */
        .content { margin-left: var(--sidebar-w); padding: 1.75rem; min-height: 100vh; }
        .topbar { display: none; }

        .card-soft { border: 0; border-radius: 1rem; box-shadow: 0 2px 14px rgba(30, 41, 80, .06); }
        .stat-icon {
            width: 48px; height: 48px; border-radius: .8rem;
            display: flex; align-items: center; justify-content: center; font-size: 1.4rem;
        }

        .btn-mobile-full { width: 100%; }
        @media (min-width: 576px) { .btn-mobile-full { width: auto; } }

        /* ---------- Responsive: tablet & HP ---------- */
        @media (max-width: 991.98px) {
            .sidebar { width: min(280px, 85vw); transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); box-shadow: 0 0 40px rgba(0,0,0,.2); }
            .content { margin-left: 0; padding: 1rem; }
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
                border: 1px solid #e9ecf3; border-radius: .8rem;
                padding: .5rem .85rem; margin-bottom: .75rem; background: #fff;
            }
            .table-cards td {
                display: flex; justify-content: space-between; align-items: center; gap: 1rem;
                border: 0 !important; padding: .35rem 0; text-align: right;
            }
            .table-cards td::before {
                content: attr(data-label);
                font-weight: 600; color: #6b7280; text-align: left; flex-shrink: 0;
            }
            .table-cards td[colspan] { justify-content: center; }
            .table-cards td[colspan]::before { content: none; }
        }

        @media print {
            .sidebar, .sidebar-backdrop, .topbar, .no-print { display: none !important; }
            .content { margin: 0; padding: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>

<div class="sidebar-backdrop" id="backdrop"></div>

<aside class="sidebar" id="sidebar">
    <div class="brand"><i class="bi bi-archive-fill"></i>Pengarsipan</div>

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

        <div class="menu-label">Laporan</div>

        <a href="{{ route('laporan.index') }}" class="nav-link {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line"></i> Laporan
        </a>
    </nav>

    <div class="user-box">
        <div class="d-flex align-items-center gap-2 mb-3">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->nama, 0, 1)) }}</div>
            <div class="lh-sm">
                <div class="fw-semibold">{{ auth()->user()->nama }}</div>
                <small class="text-muted text-capitalize">{{ auth()->user()->role }}</small>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-danger w-100 btn-sm">
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

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show no-print" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show no-print" role="alert">
            {{ session('error') }}
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