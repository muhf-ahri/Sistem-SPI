<!-- Sidebar Component -->
<nav class="sidebar d-flex flex-column">
    <div class="sidebar-header">
        <h5 class="fw-bold mb-0"><i class="bi bi-shield-check me-2"></i>SPI</h5>
        <small>PT Pindad Enjiniring Indonesia</small>
        <small class="d-block text-white-50">Sistem Pengawasan Internal</small>
    </div>

    <ul class="nav flex-column flex-grow-1 overflow-auto">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>

        <!-- Pengawasan -->
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('audit-plans.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#menuPengawasan" role="button" aria-expanded="{{ request()->routeIs('audit-plans.*') ? 'true' : 'false' }}">
                <i class="bi bi-clipboard-check"></i> Pengawasan
                <i class="bi bi-chevron-down ms-auto small"></i>
            </a>
            <div class="collapse {{ request()->routeIs('audit-plans.*') ? 'show' : '' }}" id="menuPengawasan">
                <ul class="nav flex-column ms-3">
                    <li class="nav-item"><a class="nav-link sidebar-sub {{ request()->routeIs('audit-plans.index') && !request('status') ? 'active' : '' }}" href="{{ route('audit-plans.index') }}"><i class="bi bi-list-ul me-2"></i>Semua Pengawasan</a></li>
                    <li class="nav-item"><a class="nav-link sidebar-sub {{ request('status') == 'draft' ? 'active' : '' }}" href="{{ route('audit-plans.index', ['status' => 'draft']) }}"><i class="bi bi-file-earmark me-2"></i>Rencana Pengawasan</a></li>
                    <li class="nav-item"><a class="nav-link sidebar-sub {{ request('status') == 'in_progress' ? 'active' : '' }}" href="{{ route('audit-plans.index', ['status' => 'in_progress']) }}"><i class="bi bi-play-circle me-2"></i>Sedang Berlangsung</a></li>
                    <li class="nav-item"><a class="nav-link sidebar-sub {{ request('status') == 'completed' ? 'active' : '' }}" href="{{ route('audit-plans.index', ['status' => 'completed']) }}"><i class="bi bi-archive me-2"></i>Riwayat Pengawasan</a></li>
                </ul>
            </div>
        </li>

        <!-- Temuan -->
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('findings.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#menuTemuan" role="button" aria-expanded="{{ request()->routeIs('findings.*') ? 'true' : 'false' }}">
                <i class="bi bi-exclamation-triangle"></i> Temuan
                <i class="bi bi-chevron-down ms-auto small"></i>
            </a>
            <div class="collapse {{ request()->routeIs('findings.*') ? 'show' : '' }}" id="menuTemuan">
                <ul class="nav flex-column ms-3">
                    <li class="nav-item"><a class="nav-link sidebar-sub {{ request()->routeIs('findings.index') && !request('status') && !request('overdue') ? 'active' : '' }}" href="{{ route('findings.index') }}"><i class="bi bi-list-ul me-2"></i>Semua Temuan</a></li>
                    <li class="nav-item"><a class="nav-link sidebar-sub {{ request('status') == 'open' ? 'active' : '' }}" href="{{ route('findings.index', ['status' => 'open']) }}"><i class="bi bi-folder2-open me-2"></i>Open</a></li>
                    <li class="nav-item"><a class="nav-link sidebar-sub {{ request('status') == 'in_progress' ? 'active' : '' }}" href="{{ route('findings.index', ['status' => 'in_progress']) }}"><i class="bi bi-arrow-repeat me-2"></i>In Progress</a></li>
                    <li class="nav-item"><a class="nav-link sidebar-sub {{ request('status') == 'waiting_verification' ? 'active' : '' }}" href="{{ route('findings.index', ['status' => 'waiting_verification']) }}"><i class="bi bi-hourglass-split me-2"></i>Waiting Verification</a></li>
                    <li class="nav-item"><a class="nav-link sidebar-sub {{ request('status') == 'closed' ? 'active' : '' }}" href="{{ route('findings.index', ['status' => 'closed']) }}"><i class="bi bi-check-circle me-2"></i>Closed</a></li>
                    <li class="nav-item"><a class="nav-link sidebar-sub {{ request('overdue') ? 'active' : '' }}" href="{{ route('findings.index', ['overdue' => 1]) }}"><i class="bi bi-alarm me-2"></i>Overdue</a></li>
                </ul>
            </div>
        </li>

        <!-- Tindak Lanjut -->
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('action-plans.*') ? 'active' : '' }}" href="{{ route('action-plans.index') }}">
                <i class="bi bi-arrow-repeat"></i> Tindak Lanjut
            </a>
        </li>

        <!-- Pemeriksaan -->
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('inspections.*') ? 'active' : '' }}" href="{{ route('inspections.index') }}">
                <i class="bi bi-search"></i> Pemeriksaan
            </a>
        </li>

        <!-- Laporan -->
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#menuLaporan" role="button" aria-expanded="{{ request()->routeIs('reports.*') ? 'true' : 'false' }}">
                <i class="bi bi-file-earmark-text"></i> Laporan
                <i class="bi bi-chevron-down ms-auto small"></i>
            </a>
            <div class="collapse {{ request()->routeIs('reports.*') ? 'show' : '' }}" id="menuLaporan">
                <ul class="nav flex-column ms-3">
                    <li class="nav-item"><a class="nav-link sidebar-sub {{ request()->routeIs('reports.audit-summary') ? 'active' : '' }}" href="{{ route('reports.audit-summary') }}"><i class="bi bi-clipboard-data me-2"></i>Laporan Pengawasan</a></li>
                    <li class="nav-item"><a class="nav-link sidebar-sub {{ request()->routeIs('reports.finding-analysis') ? 'active' : '' }}" href="{{ route('reports.finding-analysis') }}"><i class="bi bi-exclamation-diamond me-2"></i>Laporan Temuan & Risiko</a></li>
                    <li class="nav-item"><a class="nav-link sidebar-sub {{ request()->routeIs('reports.action-plan-status') ? 'active' : '' }}" href="{{ route('reports.action-plan-status') }}"><i class="bi bi-arrow-repeat me-2"></i>Laporan Tindak Lanjut</a></li>
                </ul>
            </div>
        </li>

        <!-- Master Data (Super Admin & SPI saja) -->
        @can('manage-master')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('master.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#menuMaster" role="button" aria-expanded="{{ request()->routeIs('master.*') ? 'true' : 'false' }}">
                <i class="bi bi-gear"></i> Master Data
                <i class="bi bi-chevron-down ms-auto small"></i>
            </a>
            <div class="collapse {{ request()->routeIs('master.*') ? 'show' : '' }}" id="menuMaster">
                <ul class="nav flex-column ms-3">
                    <li class="nav-item"><a class="nav-link sidebar-sub {{ request()->routeIs('master.divisions.*') ? 'active' : '' }}" href="{{ route('master.divisions.index') }}"><i class="bi bi-building me-2"></i>Divisi</a></li>
                    <li class="nav-item"><a class="nav-link sidebar-sub {{ request()->routeIs('master.audit-types.*') ? 'active' : '' }}" href="{{ route('master.audit-types.index') }}"><i class="bi bi-clipboard me-2"></i>Jenis Pengawasan</a></li>
                    <li class="nav-item"><a class="nav-link sidebar-sub {{ request()->routeIs('master.finding-categories.*') ? 'active' : '' }}" href="{{ route('master.finding-categories.index') }}"><i class="bi bi-tag me-2"></i>Kategori Temuan</a></li>
                    <li class="nav-item"><a class="nav-link sidebar-sub {{ request()->routeIs('master.risk-categories.*') ? 'active' : '' }}" href="{{ route('master.risk-categories.index') }}"><i class="bi bi-shield-exclamation me-2"></i>Kategori Risiko</a></li>
                    <li class="nav-item"><a class="nav-link sidebar-sub {{ request()->routeIs('master.users.*') ? 'active' : '' }}" href="{{ route('master.users.index') }}"><i class="bi bi-people me-2"></i>Users</a></li>
                </ul>
            </div>
        </li>

        <!-- Audit Log (Super Admin & SPI saja) -->
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}" href="{{ route('audit-logs.index') }}">
                <i class="bi bi-clock-history"></i> Audit Log
            </a>
        </li>
        @endcan

        <li class="nav-item mt-auto border-top border-white border-opacity-10 mt-3 pt-2">
            <a class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
                <i class="bi bi-person"></i> Profile
            </a>
        </li>
    </ul>
</nav>