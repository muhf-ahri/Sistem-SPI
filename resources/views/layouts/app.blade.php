<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SPI - PT Pindad Enjiniring') }}</title>

    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --smart-blue: #2d6ac7;
            --glaucous: #4c79bc;
            --glaucous-2: #6d8ab4;
            --racing-red: #e63232;
            --carrot-orange: #f2913b;
            --gold: #ffd631;
            --bg-neutral: #f8f9fa;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-neutral);
            color: #212529;
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: var(--smart-blue);
            color: white;
            z-index: 1000;
            padding: 1.5rem 0;
            box-shadow: 2px 0 8px rgba(0, 0, 0, .06);
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 0 1.5rem 1.5rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 1.5rem;
        }

        .sidebar-header h5 {
            color: white;
            font-size: 1.25rem;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.85);
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
            font-size: 0.95rem;
            border-radius: 0;
            transition: all 0.2s ease-in-out;
        }

        .sidebar .nav-link i {
            font-size: 1.2rem;
        }

        .sidebar .nav-link:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(0, 0, 0, 0.15);
            border-left: 4px solid white;
        }

        /* Sidebar Submenu */
        .sidebar .nav-link.sidebar-sub {
            padding: 0.45rem 1rem;
            font-size: 0.85rem;
            font-weight: 400;
        }
        .sidebar .nav-link.sidebar-sub.active,
        .sidebar .nav-link.sidebar-sub:hover {
            color: var(--gold);
            background-color: rgba(255, 255, 255, 0.05);
        }

        /* Main Content Styling */
        .main-wrapper {
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar {
            padding: 1rem 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
        }

        .content-area {
            flex-grow: 1;
            padding: 2rem 1.5rem;
        }

        /* UI Components Styling */
        .card {
            border: 1px solid rgba(0,0,0,.08);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .03);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, .06);
        }

        .stat-card {
            border-radius: 12px;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.5rem 1rem;
        }

        .btn-primary {
            background-color: var(--smart-blue);
            border-color: var(--smart-blue);
        }

        .btn-primary:hover, .btn-primary:focus {
            background-color: #2251a3;
            border-color: #2251a3;
        }

        .btn-secondary {
            background-color: var(--glaucous);
            border-color: var(--glaucous);
        }

        .btn-secondary:hover {
            background-color: #3b5f94;
            border-color: #3b5f94;
        }

        .btn-danger {
            background-color: var(--racing-red);
            border-color: var(--racing-red);
        }

        .btn-warning {
            background-color: var(--carrot-orange);
            border-color: var(--carrot-orange);
            color: white;
        }

        .btn-warning:hover {
            color: white;
        }

        /* Badges */
        .badge {
            font-weight: 600;
            padding: 0.35em 0.65em;
            border-radius: 999px;
        }

        .bg-primary { background-color: var(--smart-blue) !important; }
        .bg-secondary { background-color: var(--glaucous-2) !important; }
        .bg-warning { background-color: var(--carrot-orange) !important; color: white !important; }
        .bg-danger { background-color: var(--racing-red) !important; }
        .bg-success { background-color: #198754 !important; }

        /* Risk Badge Custom CSS */
        .risk-badge {
            font-size: 0.85rem;
        }
        .risk-low { background-color: var(--glaucous-2) !important; color: white; }
        .risk-medium { background-color: var(--gold) !important; color: #212529; }
        .risk-high { background-color: var(--carrot-orange) !important; color: white; }
        .risk-critical { background-color: var(--racing-red) !important; color: white; }

        .text-primary { color: var(--smart-blue) !important; }
        .text-secondary { color: var(--glaucous-2) !important; }
        .text-danger { color: var(--racing-red) !important; }
        .text-warning { color: var(--carrot-orange) !important; }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .sidebar {
                display: none;
            }
            .main-wrapper {
                margin-left: 0;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    <!-- Sidebar for Desktop -->
    <x-sidebar />

    <!-- Offcanvas Sidebar for Mobile -->
    <div class="offcanvas offcanvas-start bg-primary text-white" tabindex="-1" id="sidebarOffcanvas" style="width: 260px;">
        <div class="offcanvas-header border-bottom border-white border-opacity-10">
            <h5 class="offcanvas-title fw-bold">SPI</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-0">
            <nav class="sidebar-mobile">
                <ul class="nav flex-column mt-3">
                    <li class="nav-item">
                        <a class="nav-link text-white py-2 px-4 d-flex align-items-center gap-3 {{ request()->routeIs('dashboard') ? 'bg-black bg-opacity-25' : '' }}" href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white py-2 px-4 d-flex align-items-center gap-3 {{ request()->routeIs('audit-plans.*') ? 'bg-black bg-opacity-25' : '' }}" href="{{ route('audit-plans.index') }}">
                            <i class="bi bi-clipboard-check"></i> Pengawasan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white py-2 px-4 d-flex align-items-center gap-3 {{ request()->routeIs('findings.*') ? 'bg-black bg-opacity-25' : '' }}" href="{{ route('findings.index') }}">
                            <i class="bi bi-exclamation-triangle"></i> Temuan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white py-2 px-4 d-flex align-items-center gap-3 {{ request()->routeIs('action-plans.*') ? 'bg-black bg-opacity-25' : '' }}" href="{{ route('action-plans.index') }}">
                            <i class="bi bi-arrow-repeat"></i> Tindak Lanjut
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white py-2 px-4 d-flex align-items-center gap-3 {{ request()->routeIs('reports.*') ? 'bg-black bg-opacity-25' : '' }}" href="{{ route('reports.audit-summary') }}">
                            <i class="bi bi-file-earmark-text"></i> Laporan
                        </a>
                    </li>
                    @if(auth()->user()->role === 'super_admin' || auth()->user()->role === 'spi')
                    <li class="nav-item">
                        <a class="nav-link text-white py-2 px-4 d-flex align-items-center gap-3 {{ request()->routeIs('master.*') ? 'bg-black bg-opacity-25' : '' }}" href="{{ route('master.users.index') }}">
                            <i class="bi-gear"></i> Master Data
                        </a>
                    </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link text-white py-2 px-4 d-flex align-items-center gap-3 {{ request()->routeIs('audit-logs.*') ? 'bg-black bg-opacity-25' : '' }}" href="{{ route('audit-logs.index') }}">
                            <i class="bi bi-clock-history"></i> Audit Log
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <x-navbar />

        <!-- Content Area -->
        <main class="content-area">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Bootstrap 5 Bundle JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>